<?php

namespace Koen12344\AffiliateProductHighlights;
use Koen12344\AffiliateProductHighlights\BackgroundProcessing\BackgroundProcess;
use Koen12344\AffiliateProductHighlights\Configuration\EventManagementConfiguration;
use Koen12344\AffiliateProductHighlights\Configuration\MetaboxConfiguration;
use Koen12344\AffiliateProductHighlights\Configuration\PostTypeConfiguration;
use Koen12344\AffiliateProductHighlights\Configuration\RestApiConfiguration;
use Koen12344\AffiliateProductHighlights\Configuration\WordPressConfiguration;
use Koen12344\AffiliateProductHighlights\DependencyInjection\Container;
use Koen12344\AffiliateProductHighlights\PostTypes\FeedPostType;
use NumberFormatter;

class Plugin {

	const DOMAIN = 'affiliate-product-highlights';

	const VERSION = '0.2.0';

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

		$this->container->register('BackgroundProcess', function($container){
			return new BackgroundProcess();
		});



		add_action('admin_menu', [$this, 'add_admin_page']);


		add_action("save_post_phft-feeds", [$this, 'save_feed_metabox'], 10, 3);

		add_action("before_delete_post", [$this, 'delete_feed'], 10 ,2);

		add_action('phft_update_feeds', [$this, 'update_feeds']);

		add_action('admin_enqueue_scripts', [$this, 'enqueue_metabox_script']);

		add_shortcode('product-highlights', [$this, 'display_products_shortcode']);

		add_shortcode('phft-link', [$this, 'product_link_shortcode']);

		add_action('init', function(){
			add_rewrite_rule('^phft/([^/]+)/?$', 'index.php?phft_product=$matches[1]', 'top');
			add_rewrite_tag('%phft_product%', '([^/]+)');
		});

		add_action('template_redirect', function(){
			$product_slug = get_query_var('phft_product');
			if($product_slug){
				global $wpdb;
				$product = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}phft_products WHERE slug = %s", $product_slug));
				if($product){
					wp_redirect($product->product_url, 302);
					exit;
				}
			}
		});
	}

	public static function activate(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;

		$products_table = $wpdb->prefix . 'phft_products';

		$images_table = $wpdb->prefix.'phft_images';

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
	 	product_ean varchar(13) NOT NULL,
	 	slug varchar(255) NOT NULL,
	 	imported_at datetime NOT NULL,
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
    		FOREIGN KEY (product_id) REFERENCES $products_table(id) ON DELETE CASCADE,
    		KEY image_url_idx (image_url)
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
	}

	public function enqueue_styles() {
		wp_register_style('phft-style', plugins_url('../../css/front.css', __FILE__));
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
		return wp_get_attachment_image_url($wp_media_id);
	}




	public function display_products_shortcode($atts){
		global $wpdb;

		$this->enqueue_styles();

		$atts = shortcode_atts([
			'limit'=> 6,
			'product_ids' => null,
			'feed_id' => null,
			'search' => null,
			'random' => null,
			'selection' => null,
		], $atts, 'product-highlights');

		$query = "SELECT p.*, i.image_url, i.wp_media_id, i.id AS image_id FROM {$wpdb->prefix}phft_products p LEFT JOIN {$wpdb->prefix}phft_images i ON p.id = i.product_id";
		$params = [];

		if(!is_null($atts['selection'])){
			$item_selection = get_post_meta((int)$atts['selection'], '_phft_item_selection', true);
			$ids = array_keys($item_selection);
			$atts['product_ids'] = implode(',', $ids);
		}

		if(!is_null($atts['product_ids'])){
			$product_ids = explode(',', $atts['product_ids']);
			$placeholders = implode(',', array_fill(0, count($product_ids), '%d'));
			$query .= " WHERE p.id IN ({$placeholders})";
			$params = array_merge($params, $product_ids);
		}

		if(!is_null($atts['search'])){
			$search_term = $wpdb->esc_like($atts['search']);
			$query .= " WHERE product_name LIKE '%".esc_sql($search_term)."%'";
		}

		if(!is_null($atts['random'])){
			$query .= " ORDER BY RAND()";
		}


		$query .= " LIMIT %d";
		$params[] = $atts['limit'];

		$query = $wpdb->prepare($query,
			$params
		);

		$results = $wpdb->get_results($query);

		if(!$results){
			return $wpdb->print_error();
		}

		$products = [];
		foreach($results as $result){
			if (!isset($products[$result->id])) {
				$products[$result->id] = $result;
				$products[$result->id]->images = [];
			}
			if (!is_null($result->image_url)) {
				$products[$result->id]->images[] = $this->maybe_sideload_image($result->image_id, $result->wp_media_id, $result->image_url);
			}
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
		$output .= '<a target="_blank" rel="nofollow noopener sponsored" href="'.$product_url.'"><h3>'.mb_strimwidth(esc_html($product->product_name), 0, 70, '...').'</h3></a>';
		if (!empty($product->images)) {
			$output .= '<div class="phft-product-image">';
			$output .= '<a target="_blank" rel="nofollow noopener sponsored" href="'.$product_url.'"><img src="' . esc_url($product->images[0]) . '" alt="' . esc_attr($product->product_name) . '"></a>';
			$output .= '</div>';
		}
		$output .= '<div class="phft-product-description">'. mb_strimwidth(wp_strip_all_tags($product->product_description), 0, 160, '...').'</div>';
		$output .= '<div class="phft-product-price">'.numfmt_format_currency($fmt, $product->product_price, $product->product_currency).($has_sale ? '<span class="phft-original-price">'.numfmt_format_currency($fmt, $product->product_original_price, $product->product_currency).'</span>':'').'</div>';
		$output .= '<a class="phft-button-link" target="_blank" rel="nofollow noopener sponsored" href="'.$product_url.'">'.esc_html__('View').'</a>';
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
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if(!empty($_REQUEST['_phft_feed_url'])){
			$xml_url = sanitize_url($_REQUEST['_phft_feed_url']);
			update_post_meta($post_id, '_phft_feed_url', $xml_url);

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
		]);
		wp_enqueue_script('phft-metabox');

		wp_enqueue_style( 'phft-metabox-style', $this->container->get('plugin_url') . 'build/style-metabox.css', array( 'wp-components' ) );
	}

	public function add_admin_page(){
		add_menu_page(
			__('Affiliate Product Highlights', 'affiliate-product-highlights'),
			'Affiliate Product Highlights',
			'manage_options',
			self::DOMAIN,
			[$this, 'render_admin_page' ]
		);
	}
	public function render_admin_page(){
		echo "hi";
	}

}
