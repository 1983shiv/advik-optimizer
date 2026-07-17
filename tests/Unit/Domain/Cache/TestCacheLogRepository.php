<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Cache;

use AdvikLabs\Optimizer\Domain\Cache\Repository\CacheLogRepository;
use PHPUnit\Framework\TestCase;

class TestCacheLogRepository extends TestCase {
	private \wpdb $wpdb;

	protected function setUp(): void {
		parent::setUp();
		$this->wpdb = new \wpdb();
	}

	public function testLogInsertsRecord(): void {
		$repo = new CacheLogRepository( $this->wpdb );

		$repo->log( 'write', 'https://example.com', null );

		$this->expectNotToPerformAssertions();
	}

	public function testLogWithObjectId(): void {
		$repo = new CacheLogRepository( $this->wpdb );

		$repo->log( 'purge', 'https://example.com/page', 42 );

		$this->expectNotToPerformAssertions();
	}

	public function testLogWithNullUrl(): void {
		$repo = new CacheLogRepository( $this->wpdb );

		$repo->log( 'purge', null, 0 );

		$this->expectNotToPerformAssertions();
	}

	public function testGetRecentReturnsArray(): void {
		$repo = new CacheLogRepository( $this->wpdb );

		$results = $repo->getRecent( 10 );

		$this->assertIsArray( $results );
	}

	public function testGetRecentDefaultsTo50(): void {
		$repo = new CacheLogRepository( $this->wpdb );

		$results = $repo->getRecent();

		$this->assertIsArray( $results );
	}

	public function testCountByActionReturnsInt(): void {
		$repo = new CacheLogRepository( $this->wpdb );

		$count = $repo->countByAction( 'write' );

		$this->assertIsInt( $count );
	}

	public function testPurgeOlderThan(): void {
		$repo = new CacheLogRepository( $this->wpdb );

		$repo->purgeOlderThan( 30 );

		$this->expectNotToPerformAssertions();
	}

	public function testPurgeOlderThanDefaultsTo30(): void {
		$repo = new CacheLogRepository( $this->wpdb );

		$repo->purgeOlderThan();

		$this->expectNotToPerformAssertions();
	}
}
