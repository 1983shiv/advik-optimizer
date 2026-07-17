<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Rest\Controller;

use AdvikLabs\Optimizer\Domain\Vitals\Service\ScoreAggregatorService;

class ScoreController extends AbstractRestController {

	private ScoreAggregatorService $scoreAggregator;

	public function __construct( ScoreAggregatorService $scoreAggregator ) {
		$this->scoreAggregator = $scoreAggregator;
	}

	public function index( \WP_REST_Request $request ): \WP_REST_Response {
		$device = sanitize_key( $request->get_param( 'device' ) ?? 'mobile' );
		$scores = $this->scoreAggregator->currentScores( $device );
		$metrics = $this->scoreAggregator->latestMetrics( $device );
		$metricTimestamps = $this->scoreAggregator->latestMetricsWithTimestamps( $device );
		$lastScanAt = get_option( 'advik_optimizer_last_scan_at', null );

		$scanDataFresh = false;
		if ( $lastScanAt ) {
			foreach ( $metricTimestamps as $ts ) {
				if ( $ts && ( $ts['recorded_at'] ?? '' ) >= $lastScanAt ) {
					$scanDataFresh = true;
					break;
				}
			}
		}

		return $this->success(
			[
				'scores'          => $scores,
				'metrics'         => $metrics,
				'metric_timestamps' => $metricTimestamps,
				'last_scan_at'    => $lastScanAt,
				'scan_data_fresh' => $scanDataFresh,
			]
		);
	}
}
