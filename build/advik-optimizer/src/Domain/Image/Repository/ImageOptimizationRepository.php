<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Image\Repository;

use AdvikLabs\Optimizer\Domain\Image\Model\OptimizationRecord;

class ImageOptimizationRepository {

	private \wpdb $wpdb;
	private string $table;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . 'advik_image_optimizations';
	}

	public function insert( int $attachmentId, int $originalSize, string $status = 'pending' ): int {
		$this->wpdb->insert(
			$this->table,
			[
				'attachment_id'  => $attachmentId,
				'original_size'  => $originalSize,
				'status'         => $status,
				'updated_at'     => current_time( 'mysql' ),
			],
			[ '%d', '%d', '%s', '%s' ]
		);

		return $this->wpdb->insert_id;
	}

	public function update( int $id, array $data ): void {
		$data['updated_at'] = current_time( 'mysql' );

		$columnTypes = [
			'optimized_size' => '%d',
			'format'         => '%s',
			'status'         => '%s',
			'updated_at'     => '%s',
		];

		$setClauses = [];
		$values     = [];
		foreach ( $data as $column => $value ) {
			$type          = $columnTypes[ $column ] ?? '%s';
			$setClauses[]  = "{$column} = {$type}";
			$values[]      = $value;
		}
		$values[] = $id;
		$setSql   = implode( ', ', $setClauses );
		$table    = $this->table;

		$this->wpdb->query(
			$this->wpdb->prepare( "UPDATE {$table} SET {$setSql} WHERE id = %d", ...$values )
		);
	}

	public function findByAttachmentId( int $attachmentId ): ?OptimizationRecord {
		$table = $this->table;
		$sql   = $this->wpdb->prepare(
			"SELECT * FROM {$table} WHERE attachment_id = %d ORDER BY id DESC LIMIT 1",
			$attachmentId
		);

		$results = $this->wpdb->get_results( $sql, ARRAY_A );

		if ( ! is_array( $results ) || empty( $results ) ) {
			return null;
		}

		return $this->mapRow( $results[0] );
	}

	public function findById( int $id ): ?OptimizationRecord {
		$table = $this->table;
		$sql   = $this->wpdb->prepare(
			"SELECT * FROM {$table} WHERE id = %d",
			$id
		);

		$results = $this->wpdb->get_results( $sql, ARRAY_A );

		if ( ! is_array( $results ) || empty( $results ) ) {
			return null;
		}

		return $this->mapRow( $results[0] );
	}

	public function getPending(): array {
		$table = $this->table;
		$sql   = $this->wpdb->prepare(
			"SELECT * FROM {$table} WHERE status = %s ORDER BY id ASC",
			'pending'
		);

		$results = $this->wpdb->get_results( $sql, ARRAY_A );

		if ( ! is_array( $results ) ) {
			return [];
		}

		return array_map( [ $this, 'mapRow' ], $results );
	}

	public function getAll(): array {
		$table = $this->table;
		$sql   = "SELECT * FROM {$table} ORDER BY updated_at DESC";

		$results = $this->wpdb->get_results( $sql, ARRAY_A );

		if ( ! is_array( $results ) ) {
			return [];
		}

		return array_map( [ $this, 'mapRow' ], $results );
	}

	public function getByStatus( string $status ): array {
		$table = $this->table;
		$sql   = $this->wpdb->prepare(
			"SELECT * FROM {$table} WHERE status = %s ORDER BY updated_at DESC",
			$status
		);

		$results = $this->wpdb->get_results( $sql, ARRAY_A );

		if ( ! is_array( $results ) ) {
			return [];
		}

		return array_map( [ $this, 'mapRow' ], $results );
	}

	public function getTotalSavings(): int {
		$table = $this->table;
		$sql   = "SELECT COALESCE(SUM(original_size - optimized_size), 0) FROM {$table} WHERE status = 'done' AND optimized_size IS NOT NULL";

		return (int) $this->wpdb->get_var( $sql );
	}

	public function getDoneCount(): int {
		$table = $this->table;
		$sql   = $this->wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE status = %s",
			'done'
		);

		return (int) $this->wpdb->get_var( $sql );
	}

	public function getTotalOriginalSize(): int {
		$table = $this->table;
		$sql   = "SELECT COALESCE(SUM(original_size), 0) FROM {$table} WHERE status = 'done'";

		return (int) $this->wpdb->get_var( $sql );
	}

	public function getUnprocessedAttachmentIds( int $limit = 50 ): array {
		$processed = $this->getAll();
		$exclude   = [];

		foreach ( $processed as $record ) {
			$exclude[] = $record->getAttachmentId();
		}

		$excludeSql = empty( $exclude )
			? ''
			: 'AND p.ID NOT IN (' . implode( ',', array_map( 'intval', $exclude ) ) . ')';

		$sql = "SELECT p.ID FROM {$this->wpdb->posts} p
                WHERE p.post_type = 'attachment'
                AND p.post_mime_type IN ('image/jpeg','image/png','image/gif')
                AND p.post_status = 'inherit'
                {$excludeSql}
                ORDER BY p.ID ASC
                LIMIT " . (int) $limit;

		$results = $this->wpdb->get_col( $sql );

		return is_array( $results ) ? array_map( 'intval', $results ) : [];
	}

	public function deleteByAttachmentId( int $attachmentId ): void {
		$table = $this->table;
		$this->wpdb->query(
			$this->wpdb->prepare( "DELETE FROM {$table} WHERE attachment_id = %d", $attachmentId )
		);
	}

	private function mapRow( array $row ): OptimizationRecord {
		return new OptimizationRecord(
			(int) $row['id'],
			(int) $row['attachment_id'],
			(int) $row['original_size'],
			isset( $row['optimized_size'] ) ? (int) $row['optimized_size'] : null,
			$row['format'] ?? null,
			$row['status'],
			$row['updated_at']
		);
	}
}
