<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Rest\Controller;

use AdvikLabs\Optimizer\Domain\Vitals\Service\ScoreAggregatorService;
use AdvikLabs\Optimizer\Domain\Vitals\Service\VitalsIngestService;

class VitalsController extends AbstractRestController {

	private ScoreAggregatorService $scoreAggregator;
	private VitalsIngestService $ingestService;

	public function __construct(
		ScoreAggregatorService $scoreAggregator,
		VitalsIngestService $ingestService
	) {
		$this->scoreAggregator = $scoreAggregator;
		$this->ingestService   = $ingestService;
	}

	public function trend( \WP_REST_Request $request ): \WP_REST_Response {
		$metricType = sanitize_key( $request->get_param( 'metric' ) ?? 'lcp' );
		$range      = sanitize_key( $request->get_param( 'range' ) ?? '7d' );
		$device     = sanitize_key( $request->get_param( 'device' ) ?? 'mobile' );

		$data = $this->scoreAggregator->trend( $metricType, $range, $device );

		return $this->success(
			[
				'metric' => $metricType,
				'range' => $range,
				'data' => $data,
			]
		);
	}

	public function ingest( \WP_REST_Request $request ): \WP_REST_Response {
		$payload = $request->get_json_params();

		if ( empty( $payload ) || empty( $payload['url'] ) ) {
			return $this->error( 'advik_invalid_payload', 'Invalid RUM payload.', 400 );
		}

		$settings = get_option( 'advik_optimizer_settings', [] );
		$sampling = (int) ( $settings['vitals_sampling_rate'] ?? 10 );

		if ( $sampling < 100 && wp_rand( 1, 100 ) > $sampling ) {
			return $this->success( [ 'sampled_out' => true ] );
		}

		$this->ingestService->ingestFieldData( $payload );

		return $this->success( [ 'recorded' => true ] );
	}
}
