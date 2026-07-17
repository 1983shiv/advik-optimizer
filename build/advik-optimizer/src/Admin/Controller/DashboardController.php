<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Admin\Controller;

use AdvikLabs\Optimizer\Admin\View\DashboardView;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheStatsService;
use AdvikLabs\Optimizer\Domain\Vitals\Repository\AuditRepository;
use AdvikLabs\Optimizer\Domain\Vitals\Service\ScoreAggregatorService;

class DashboardController extends AbstractController {

	private DashboardView $view;
	private ScoreAggregatorService $scoreAggregator;
	private CacheStatsService $cacheStatsService;
	private AuditRepository $auditRepository;

	public function __construct(
		DashboardView $view,
		ScoreAggregatorService $scoreAggregator,
		CacheStatsService $cacheStatsService,
		AuditRepository $auditRepository
	) {
		$this->view              = $view;
		$this->scoreAggregator   = $scoreAggregator;
		$this->cacheStatsService = $cacheStatsService;
		$this->auditRepository   = $auditRepository;
	}

	public function index(): void {
		$this->verifyCapability();

		$ranges = [ '7d', '30d', '90d' ];

		$deviceData = [];
		foreach ( [ 'mobile', 'desktop' ] as $device ) {
			$scores  = $this->scoreAggregator->currentScores( $device );
			$metrics = $this->scoreAggregator->latestMetrics( $device );

			$trends = [];
			foreach ( $ranges as $range ) {
				foreach ( [ 'lcp', 'cls', 'inp' ] as $metric ) {
					$trends[ $metric ][ $range ] = $this->scoreAggregator->trend( $metric, $range, $device );
				}
			}

			$audits = $this->auditRepository->getByDevice( $device, 20 );

			$deviceData[ $device ] = compact( 'scores', 'metrics', 'trends', 'audits' );
		}

		$cacheStats = $this->cacheStatsService->summary();

		$settings  = get_option( 'advik_optimizer_settings', [] );
		$hasApiKey = ! empty( $settings['vitals_psi_api_key'] ?? '' );

		$notice = null;
		if ( '1' === ( $_GET['scanned'] ?? '' ) ) {
			$mobileScores = $deviceData['mobile']['scores'];
			$total = array_sum( $mobileScores );
			if ( $total > 0 ) {
				$notice = [ 'success', __( 'Scan complete &mdash; dashboard updated.', 'advik-optimizer' ) ];
			} elseif ( ! $hasApiKey ) {
				$notice = [ 'warning', __( 'Scan finished but no data was returned. Configure a PageSpeed Insights API key on the <a href="admin.php?page=advik-optimizer-settings&tab=vitals">Core Web Vitals settings tab</a> to enable lab scans.', 'advik-optimizer' ) ];
			} else {
				$notice = [ 'warning', __( 'Scan finished but the PageSpeed Insights API returned no data. Make sure your site is publicly accessible.', 'advik-optimizer' ) ];
			}
		}

		$this->view->render(
			'dashboard',
			[
				'deviceData' => $deviceData,
				'cacheStats' => $cacheStats,
				'notice'     => $notice,
				'hasApiKey'  => $hasApiKey,
			]
		);
	}
}
