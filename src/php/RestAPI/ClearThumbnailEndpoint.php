<?php

namespace Koen12344\AffiliateProductHighlights\RestAPI;

use Koen12344\AffiliateProductHighlights\RestAPI\EndpointInterface;
use WP_REST_Request;
use WP_REST_Server;
use wpdb;

class ClearThumbnailEndpoint implements EndpointInterface {

	private wpdb $wpdb;

	public function __construct(wpdb $wpdb){

		$this->wpdb = $wpdb;
	}
	public function get_arguments(): array {
		return [];
	}

	public function respond( WP_REST_Request $request ) {
		//Delete all sideloaded media
		$wp_media = $this->wpdb->get_results("SELECT wp_media_id FROM {$this->wpdb->prefix}phft_images WHERE wp_media_id > 0");
		if($wp_media){
			foreach($wp_media as $media){
				wp_delete_attachment($media->wp_media_id, true);
			}
		}
		return rest_ensure_response(true);
	}

	public function validate( WP_REST_Request $request ): bool {
		return current_user_can('manage_options');
	}

	public function get_methods(): array {
		return [ WP_REST_Server::DELETABLE ];
	}

	public function get_path(): string {
		return '/thumbnails/';
	}
}
