<?php

namespace Koen12344\AffiliateProductHighlights;
use Koen12344\AffiliateProductHighlights\BackgroundProcessing\BackgroundProcess;
use Koen12344\AffiliateProductHighlights\Configuration\AdminConfiguration;
use Koen12344\AffiliateProductHighlights\Configuration\EventManagementConfiguration;
use Koen12344\AffiliateProductHighlights\Configuration\MetaboxConfiguration;
use Koen12344\AffiliateProductHighlights\Configuration\PostTypeConfiguration;
use Koen12344\AffiliateProductHighlights\Configuration\RestApiConfiguration;
use Koen12344\AffiliateProductHighlights\Configuration\WordPressConfiguration;
use Koen12344\AffiliateProductHighlights\DependencyInjection\Container;
use Koen12344\AffiliateProductHighlights\Logger\Logger;
use Koen12344\AffiliateProductHighlights\PostTypes\FeedPostType;
use Koen12344\APH_Vendor\Koen12344\GithubPluginUpdater\Updater;
use NumberFormatter;

class Plugin {

	const DOMAIN = 'affiliate-product-highlights';

	const VERSION = '0.4.5';

	const REST_NAMESPACE = 'phft/v1';

	private $container;

	private $loaded;

	private $background_process;

	public function __construct($file){
		$this->loaded = false;

		$this->container = new Container([
			'plugin_basename'       => plugin_basename($file),
			'plugin_domain'         => self::DOMAIN,
			'plugin_path'           => plugin_dir_path($file),
			'plugin_relative_path'  => basename(plugin_dir_path($file)),
			'plugin_url'            => plugin_dir_url($file),
			'plugin_version'        => self::VERSION,
			'plugin_rest_namespace' => self::REST_NAMESPACE,
		]);

		$this->container->register('Logger', function($container) {
			global $wpdb;
			return new Logger($wpdb);
		});

		$this->container->register('BackgroundProcess', function($container){
			return new BackgroundProcess($this->container->get('Logger'));
		});

		$updater = new Updater(
			new \WP_Http(),
			$file,
			'koen12344',
			'affiliate-product-highlights',
			self::VERSION
		);
		$updater->register();


		add_action("save_post_phft-feeds", [$this, 'save_feed_metabox'], 10, 3);

		add_action("before_delete_post", [$this, 'delete_feed'], 10 ,2);

		add_action('phft_update_feeds', [$this, 'update_feeds']);

		add_action('admin_enqueue_scripts', [$this, 'enqueue_metabox_script']);

		add_shortcode('product-highlights', [$this, 'display_products_shortcode']);

		add_shortcode('phft-link', [$this, 'product_link_shortcode']);

		add_image_size('phft-logo', 100, 30 );
		add_image_size('phft-product-thumb', 0, 150 );
	}

	public static function activate(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;

		$products_table = $wpdb->prefix . 'phft_products';

		$images_table = $wpdb->prefix.'phft_images';

		$logs_table = $wpdb->prefix.'phft_logs';

		$charset_collate = $wpdb->get_charset_collate();

		// Define the table schema
		$sql = "CREATE TABLE $products_table (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        feed_id bigint(20) UNSIGNED NOT NULL,
        campaign_id bigint(20) UNSIGNED DEFAULT NULL,
        product_id bigint(20) UNSIGNED DEFAULT NULL,
        sku varchar(255) DEFAULT NULL,
        product_name varchar(255) NOT NULL,
        product_price DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        product_original_price DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        product_currency varchar(3) NOT NULL,
		product_url varchar(255) NOT NULL,
	 	product_description TEXT,
	 	product_ean varchar(15) NOT NULL,
	 	slug varchar(255) NOT NULL,
	 	imported_at datetime NOT NULL,
        in_latest_import tinyint(1) NOT NULL DEFAULT 1,
	 	PRIMARY KEY  (id),
        UNIQUE KEY feed_campaign_product_unique (feed_id, campaign_id, product_id),
        UNIQUE KEY feed_sku_unique (feed_id, sku),
        UNIQUE KEY slug_unique (slug),
        KEY product_name_idx (product_name)
    ) $charset_collate;";

		$sql  .= "CREATE TABLE $images_table (
    		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    		feed_id bigint(20) UNSIGNED NOT NULL,
    		product_id bigint(20) UNSIGNED NOT NULL,
    		image_url varchar(255) NOT NULL,
    		wp_media_id bigint(20) UNSIGNED NOT NULL,
    		imported_at datetime NOT NULL,
    		PRIMARY KEY  (id),
    		KEY image_url_idx (image_url)
		) $charset_collate;";

		$sql .= "CREATE TABLE $logs_table (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		feed_id BIGINT UNSIGNED NULL,
		action VARCHAR(100) NULL,
		level VARCHAR(20) NOT NULL,
		message TEXT NOT NULL,
		context LONGTEXT NULL,
		created_at DATETIME NOT NULL,
		PRIMARY KEY (id),
		KEY feed_id (feed_id),
		KEY action (action)
	) $charset_collate;";

		dbDelta($sql);

		if(!wp_next_scheduled('phft_update_feeds')){
			wp_schedule_event(time(), 'daily', 'phft_update_feeds');
		}


	}

	public static function deactivate(){

	}

	public static function uninstall(){
		global $wpdb;

		//Delete all sideloaded media
		$wp_media = $wpdb->get_results("SELECT wp_media_id FROM {$wpdb->prefix}phft_images WHERE wp_media_id > 0");
		if($wp_media){
			foreach($wp_media as $media){
				wp_delete_attachment($media->wp_media_id, true);
			}
		}

		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}phft_images,{$wpdb->prefix}phft_products");
	}

	public function is_loaded(): bool {
		return $this->loaded;
	}

	public function init(){
		if($this->is_loaded()){
			return;
		}

		$this->container->configure([
			AdminConfiguration::class,
			MetaboxConfiguration::class,
			PostTypeConfiguration::class,
			EventManagementConfiguration::class,
			WordPressConfiguration::class,
			RestApiConfiguration::class,
		]);

		foreach($this->container['subscribers'] as $subscriber){
			$this->container['service.event_manager']->add_subscriber($subscriber);
		}

		$this->background_process = $this->container['BackgroundProcess'];

		$this->loaded = true;
	}



	// ---

	public function update_feeds(){
		$feeds = get_posts([
			'post_type' => FeedPostType::FEED_POST_TYPE,
			'post_status' => 'publish',
			'numberposts'   => -1,
			'fields'        => 'ids'
		]);
		foreach($feeds as $feed_id){
			$this->background_process->push_to_queue([
				'action'    => 'download_feed',
				'feed_id'   => $feed_id,
			]);
		}

		$this->background_process->save()->dispatch();
		update_option('phft_is_daily_update', true);
	}

	public function enqueue_styles() {
		wp_register_style('phft-style', plugins_url('../../css/front.css', __FILE__), [], self::VERSION);
		wp_enqueue_style('phft-style');
	}


	public function maybe_sideload_image($image_id, $wp_media_id, $url){
		global $wpdb;
		if(!$wp_media_id || !wp_get_attachment_image_url($wp_media_id)){
			require_once(ABSPATH . 'wp-admin/includes/media.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/image.php');

			$wp_media_id = media_sideload_image($url.'#.jpg', 0, null, 'id');

			if(is_wp_error($wp_media_id)){
				return $url;
			}
			$image_data = [
				'wp_media_id' => (int)$wp_media_id
			];
			$wpdb->update($wpdb->prefix . 'phft_images', $image_data, [
				'id' => $image_id,
			]);
		}
		return wp_get_attachment_image_url($wp_media_id, 'phft-product-thumb');
	}




	public function display_products_shortcode($atts){
		global $wpdb;

		$this->enqueue_styles();

		$atts = shortcode_atts([
			'limit'         => 6,
			'product_ids'   => null,
			'feed_id'       => null,
			'search'        => null,
			'random'        => null,
			'selection'     => null,
		], $atts, 'product-highlights');


		$params = [];
		$where_parts = [];

		if(!is_null($atts['selection'])){
			$item_selection = get_post_meta((int)$atts['selection'], '_phft_item_selection', true);
			if(!is_array($item_selection) || empty($item_selection)){
				return esc_html__('The product selection doesn\'t contain any items', 'affiliate-product-highlights');
			}
			$ids = array_keys($item_selection);
			$atts['product_ids'] = implode(',', $ids);
		}

		if(!is_null($atts['product_ids'])){
			$product_ids = explode(',', $atts['product_ids']);
			$placeholders = implode(',', array_fill(0, count($product_ids), '%d'));
			$where_parts[] = "id IN ({$placeholders})";
			$params = array_merge($params, $product_ids);
		}

		if(!is_null($atts['search'])){
			$search_term = $wpdb->esc_like($atts['search']);
			$where_parts[] = "product_name LIKE '%".esc_sql($search_term)."%'";
			$where_parts[] = "in_latest_import=1";
		}



		$where_clause = '';
		if ( ! empty( $where_parts ) ) {
			$where_clause = ' WHERE ' . implode( ' AND ', $where_parts );
		}

		$limit = "";
		if(!is_null($atts['random'])){
			$limit .= " ORDER BY RAND()";
		}

		$limit .= " LIMIT %d";
		$params[] = $atts['limit'];

		$prepared = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}phft_products $where_clause $limit",
			$params
		);

		$cache_key = 'phft_'.md5($prepared);

		$products = wp_cache_get($cache_key, 'phft');

		if(!$products){
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared  -- The query is prepared, but also used as md5 hashed key to fetch from the database
			$results = $wpdb->get_results($prepared, OBJECT_K);

			if(!$results){
				return $wpdb->print_error();
			}

			$product_ids = wp_parse_id_list(array_column($results, 'id'));
			$placeholders = implode(',', array_fill(0, count($product_ids), '%d'));
			$images = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}phft_images WHERE product_id IN ({$placeholders})", $product_ids));

	//		$images_query = "SELECT * FROM {$wpdb->prefix}phft_images WHERE product_id IN ({$product_ids})";
	//		$images = $wpdb->get_results($images_query);
			/*
			 * 			$product_ids = explode(',', $atts['product_ids']);
				$placeholders = implode(',', array_fill(0, count($product_ids), '%d'));
			 */



			$images_by_id = [];
			foreach($images as $image){
				//So we only get the first image
				if(!isset($images_by_id[$image->product_id])){
					$images_by_id[$image->product_id] = $image;
				}
			}

			$products = array_map(function($product) use ( $images_by_id ) {
				$product->images = [];
				if (isset($images_by_id[$product->id])) {
					$product->images[] = $this->maybe_sideload_image($images_by_id[$product->id]->id, $images_by_id[$product->id]->wp_media_id, $images_by_id[$product->id]->image_url);
				}
				return $product;
			}, $results);
			wp_cache_set($cache_key, $products, 'phft', 3600*24);
		}

		if($atts['limit'] > 1){
			$output = '<div class="phft-products-multiple">';
			foreach($products as $product){
				$output .= $this->draw_product($product);
			}
			$output .= '</div>';
			return $output;
		}

		$output = '<div class="phft-products-single">';
		$output .= $this->draw_product(reset($products));
		$output .= '</div>';

		return $output;

	}

	public function draw_product($product){
		$locale = get_locale();
		$fmt = numfmt_create( $locale, NumberFormatter::CURRENCY );
		$product_url = esc_url(trailingslashit(home_url('/phft/' . urlencode($product->slug))));

		$has_sale = $product->product_original_price > $product->product_price;



		$output = '<div class="phft-product'.($has_sale ? ' phft-sale-product' : '').'">';
		$output .= get_the_post_thumbnail($product->feed_id, 'phft-logo');
		$output .= '<a target="_blank" rel="nofollow noopener sponsored" href="'.$product_url.'"><h3>'.mb_strimwidth(esc_html($product->product_name), 0, 70, '...').'</h3></a>';
		if (!empty($product->images)) {
			$output .= '<div class="phft-product-image">';
			$output .= '<a target="_blank" rel="nofollow noopener sponsored" href="'.$product_url.'"><img src="' . esc_url($product->images[0]) . '" alt="' . esc_attr($product->product_name) . '"></a>';
			$output .= '</div>';
		}
		$output .= '<div class="phft-product-description">'. mb_strimwidth(wp_strip_all_tags($product->product_description), 0, 160, '...').'</div>';
		$output .= '<div class="phft-product-price">'.numfmt_format_currency($fmt, $product->product_price, $product->product_currency).($has_sale ? '<span class="phft-original-price">'.numfmt_format_currency($fmt, $product->product_original_price, $product->product_currency).'</span>':'').'</div>';
		$output .= '<a class="phft-button-link" target="_blank" rel="nofollow noopener sponsored" href="'.$product_url.'">'.esc_html__('View', 'affiliate-product-highlights').'</a>';
		$output .= '</div>';

		return $output;
	}


	public function product_link_shortcode($atts, $content){
		global $wpdb;

		$atts = shortcode_atts([
			'product_id' => null,
		], $atts, 'phft-link');

		if($atts['product_id'] === null){
			return $content;
		}

		$product_id = $atts['product_id'];

		$product = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}phft_products WHERE id = %d", $product_id));

		$product_url = esc_url(trailingslashit(home_url('/phft/' . urlencode($product->slug))));

		return '<a target="_blank" rel="nofollow noopener sponsored" href="'.$product_url.'">'.$content.'</a>';
	}

	public function save_feed_metabox($post_id, $post, $update){
		if (!isset($_POST['phft_feed_metabox_nonce']) || !wp_verify_nonce(sanitize_key($_POST['phft_feed_metabox_nonce']), 'phft_save_feed_metabox') || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}


		if(!empty($_REQUEST['_phft_feed_url'])){
			$feed_url = sanitize_url(wp_unslash($_REQUEST['_phft_feed_url']));
			update_post_meta($post_id, '_phft_feed_url', $feed_url);

			$this->background_process->push_to_queue([
				'action'    => 'download_feed',
				'feed_id'   => $post_id,
			]);

			$this->background_process->save()->dispatch();
		}

	}

	public function delete_feed($post_id, \WP_Post $post){
		if($post->post_type !== FeedPostType::FEED_POST_TYPE){
			return;
		}

		global $wpdb;

		$wp_media = $wpdb->get_results($wpdb->prepare("SELECT wp_media_id FROM {$wpdb->prefix}phft_images WHERE feed_id = %d AND wp_media_id > 0", $post_id));

		if($wp_media){
			foreach($wp_media as $media){
				wp_delete_attachment($media->wp_media_id, true);
			}
		}

		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}phft_products WHERE feed_id = %d", $post_id));
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}phft_images WHERE feed_id = %d", $post_id));
	}



	public function enqueue_metabox_script($hook){
		if(!in_array($hook, [ 'post.php', 'post-new.php' ] )){
			return;
		}

		$screen = get_current_screen();
		if(!is_object($screen) || $screen->post_type != 'phft-selections'){
			return;
		}
		$script_assets = require( $this->container->get('plugin_path') . 'build/metabox.asset.php');

		wp_register_script('phft-metabox', $this->container->get('plugin_url').'build/metabox.js', $script_assets['dependencies'], $script_assets['version'], true);
		wp_localize_script('phft-metabox', 'psfg_localize_metabox', [
			'selection_id' => get_the_ID(),
			'userView' => get_user_meta(get_current_user_id(), 'phft_selection_view', true),
		]);
		wp_set_script_translations('phft-metabox', 'affiliate-product-highlights', $this->container->get('plugin_path').'languages');

		wp_enqueue_script('phft-metabox');

		wp_enqueue_style( 'phft-metabox-style', $this->container->get('plugin_url') . 'build/metabox.css', array( 'wp-components' ), $script_assets['version'] );
	}


}
