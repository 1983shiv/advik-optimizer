<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Cache\Service;

use AdvikLabs\Optimizer\Domain\Cache\Contract\CacheStoreInterface;
use AdvikLabs\Optimizer\Domain\Cache\Store\FileCacheStore;

class CacheManager {

	private ?CacheStoreInterface $store = null;
	private array $settings;

	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	public function store(): CacheStoreInterface {
		if ( null === $this->store ) {
			$this->store = new FileCacheStore();
		}

		return $this->store;
	}

	public function ttl(): int {
		return (int) ( $this->settings['cache_ttl'] ?? 3600 );
	}

	public function isEnabled(): bool {
		return ! empty( $this->settings['module_cache'] );
	}
}
