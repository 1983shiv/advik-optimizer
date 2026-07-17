<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Cache\Service;

use AdvikLabs\Optimizer\Domain\Cache\Contract\Purgeable;
use AdvikLabs\Optimizer\Domain\Cache\Repository\CacheLogRepository;

class CachePurgeService implements Purgeable {

	private CacheManager $manager;
	private CacheLogRepository $logRepository;

	public function __construct( CacheManager $manager, CacheLogRepository $logRepository ) {
		$this->manager       = $manager;
		$this->logRepository = $logRepository;
	}

	public function purge( array $context ): bool {
		$scope = $context['scope'] ?? 'all';

		if ( 'all' === $scope ) {
			$result = $this->manager->store()->flush();
			$this->logRepository->log( 'purge', null, 0 );
			do_action( 'advik_optimizer_cache_purged', $scope, $context );

			return $result;
		}

		if ( ! empty( $context['url'] ) ) {
			$result = $this->manager->store()->delete( $context['url'] );
			$this->logRepository->log( 'purge', $context['url'] );
			do_action( 'advik_optimizer_cache_purged', $scope, $context );

			return $result;
		}

		return false;
	}

	public function purgeForObject( int $objectId ): void {
		$this->manager->store()->flush();
		$this->logRepository->log( 'purge', null, $objectId );
		do_action( 'advik_optimizer_cache_purged', 'object', [ 'object_id' => $objectId ] );
	}

	public function purgeAll(): bool {
		return $this->purge( [ 'scope' => 'all' ] );
	}
}
