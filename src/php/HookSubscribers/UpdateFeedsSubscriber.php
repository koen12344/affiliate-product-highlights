<?php

namespace Koen12344\ProductFrame\HookSubscribers;

use Koen12344\ProductFrame\EventManagement\SubscriberInterface;
use Koen12344\ProductFrame\PostTypes\FeedPostType;

class UpdateFeedsSubscriber implements SubscriberInterface {

	private $background_process;

	public function __construct($background_process) {
		$this->background_process = $background_process;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_hooks(): array {
		return [
			'prfr_update_feeds' => 'update_feeds'
		];
	}

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
		update_option('prfr_is_daily_update', true);
	}
}
