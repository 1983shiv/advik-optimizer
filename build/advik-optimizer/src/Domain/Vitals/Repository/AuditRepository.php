<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Vitals\Repository;

use AdvikLabs\Optimizer\Domain\Vitals\Model\LabAudit;
use wpdb;

class AuditRepository {

	private wpdb $wpdb;
	private string $table;

	public function __construct( wpdb $wpdb ) {
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . 'advik_audits';
	}

	public function storeBatch( array $audits, string $device ): void {
		$this->clearByDevice( $device );

		foreach ( $audits as $audit ) {
			if ( ! $audit instanceof LabAudit ) {
				continue;
			}
			$this->wpdb->insert(
				$this->table,
				[
					'url_hash'            => md5( $audit->getAuditId() ),
					'audit_id'            => $audit->getAuditId(),
					'title'               => $audit->getTitle(),
					'description'         => $audit->getDescription(),
					'score'               => $audit->getScore(),
					'severity'            => $audit->getSeverity(),
					'category'            => $audit->getCategory(),
					'estimated_savings_ms' => $audit->getEstimatedSavingsMs(),
					'device'              => $device,
					'recorded_at'         => $audit->getRecordedAt(),
				],
				[ '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%d', '%s', '%s' ]
			);
		}
	}

	public function getByDevice( string $device = 'mobile', int $limit = 50 ): array {
		$sql = $this->wpdb->prepare(
			"SELECT * FROM {$this->table}
             WHERE device = %s
             ORDER BY FIELD(severity, 'error', 'warning', 'info'), estimated_savings_ms DESC
             LIMIT %d",
			$device,
			$limit
		);

		$rows = $this->wpdb->get_results( $sql, ARRAY_A );

		return array_map(
			function ( array $row ): LabAudit {
				return LabAudit::fromArray( $row );
			},
			$rows
		);
	}

	public function clearByDevice( string $device ): void {
		$this->wpdb->delete( $this->table, [ 'device' => $device ], [ '%s' ] );
	}
}
