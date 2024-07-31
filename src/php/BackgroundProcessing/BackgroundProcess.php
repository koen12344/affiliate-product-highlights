<?php

namespace Koen12344\AffiliateProductHighlights\BackgroundProcessing;

use Koen12344\AffiliateProductHighlights\Provider\AdTraction\ProductMapping;
use SimpleXMLElement;
use WP_Http;
use XMLReader;

class BackgroundProcess extends \Koen12344_APH_Vendor_WP_Background_Process {


	protected function task( $item ) {
		if($item['action'] === 'download_feed'){
			return $this->download_feed($item);
		}elseif($item['action'] === 'split_feed'){
			return $this->split_feed($item);
		}elseif($item['action'] === 'import_chunk'){
			return $this->import_chunk($item);
		}

		return false;
	}

	public function import_images(int $feed_id, int $product_id, $images){
		global $wpdb;

		/**
		 *     		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		product_id bigint(20) UNSIGNED NOT NULL,
		image_url varchar(255) NOT NULL,
		imported_at DATETIME NOT NULL,
		 */
		$image_table = $wpdb->prefix.'phft_images';
		foreach($images as $image){
			$image_data = [
				'feed_id'   => $feed_id,
				'product_id' => $product_id,
				'image_url' => (string)$image,
				'imported_at' => current_time('mysql', true),
			];

			$existing_image = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM $image_table WHERE product_id = %d AND image_url = %s",
				$product_id,
				(string)$image
			));
			if($existing_image){
				$wpdb->update($image_table, $image_data, ['id' => $existing_image->id]);
			}else{
				$wpdb->insert($image_table, $image_data);
			}
		}
	}

	public function download_xml_file($url) {
		if (filter_var($url, FILTER_VALIDATE_URL) === false) {
			return false;
		}

		if(!function_exists('wp_tempnam')){
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}

		$tmp_file = \wp_tempnam();
		$handle = fopen($tmp_file, 'w');

		if ($handle === false) {
			return false;
		}

		$http = new WP_Http();
		$response = $http->request($url, [
			'stream' => true,
			'filename' => $tmp_file,
			'timeout' => 300,
		]);

		if (is_wp_error($response)) {
			@fclose($handle);
			@unlink($tmp_file);
			return false;
		}

		fclose($handle);
		return $tmp_file;
	}

	public function import_xml($file, int $feed_id, $feed_type) {
		global $wpdb;

		$reader = new XMLReader();
		$reader->open($file);

		while ($reader->read()) {
			if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'product') {
				$product = new SimpleXMLElement($reader->readOuterXML());

				if($feed_type == 'tradetracker'){
					$existing_product = $wpdb->get_row($wpdb->prepare(
						"SELECT * FROM " . $wpdb->prefix . 'phft_products' . " WHERE product_id = %d AND campaign_id = %d AND feed_id = %d",
						(int)$product['ID'],
						(int)$product->campaignID,
						$feed_id
					));
					$mapped_product = new \Koen12344\AffiliateProductHighlights\Provider\TradeTracker\ProductMapping($product);
				}elseif($feed_type == 'adtraction'){
					$existing_product = $wpdb->get_row($wpdb->prepare(
						"SELECT * FROM " . $wpdb->prefix . 'phft_products' . " WHERE sku = %s AND feed_id = %d",
						(string)$product->SKU,
						$feed_id
					));
					$mapped_product = new ProductMapping($product);
				}else{
					break;
				}


				$product_data = [
					'feed_id'               => $feed_id,
					'imported_at'           => current_time('mysql', true),
				];

				$product_data = array_merge($product_data, $mapped_product->get_product_mapping());

				if ($existing_product) {
					$wpdb->update($wpdb->prefix . 'phft_products', $product_data, [
						'id' => $existing_product->id,
					]);

					$inserted_id = $existing_product->id;
				} else {
					$product_slug = sanitize_title($product_data['product_name']);
					$product_data['slug'] = $product_slug;
					$inserted = false;
					$suffix = 1;

					while(!$inserted){
						$result = @$wpdb->insert($wpdb->prefix . 'phft_products', $product_data);
						if($result){
							$inserted = true;
						}else{
							if($wpdb->last_error && strpos($wpdb->last_error, 'Duplicate entry') !== false){
								$product_data['slug'] = $product_slug . '-' . $suffix;
								$suffix++;
							}else{
								break;
							}
						}

					}


					$inserted_id = $wpdb->insert_id;
				}

				$this->import_images($feed_id, (int)$inserted_id, $mapped_product->get_product_images());
			}
		}

		$reader->close();
	}

	private function split_feed($item){
		$temp_file = $item['temp_file'];

		if(!function_exists('wp_tempnam')){
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}

		$reader = new XMLReader();
		$reader->open($temp_file);
		$product_counter = 0;

		$output_xml = new SimpleXMLElement('<products/>');
		while($reader->read()){
			if ($reader->nodeType == XMLReader::ELEMENT && $reader->name === 'product') {
				$product = new SimpleXMLElement($reader->readOuterXML());
				$node = dom_import_simplexml($output_xml->addChild('product'));
				$node->parentNode->replaceChild($node->ownerDocument->importNode(dom_import_simplexml($product), true), $node);


				$product_counter++;

				if ($product_counter % 100 == 0) {
					$output_file = \wp_tempnam();
					$output_xml->asXML($output_file);

					// Reset the output XML for the next file
					$output_xml = new SimpleXMLElement('<products/>');

					$this->push_to_queue([
						'action'        => 'import_chunk',
						'chunk_file'    => $output_file,
						'feed_id'       => $item['feed_id'],
						'feed_type'     => $item['feed_type'],
					]);
				}
			}
		}

		// Save the remaining products in the last file
		if ($product_counter % 100 != 0) {
			$output_file = \wp_tempnam();
			$output_xml->asXML($output_file);
			$this->push_to_queue([
				'action'        => 'import_chunk',
				'chunk_file'    => $output_file,
				'feed_id'       => $item['feed_id'],
				'feed_type'     => $item['feed_type'],
			]);
		}

		$this->save();

		$reader->close();

		@unlink($temp_file);

		return false;
	}
	private function download_feed($item){

		$xml_url = get_post_meta($item['feed_id'], '_phft_feed_url', true);

		$temp_file = $this->download_xml_file($xml_url);

		update_post_meta($item['feed_id'], '_phft_last_import', time());

		$item['action'] = 'split_feed';
		$item['feed_type'] = $this->get_affiliate_network($xml_url);
		$item['temp_file'] = $temp_file;

		return $item;
	}

	private function import_chunk( $item ) {
		$chunk_file = $item['chunk_file'];
		$this->import_xml($chunk_file, $item['feed_id'], $item['feed_type']);

		@unlink($chunk_file);

		return false;
	}

	private function get_affiliate_network($url) {
	    $parsed_url = parse_url($url);
	    $host = $parsed_url['host'];


	    $networks = [
	        'adtraction' => 'adtraction.com',
	        'tradetracker' => 'tradetracker.net',
	    ];


	    foreach ($networks as $network => $pattern) {
	        if (strpos($host, $pattern) !== false) {
	            return $network;
	        }
		}

		return 'unknown';
	}
}