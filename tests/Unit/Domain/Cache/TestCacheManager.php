<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Cache;

use AdvikLabs\Optimizer\Domain\Cache\Service\CacheManager;
use AdvikLabs\Optimizer\Domain\Cache\Store\FileCacheStore;
use PHPUnit\Framework\TestCase;

class TestCacheManager extends TestCase {

	public function testTtlReturnsDefaultWhenNotSet(): void {
		$manager = new CacheManager( [] );

		$this->assertSame( 3600, $manager->ttl() );
	}

	public function testTtlReturnsConfiguredValue(): void {
		$manager = new CacheManager( [ 'cache_ttl' => 7200 ] );

		$this->assertSame( 7200, $manager->ttl() );
	}

	public function testIsEnabledReturnsFalseWhenModuleDisabled(): void {
		$manager = new CacheManager( [ 'module_cache' => false ] );

		$this->assertFalse( $manager->isEnabled() );
	}

	public function testIsEnabledReturnsFalseByDefault(): void {
		$manager = new CacheManager( [] );

		$this->assertFalse( $manager->isEnabled() );
	}

	public function testIsEnabledReturnsTrueWhenModuleEnabled(): void {
		$manager = new CacheManager( [ 'module_cache' => true ] );

		$this->assertTrue( $manager->isEnabled() );
	}

	public function testStoreReturnsFileCacheStoreInstance(): void {
		$manager = new CacheManager( [] );

		$this->assertInstanceOf( FileCacheStore::class, $manager->store() );
	}

	public function testStoreReturnsSameInstanceOnMultipleCalls(): void {
		$manager = new CacheManager( [] );

		$store1 = $manager->store();
		$store2 = $manager->store();

		$this->assertSame( $store1, $store2 );
	}
}
