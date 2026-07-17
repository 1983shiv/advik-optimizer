<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Vitals\Repository;

use AdvikLabs\Optimizer\Domain\Vitals\Model\VitalMetric;
use wpdb;

class VitalsRepository {

	private wpdb $wpdb;
	private string $table;

	public function __construct( wpdb $wpdb ) {
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . 'advik_cwv_metrics';
	}

	public function store( VitalMetric $metric ): int {
		$result = $this->wpdb->insert(
			$this->table,
			[
				'url_hash'    => $metric->getUrlHash(),
				'url'         => $metric->getUrl(),
				'metric_type' => $metric->getMetricType(),
				'value'       => $metric->getValue(),
				'device'      => $metric->getDevice(),
				'source'      => $metric->getSource(),
				'recorded_at' => $metric->getRecordedAt(),
			],
			[ '%s', '%s', '%s', '%f', '%s', '%s', '%s' ]
		);

		if ( false === $result ) {
			return 0;
		}

		return (int) $this->wpdb->insert_id;
	}

	public function storeBatch( array $metrics ): void {
		foreach ( $metrics as $metric ) {
			$this->store( $metric );
		}
	}

	public function getLatestScore( string $metricType, string $device = 'mobile', string $source = 'lab' ): ?float {
		$sql = $this->wpdb->prepare(
			"SELECT value FROM {$this->table}
             WHERE metric_type = %s AND device = %s AND source = %s
             ORDER BY recorded_at DESC LIMIT 1",
			$metricType,
			$device,
			$source
		);

		$value = $this->wpdb->get_var( $sql );

		return null !== $value ? (float) $value : null;
	}

	public function getLatestScores( string $device = 'mobile', string $source = 'lab' ): array {
		$types = [ 'performance', 'seo', 'accessibility', 'best_practices' ];
		$scores = [];

		foreach ( $types as $type ) {
			$score = $this->getLatestScore( $type, $device, $source );
			if ( null !== $score ) {
				$scores[ $type ] = (int) round( $score );
			}
		}

		return $scores;
	}

	public function getLatestMetricValue( string $metricType, string $device = 'mobile', string $source = 'lab' ): ?float {
		return $this->getLatestScore( $metricType, $device, $source );
	}

	public function getTrend( string $metricType, string $range = '7d', string $device = 'mobile', string $source = 'lab' ): array {
		$days = match ( $range ) {
			'30d' => 30,
			'90d' => 90,
			default => 7,
		};

		$since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$sql = $this->wpdb->prepare(
			"SELECT value, recorded_at FROM {$this->table}
             WHERE metric_type = %s AND device = %s AND source = %s AND recorded_at >= %s
             ORDER BY recorded_at ASC",
			$metricType,
			$device,
			$source,
			$since
		);

		$results = $this->wpdb->get_results( $sql, ARRAY_A );

		return array_map(
			function ( $row ) {
				return [
					'value'       => (float) $row['value'],
					'recorded_at' => $row['recorded_at'],
				];
			},
			$results
		);
	}

	public function getRecentMetrics( int $limit = 100, string $source = 'lab' ): array {
		$sql = $this->wpdb->prepare(
			"SELECT * FROM {$this->table}
             WHERE source = %s
             ORDER BY recorded_at DESC LIMIT %d",
			$source,
			$limit
		);

		$results = $this->wpdb->get_results( $sql, ARRAY_A );
		$metrics = [];

		foreach ( $results as $row ) {
			$metrics[] = VitalMetric::fromArray( $row );
		}

		return $metrics;
	}

	public function purgeOlderThan( int $days = 90 ): void {
		$since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$this->wpdb->query(
			$this->wpdb->prepare(
				"DELETE FROM {$this->table} WHERE recorded_at < %s",
				$since
			)
		);
	}
}
