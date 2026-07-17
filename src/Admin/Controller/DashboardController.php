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

		$mobileScores   = $this->scoreAggregator->currentScores( 'mobile' );
		$mobileMetrics  = $this->scoreAggregator->latestMetrics( 'mobile' );
		$mobileLcpTrend = $this->scoreAggregator->trend( 'lcp', '7d', 'mobile' );
		$mobileClsTrend = $this->scoreAggregator->trend( 'cls', '7d', 'mobile' );
		$mobileInpTrend = $this->scoreAggregator->trend( 'inp', '7d', 'mobile' );

		$desktopScores   = $this->scoreAggregator->currentScores( 'desktop' );
		$desktopMetrics  = $this->scoreAggregator->latestMetrics( 'desktop' );
		$desktopLcpTrend = $this->scoreAggregator->trend( 'lcp', '7d', 'desktop' );
		$desktopClsTrend = $this->scoreAggregator->trend( 'cls', '7d', 'desktop' );
		$desktopInpTrend = $this->scoreAggregator->trend( 'inp', '7d', 'desktop' );

		$cacheStats = $this->cacheStatsService->summary();

		$mobileAudits  = $this->auditRepository->getByDevice( 'mobile', 20 );
		$desktopAudits = $this->auditRepository->getByDevice( 'desktop', 20 );

		$settings  = get_option( 'advik_optimizer_settings', [] );
		$hasApiKey = ! empty( $settings['vitals_psi_api_key'] ?? '' );

		$notice = null;
		if ( '1' === ( $_GET['scanned'] ?? '' ) ) {
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
				'deviceData' => [
					'mobile'  => [
						'scores'  => $mobileScores,
						'metrics' => $mobileMetrics,
						'trends'  => [
							'lcp' => $mobileLcpTrend,
							'cls' => $mobileClsTrend,
							'inp' => $mobileInpTrend,
						],
						'audits'  => $mobileAudits,
					],
					'desktop' => [
						'scores'  => $desktopScores,
						'metrics' => $desktopMetrics,
						'trends'  => [
							'lcp' => $desktopLcpTrend,
							'cls' => $desktopClsTrend,
							'inp' => $desktopInpTrend,
						],
						'audits'  => $desktopAudits,
					],
				],
				'cacheStats' => $cacheStats,
				'notice'     => $notice,
				'hasApiKey'  => $hasApiKey,
			]
		);
	}
}
