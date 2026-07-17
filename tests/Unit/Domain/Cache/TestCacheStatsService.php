<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Cache;

use AdvikLabs\Optimizer\Domain\Cache\Repository\CacheLogRepository;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheManager;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheStatsService;
use AdvikLabs\Optimizer\Domain\Cache\Store\FileCacheStore;
use PHPUnit\Framework\TestCase;

class TestCacheStatsService extends TestCase {

	public function testSummaryReturnsDefaultStatsWhenNoCacheDir(): void {
		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->method( 'countByAction' )
			->willReturnMap( [
				[ 'write', 0 ],
				[ 'purge', 0 ],
			] );

		$store = $this->createMock( FileCacheStore::class );
		$store->method( 'getBaseDir' )->willReturn( '/nonexistent/dir' );

		$manager = $this->createMock( CacheManager::class );
		$manager->method( 'store' )->willReturn( $store );

		$service = new CacheStatsService( $manager, $logRepo );
		$summary = $service->summary();

		$this->assertSame( 0, $summary['size'] );
		$this->assertSame( 0, $summary['file_count'] );
		$this->assertSame( 0, $summary['write_count'] );
		$this->assertSame( 0, $summary['purge_count'] );
		$this->assertSame( 0, $summary['hit_rate'] );
	}

	public function testSummaryCalculatesHitRate(): void {
		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->method( 'countByAction' )
			->willReturnMap( [
				[ 'write', 80 ],
				[ 'purge', 20 ],
			] );

		$store = $this->createMock( FileCacheStore::class );
		$store->method( 'getBaseDir' )->willReturn( '/nonexistent/dir' );

		$manager = $this->createMock( CacheManager::class );
		$manager->method( 'store' )->willReturn( $store );

		$service = new CacheStatsService( $manager, $logRepo );
		$summary = $service->summary();

		$this->assertEquals( 80, $summary['hit_rate'] );
	}

	public function testSummaryHitRateZeroWhenNoData(): void {
		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->method( 'countByAction' )->willReturn( 0 );

		$store = $this->createMock( FileCacheStore::class );
		$store->method( 'getBaseDir' )->willReturn( '/nonexistent/dir' );

		$manager = $this->createMock( CacheManager::class );
		$manager->method( 'store' )->willReturn( $store );

		$service = new CacheStatsService( $manager, $logRepo );
		$summary = $service->summary();

		$this->assertSame( 0, $summary['hit_rate'] );
	}

	public function testSummaryWhenStoreIsNotFileCacheStore(): void {
		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->method( 'countByAction' )->willReturn( 0 );

		$store = $this->createMock( \AdvikLabs\Optimizer\Domain\Cache\Contract\CacheStoreInterface::class );

		$manager = $this->createMock( CacheManager::class );
		$manager->method( 'store' )->willReturn( $store );

		$service = new CacheStatsService( $manager, $logRepo );
		$summary = $service->summary();

		$this->assertSame( 0, $summary['size'] );
		$this->assertSame( 0, $summary['file_count'] );
	}
}
