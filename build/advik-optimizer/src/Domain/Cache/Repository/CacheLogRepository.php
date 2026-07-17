<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Cache\Repository;

class CacheLogRepository {

	private \wpdb $wpdb;
	private string $table;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . 'advik_cache_log';
	}

	public function log( string $action, ?string $url = null, ?int $objectId = null ): void {
		$this->wpdb->insert(
			$this->table,
			[
				'action'     => $action,
				'url'        => $url,
				'object_id'  => $objectId,
				'created_at' => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%d', '%s' ]
		);
	}

	public function getRecent( int $limit = 50 ): array {
		$table = $this->table;
		$sql   = $this->wpdb->prepare(
			"SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d",
			$limit
		);

		$results = $this->wpdb->get_results( $sql, ARRAY_A );

		return is_array( $results ) ? $results : [];
	}

	public function countByAction( string $action ): int {
		$table = $this->table;
		$sql   = $this->wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE action = %s",
			$action
		);

		return (int) $this->wpdb->get_var( $sql );
	}

	public function purgeOlderThan( int $days = 30 ): void {
		$table = $this->table;
		$sql   = $this->wpdb->prepare(
			"DELETE FROM {$table} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
			$days
		);
		$this->wpdb->query( $sql );
	}
}
