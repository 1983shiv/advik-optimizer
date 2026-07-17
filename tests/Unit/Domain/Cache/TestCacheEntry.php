<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Cache;

use AdvikLabs\Optimizer\Domain\Cache\Model\CacheEntry;
use PHPUnit\Framework\TestCase;

class TestCacheEntry extends TestCase {

	public function testConstructorSetsProperties(): void {
		$entry = new CacheEntry( 'test-key', '<html>', [ 'Content-Type: text/html' ], 3600 );

		$this->assertSame( 'test-key', $entry->getKey() );
		$this->assertSame( '<html>', $entry->getHtml() );
		$this->assertSame( [ 'Content-Type: text/html' ], $entry->getHeaders() );
	}

	public function testIsExpiredReturnsFalseForFutureExpiry(): void {
		$entry = new CacheEntry( 'k', 'h', [], 3600 );

		$this->assertFalse( $entry->isExpired() );
	}

	public function testGetExpiresAtReturnsTimestamp(): void {
		$entry = new CacheEntry( 'k', 'h', [], 3600 );
		$now   = time();

		$this->assertGreaterThan( $now, $entry->getExpiresAt() );
		$this->assertLessThanOrEqual( $now + 3600, $entry->getExpiresAt() );
	}

	public function testToArrayRoundTrip(): void {
		$entry = new CacheEntry( 'key', '<p>hello</p>', [ 'X-Custom: 1' ], 7200 );
		$data  = $entry->toArray();

		$this->assertSame( 'key', $data['key'] );
		$this->assertSame( '<p>hello</p>', $data['html'] );
		$this->assertSame( [ 'X-Custom: 1' ], $data['headers'] );
		$this->assertArrayHasKey( 'expires_at', $data );
	}

	public function testFromArrayRestoresEntry(): void {
		$original = new CacheEntry( 'restore-key', 'content', [ 'X-Test: 1' ], 1800 );
		$data     = $original->toArray();
		$restored = CacheEntry::fromArray( $data );

		$this->assertSame( $original->getKey(), $restored->getKey() );
		$this->assertSame( $original->getHtml(), $restored->getHtml() );
		$this->assertSame( $original->getHeaders(), $restored->getHeaders() );
	}

	public function testFromArrayWithMinimalData(): void {
		$futureExpiry = time() + 3600;

		$entry = CacheEntry::fromArray( [
			'key'        => 'minimal-key',
			'html'       => '<p>minimal</p>',
			'expires_at' => $futureExpiry,
		] );

		$this->assertSame( 'minimal-key', $entry->getKey() );
		$this->assertSame( [], $entry->getHeaders() );
		$this->assertFalse( $entry->isExpired() );
	}
}
