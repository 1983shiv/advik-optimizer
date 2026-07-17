<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Cache\Service;

use AdvikLabs\Optimizer\Domain\Cache\Repository\CacheLogRepository;

class CacheWriteService {

	private CacheManager $manager;
	private CacheLogRepository $logRepository;

	public function __construct( CacheManager $manager, CacheLogRepository $logRepository ) {
		$this->manager       = $manager;
		$this->logRepository = $logRepository;
	}

	public function put( string $key, string $html ): void {
		$this->manager->store()->put( $key, $html, $this->manager->ttl() );
		$this->logRepository->log( 'write' );
	}
}
