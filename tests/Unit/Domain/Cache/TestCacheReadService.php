<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Cache;

use AdvikLabs\Optimizer\Domain\Cache\Contract\CacheStoreInterface;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheManager;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheReadService;
use PHPUnit\Framework\TestCase;

class TestCacheReadService extends TestCase {

	public function testGetDelegatesToStore(): void {
		$store = $this->createMock( CacheStoreInterface::class );
		$store->expects( $this->once() )
			->method( 'get' )
			->with( 'page-key' )
			->willReturn( '<html>cached</html>' );

		$manager = $this->createMock( CacheManager::class );
		$manager->method( 'store' )->willReturn( $store );

		$service = new CacheReadService( $manager );

		$this->assertSame( '<html>cached</html>', $service->get( 'page-key' ) );
	}

	public function testGetReturnsNullWhenCacheMiss(): void {
		$store = $this->createMock( CacheStoreInterface::class );
		$store->method( 'get' )->willReturn( null );

		$manager = $this->createMock( CacheManager::class );
		$manager->method( 'store' )->willReturn( $store );

		$service = new CacheReadService( $manager );

		$this->assertNull( $service->get( 'missing-key' ) );
	}
}
