<?php

namespace Koen12344\AffiliateProductHighlights\Provider\TradeTracker;

use Koen12344\AffiliateProductHighlights\Provider\ProductMapInterface;
use SimpleXMLElement;

class ProductMapping implements ProductMapInterface {

	/**
	 * @var SimpleXMLElement
	 */
	private $product_xml;

	public function __construct( SimpleXMLElement $product_xml ) {
		$this->product_xml = $product_xml;
	}

	public function get_product_mapping(): array {
		return [
			'campaign_id'           => (int)$this->product_xml->campaignID,
			'product_id'            => (int)$this->product_xml['ID'],
			'product_name'          => (string)$this->product_xml->name,
			'product_price'         => number_format((float)$this->product_xml->price, 2,'.', ''),
			'product_currency'      => (string)$this->product_xml->price['currency'],
			'product_url'           => sanitize_url((string)$this->product_xml->URL),
			'product_description'   => (string)$this->product_xml->description,
			'product_ean'           => $this->get_product_ean(),
		];
	}

	public function get_product_images(): array {
		return array_map(function($image) {
			return (string)$image->image;
		}, iterator_to_array($this->product_xml->images));
	}

	private function get_product_ean() {
		foreach ($this->product_xml->properties->property as $property) {
			if ((string)$property['name'] === 'GTIN') {
				return (string)$property->value;
			}
		}
		return null;
	}
}
