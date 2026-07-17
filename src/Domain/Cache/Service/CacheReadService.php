<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Cache\Service;

class CacheReadService {

	private CacheManager $manager;

	public function __construct( CacheManager $manager ) {
		$this->manager = $manager;
	}

	public function get( string $key ): ?string {
		return $this->manager->store()->get( $key );
	}
}
