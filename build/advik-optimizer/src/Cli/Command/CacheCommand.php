<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Cli\Command;

use AdvikLabs\Optimizer\Domain\Cache\Service\CachePurgeService;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheWarmService;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheStatsService;

class CacheCommand extends AbstractCommand {

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

	public function purge( array $args, array $assocArgs ): void {
		$scope = $assocArgs['scope'] ?? 'all';

		$result = $this->purgeService->purge( [ 'scope' => $scope ] );

		if ( $result ) {
			$this->success( "Cache purged (scope: {$scope})." );
		} else {
			$this->error( 'Cache purge failed.' );
		}
	}

	public function warm( array $args, array $assocArgs ): void {
		$sitemap = $assocArgs['sitemap'] ?? '';

		if ( ! empty( $sitemap ) ) {
			$this->warmService->warmFromSitemap( $sitemap );
			$this->success( "Cache warming triggered from sitemap: {$sitemap}." );
		} else {
			$urls = $args ?? [];

			if ( ! empty( $urls ) ) {
				$this->warmService->warm( $urls );
				$this->success( 'Cache warming triggered for ' . count( $urls ) . ' URLs.' );
			} else {
				$this->error( 'Provide a --sitemap URL or a list of URLs.' );
			}
		}
	}

	public function stats( array $args, array $assocArgs ): void {
		$summary = $this->statsService->summary();

		$this->line( "Cache Hit Rate: {$summary['hit_rate']}%" );
		$this->line( "Files: {$summary['file_count']}" );
		$this->line( "Size: {$summary['size']} bytes" );
		$this->line( "Writes: {$summary['write_count']}" );
		$this->line( "Purges: {$summary['purge_count']}" );
	}
}
