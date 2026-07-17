<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Cache\Service;

use AdvikLabs\Optimizer\Domain\Cache\Repository\CacheLogRepository;
use AdvikLabs\Optimizer\Domain\Cache\Store\FileCacheStore;

class CacheStatsService {

	private CacheManager $manager;
	private CacheLogRepository $logRepository;

	public function __construct( CacheManager $manager, CacheLogRepository $logRepository ) {
		$this->manager       = $manager;
		$this->logRepository = $logRepository;
	}

	public function summary(): array {
		$store = $this->manager->store();

		$size      = 0;
		$fileCount = 0;

		if ( $store instanceof FileCacheStore ) {
			$dir = $store->getBaseDir();

			if ( is_dir( $dir ) ) {
				$files = glob( $dir . '/*.cache' );

				if ( false !== $files ) {
					foreach ( $files as $file ) {
						$size += filesize( $file );
						++$fileCount;
					}
				}
			}
		}

		$writeCount = $this->logRepository->countByAction( 'write' );
		$purgeCount = $this->logRepository->countByAction( 'purge' );

		$hitRate = 0;

		if ( $writeCount + $purgeCount > 0 ) {
			$hitRate = round( ( $writeCount / max( $writeCount + $purgeCount, 1 ) ) * 100 );
		}

		return [
			'size'        => $size,
			'file_count'  => $fileCount,
			'write_count' => $writeCount,
			'purge_count' => $purgeCount,
			'hit_rate'    => $hitRate,
		];
	}
}
