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

		return $this->success(
			[
				'scores'  => $scores,
				'metrics' => $metrics,
			]
		);
	}
}
