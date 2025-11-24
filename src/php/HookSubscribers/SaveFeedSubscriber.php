<?php

namespace Koen12344\ProductFrame\HookSubscribers;

use Koen12344\ProductFrame\EventManagement\SubscriberInterface;

class SaveFeedSubscriber implements SubscriberInterface {

	private $background_process;

	public function __construct($background_process) {

		$this->background_process = $background_process;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_hooks(): array {
		return [
			'save_post_prfr-feeds' => 'save_feed'
		];
	}

	public function save_feed($post_id){
		if (!isset($_POST['prfr_feed_metabox_nonce']) || !wp_verify_nonce(sanitize_key($_POST['prfr_feed_metabox_nonce']), 'prfr_save_feed_metabox') || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}


		if(!empty($_REQUEST['_prfr_feed_url'])){
			$feed_url = sanitize_url(wp_unslash($_REQUEST['_prfr_feed_url']));
			update_post_meta($post_id, '_prfr_feed_url', $feed_url);

			$this->background_process->push_to_queue([
				'action'    => 'download_feed',
				'feed_id'   => $post_id,
			]);

			$this->background_process->save()->dispatch();
		}

	}
}
