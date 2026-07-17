<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Cache;

use AdvikLabs\Optimizer\Domain\Cache\Contract\CacheStoreInterface;
use AdvikLabs\Optimizer\Domain\Cache\Repository\CacheLogRepository;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheManager;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheWriteService;
use PHPUnit\Framework\TestCase;

class TestCacheWriteService extends TestCase {

	public function testPutDelegatesToStoreAndLogs(): void {
		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->expects( $this->once() )
			->method( 'log' )
			->with( 'write' );

		$store = $this->createMock( CacheStoreInterface::class );
		$store->expects( $this->once() )
			->method( 'put' )
			->with( 'page-key', '<html>content</html>', 3600 );

		$manager = $this->createMock( CacheManager::class );
		$manager->method( 'store' )->willReturn( $store );
		$manager->method( 'ttl' )->willReturn( 3600 );

		$service = new CacheWriteService( $manager, $logRepo );
		$service->put( 'page-key', '<html>content</html>' );
	}

	public function testPutUsesManagerTtl(): void {
		$logRepo = $this->createMock( CacheLogRepository::class );

		$store = $this->createMock( CacheStoreInterface::class );
		$store->expects( $this->once() )
			->method( 'put' )
			->with( 'k', 'v', 7200 );

		$manager = $this->createMock( CacheManager::class );
		$manager->method( 'store' )->willReturn( $store );
		$manager->method( 'ttl' )->willReturn( 7200 );

		$service = new CacheWriteService( $manager, $logRepo );
		$service->put( 'k', 'v' );
	}
}
