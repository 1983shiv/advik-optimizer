<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Vitals\Repository;

use AdvikLabs\Optimizer\Domain\Vitals\Model\VitalMetric;
use AdvikLabs\Optimizer\Domain\Vitals\Repository\VitalsRepository;
use PHPUnit\Framework\TestCase;

class TestVitalsRepository extends TestCase {
	private \wpdb $wpdb;

	protected function setUp(): void {
		parent::setUp();
		$this->wpdb = new \wpdb();
	}

	public function testStoreInsertsRecord(): void {
		$repo   = new VitalsRepository( $this->wpdb );
		$metric = new VitalMetric( null, 'hash', 'https://example.com', 'lcp', 1.5, 'mobile', 'lab', '2024-01-01 00:00:00' );

		$id = $repo->store( $metric );

		$this->assertIsInt( $id );
	}

	public function testStoreBatchInsertsMultiple(): void {
		$repo    = new VitalsRepository( $this->wpdb );
		$metrics = [
			new VitalMetric( null, 'h1', 'https://example.com', 'lcp', 1.5, 'mobile', 'lab', '2024-01-01 00:00:00' ),
			new VitalMetric( null, 'h1', 'https://example.com', 'cls', 0.05, 'mobile', 'lab', '2024-01-01 00:00:00' ),
		];

		$repo->storeBatch( $metrics );

		$this->expectNotToPerformAssertions();
	}

	public function testGetLatestScoreReturnsNullWhenNoData(): void {
		$repo = new VitalsRepository( $this->wpdb );

		$score = $repo->getLatestScore( 'lcp' );

		$this->assertNull( $score );
	}

	public function testGetLatestScoresReturnsArray(): void {
		$repo = new VitalsRepository( $this->wpdb );

		$scores = $repo->getLatestScores();

		$this->assertIsArray( $scores );
	}

	public function testGetLatestMetricValueReturnsNull(): void {
		$repo = new VitalsRepository( $this->wpdb );

		$value = $repo->getLatestMetricValue( 'lcp' );

		$this->assertNull( $value );
	}

	public function testGetTrendReturnsArray(): void {
		$repo = new VitalsRepository( $this->wpdb );

		$trend = $repo->getTrend( 'lcp', '7d' );

		$this->assertIsArray( $trend );
	}

	public function testGetTrendWith30dRange(): void {
		$repo = new VitalsRepository( $this->wpdb );

		$trend = $repo->getTrend( 'lcp', '30d' );

		$this->assertIsArray( $trend );
	}

	public function testGetTrendWith90dRange(): void {
		$repo = new VitalsRepository( $this->wpdb );

		$trend = $repo->getTrend( 'lcp', '90d' );

		$this->assertIsArray( $trend );
	}

	public function testGetRecentMetricsReturnsArray(): void {
		$repo = new VitalsRepository( $this->wpdb );

		$metrics = $repo->getRecentMetrics();

		$this->assertIsArray( $metrics );
	}

	public function testPurgeOlderThan(): void {
		$repo = new VitalsRepository( $this->wpdb );

		$repo->purgeOlderThan( 30 );

		$this->expectNotToPerformAssertions();
	}

	public function testPurgeOlderThanDefaultsTo90(): void {
		$repo = new VitalsRepository( $this->wpdb );

		$repo->purgeOlderThan();

		$this->expectNotToPerformAssertions();
	}
}
