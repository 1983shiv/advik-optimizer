<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Cache;

use AdvikLabs\Optimizer\Domain\Cache\Contract\CacheStoreInterface;
use AdvikLabs\Optimizer\Domain\Cache\Repository\CacheLogRepository;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheManager;
use AdvikLabs\Optimizer\Domain\Cache\Service\CachePurgeService;
use PHPUnit\Framework\TestCase;

class TestCachePurgeService extends TestCase {

	public function testPurgeAllFlushesStoreAndLogs(): void {
		$store = $this->createMock( CacheStoreInterface::class );
		$store->expects( $this->once() )->method( 'flush' )->willReturn( true );

		$manager = $this->createMock( CacheManager::class );
		$manager->method( 'store' )->willReturn( $store );

		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->expects( $this->once() )
			->method( 'log' )
			->with( 'purge', null, 0 );

		$service = new CachePurgeService( $manager, $logRepo );
		$result  = $service->purge( [ 'scope' => 'all' ] );

		$this->assertTrue( $result );
	}

	public function testPurgeAllMethod(): void {
		$store = $this->createMock( CacheStoreInterface::class );
		$store->expects( $this->once() )->method( 'flush' )->willReturn( true );

		$manager = $this->createMock( CacheManager::class );
		$manager->method( 'store' )->willReturn( $store );

		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->expects( $this->once() )->method( 'log' );

		$service = new CachePurgeService( $manager, $logRepo );

		$this->assertTrue( $service->purgeAll() );
	}

	public function testPurgeByUrlDeletesFromStoreAndLogs(): void {
		$store = $this->createMock( CacheStoreInterface::class );
		$store->expects( $this->once() )
			->method( 'delete' )
			->with( 'https://example.com/page' )
			->willReturn( true );

		$manager = $this->createMock( CacheManager::class );
		$manager->method( 'store' )->willReturn( $store );

		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->expects( $this->once() )
			->method( 'log' )
			->with( 'purge', 'https://example.com/page' );

		$service = new CachePurgeService( $manager, $logRepo );
		$result  = $service->purge( [
			'scope' => 'single',
			'url'   => 'https://example.com/page',
		] );

		$this->assertTrue( $result );
	}

	public function testPurgeWithNoUrlReturnsFalse(): void {
		$manager = $this->createMock( CacheManager::class );
		$logRepo = $this->createMock( CacheLogRepository::class );

		$service = new CachePurgeService( $manager, $logRepo );
		$result  = $service->purge( [ 'scope' => 'single' ] );

		$this->assertFalse( $result );
	}

	public function testPurgeForObjectFlushesAndLogsWithObjectId(): void {
		$store = $this->createMock( CacheStoreInterface::class );
		$store->expects( $this->once() )->method( 'flush' )->willReturn( true );

		$manager = $this->createMock( CacheManager::class );
		$manager->method( 'store' )->willReturn( $store );

		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->expects( $this->once() )
			->method( 'log' )
			->with( 'purge', null, 42 );

		$service = new CachePurgeService( $manager, $logRepo );
		$service->purgeForObject( 42 );
	}
}
