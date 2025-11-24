<?php

namespace Koen12344\ProductFrame\Logger;

use Psr\Log\AbstractLogger;
use wpdb;


class Logger extends AbstractLogger {

	private wpdb $wpdb;
	private string $table;

	public function __construct(wpdb $wpdb) {
		$this->wpdb = $wpdb;
		$this->table = $wpdb->prefix . "prfr_logs";
	}
	public function log( $level, \Stringable|string $message, array $context = [] ): void {

		$feed_id = $context['feed_id'] ?? null;
		$action  = $context['action'] ?? null;


		$this->wpdb->insert(
			$this->table,
			[
				'feed_id'   => $feed_id,
				'action'    => $action,
				'level'     => $level,
				'message'   => $message,
				'context'   => maybe_serialize($context),
				'created_at'=> current_time('mysql', 1),
			],
			[ '%d', '%s', '%s', '%s', '%s', '%s' ]
		);
	}
}
