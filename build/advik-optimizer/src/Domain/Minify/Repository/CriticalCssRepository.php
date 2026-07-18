<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Repository;

use AdvikLabs\Optimizer\Domain\Minify\Model\CriticalCssRule;

class CriticalCssRepository {

	private \wpdb $wpdb;
	private string $table;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . 'advik_critical_css';
	}

	public function save( CriticalCssRule $rule ): int {
		$this->wpdb->replace(
			$this->table,
			[
				'template'   => $rule->getTemplate(),
				'css'        => $rule->getCss(),
				'created_at' => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%s' ]
		);

		$insertId = $this->wpdb->insert_id;
		if ( 0 !== $insertId ) {
			return $insertId;
		}

		return (int) $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT id FROM {$this->table} WHERE template = %s",
				$rule->getTemplate()
			)
		);
	}

	public function findByTemplate( string $template ): ?CriticalCssRule {
		$table = $this->table;
		$sql   = $this->wpdb->prepare(
			"SELECT * FROM {$table} WHERE template = %s ORDER BY created_at DESC LIMIT 1",
			$template
		);

		$results = $this->wpdb->get_results( $sql, ARRAY_A );

		if ( ! is_array( $results ) || empty( $results ) ) {
			return null;
		}

		return $this->mapRow( $results[0] );
	}

	public function getAll(): array {
		$table = $this->table;
		$sql   = "SELECT * FROM {$table} ORDER BY created_at DESC";

		$results = $this->wpdb->get_results( $sql, ARRAY_A );

		if ( ! is_array( $results ) ) {
			return [];
		}

		return array_map( [ $this, 'mapRow' ], $results );
	}

	public function deleteByTemplate( string $template ): void {
		$table = $this->table;
		$this->wpdb->query(
			$this->wpdb->prepare( "DELETE FROM {$table} WHERE template = %s", $template )
		);
	}

	public function getLastScanTime(): ?string {
		$table = $this->table;
		$sql   = "SELECT MAX(created_at) FROM {$table}";

		$result = $this->wpdb->get_var( $sql );

		return $result ? (string) $result : null;
	}

	public function getTotalCssLength(): int {
		$table = $this->table;
		$sql   = "SELECT COALESCE(SUM(LENGTH(css)), 0) FROM {$table}";

		return (int) $this->wpdb->get_var( $sql );
	}

	public function getRuleCount(): int {
		$table = $this->table;
		$sql   = "SELECT COUNT(*) FROM {$table}";

		return (int) $this->wpdb->get_var( $sql );
	}

	private function mapRow( array $row ): CriticalCssRule {
		return new CriticalCssRule(
			(int) $row['id'],
			$row['template'],
			$row['css'],
			$row['created_at']
		);
	}
}
