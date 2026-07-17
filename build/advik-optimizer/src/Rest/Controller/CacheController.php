<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Rest\Controller;

use AdvikLabs\Optimizer\Domain\Cache\Service\CachePurgeService;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheWarmService;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheStatsService;

class CacheController extends AbstractRestController {

	private CachePurgeService $purgeService;
	private CacheWarmService $warmService;
	private CacheStatsService $statsService;

	public function __construct(
		CachePurgeService $purgeService,
		CacheWarmService $warmService,
		CacheStatsService $statsService
	) {
		$this->purgeService = $purgeService;
		$this->warmService  = $warmService;
		$this->statsService = $statsService;
	}

	public function purge( \WP_REST_Request $request ): \WP_REST_Response {
		$scope = sanitize_key( $request->get_param( 'scope' ) ?? 'all' );
		$url   = esc_url_raw( $request->get_param( 'url' ) ?? '' );

		$result = $this->purgeService->purge(
			[
				'scope' => $scope,
				'url'   => $url,
			]
		);

		if ( $result ) {
			return $this->success(
				[
					'scope' => $scope,
					'url'   => $url,
				]
			);
		}

		return $this->error( 'advik_cache_purge_failed', 'Cache purge failed.', 500 );
	}

	public function warm( \WP_REST_Request $request ): \WP_REST_Response {
		$sitemap = esc_url_raw( $request->get_param( 'sitemap' ) ?? '' );

		if ( ! empty( $sitemap ) ) {
			$this->warmService->warmFromSitemap( $sitemap );
		} else {
			$urls = $request->get_param( 'urls' ) ?? [];

			if ( is_array( $urls ) && ! empty( $urls ) ) {
				$this->warmService->warm( $urls );
			}
		}

		return $this->success( [ 'status' => 'warming' ] );
	}

	public function stats( \WP_REST_Request $request ): \WP_REST_Response {
		$summary = $this->statsService->summary();

		return $this->success( $summary );
	}
}
