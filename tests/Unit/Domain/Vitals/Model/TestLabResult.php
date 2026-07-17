<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Vitals\Model;

use AdvikLabs\Optimizer\Domain\Vitals\Model\LabResult;
use PHPUnit\Framework\TestCase;

class TestLabResult extends TestCase {

	public function testConstructorSetsProperties(): void {
		$result = new LabResult( 'https://example.com', 1.5, 0.05, 100, 300, 95, 90, 85, 80, 'mobile', '2024-01-01 00:00:00' );

		$this->assertSame( 'https://example.com', $result->getUrl() );
		$this->assertSame( 1.5, $result->getLcp() );
		$this->assertSame( 0.05, $result->getCls() );
		$this->assertSame( 100.0, $result->getInp() );
		$this->assertSame( 300.0, $result->getTtfb() );
		$this->assertSame( 95, $result->getPerformanceScore() );
		$this->assertSame( 90, $result->getSeoScore() );
		$this->assertSame( 85, $result->getAccessibilityScore() );
		$this->assertSame( 80, $result->getBestPracticesScore() );
		$this->assertSame( 'mobile', $result->getDevice() );
	}

	public function testToMetricsReturnsEightMetrics(): void {
		$result  = new LabResult( 'https://example.com', 1.5, 0.05, 100, 300, 95, 90, 85, 80, 'mobile' );
		$metrics = $result->toMetrics();

		$this->assertCount( 8, $metrics );

		$types = array_map( fn( $m ) => $m->getMetricType(), $metrics );
		$this->assertContains( 'lcp', $types );
		$this->assertContains( 'cls', $types );
		$this->assertContains( 'inp', $types );
		$this->assertContains( 'ttfb', $types );
		$this->assertContains( 'performance', $types );
		$this->assertContains( 'seo', $types );
		$this->assertContains( 'accessibility', $types );
		$this->assertContains( 'best_practices', $types );
	}

	public function testToMetricsHasCorrectValues(): void {
		$result  = new LabResult( 'https://example.com', 2.5, 0.1, 200, 800, 85, 80, 90, 95, 'desktop' );
		$metrics = $result->toMetrics();

		foreach ( $metrics as $m ) {
			if ( 'lcp' === $m->getMetricType() ) {
				$this->assertSame( 2.5, $m->getValue() );
			}
			if ( 'performance' === $m->getMetricType() ) {
				$this->assertSame( 85.0, $m->getValue() );
			}
			$this->assertSame( 'desktop', $m->getDevice() );
			$this->assertSame( 'lab', $m->getSource() );
		}
	}

	public function testFromArray(): void {
		$result = LabResult::fromArray( [
			'url'                   => 'https://example.com',
			'lcp'                   => 1.2,
			'cls'                   => 0.03,
			'inp'                   => 80,
			'ttfb'                  => 200,
			'performance_score'     => 98,
			'seo_score'             => 92,
			'accessibility_score'   => 88,
			'best_practices_score'  => 85,
			'device'                => 'desktop',
		] );

		$this->assertSame( 'https://example.com', $result->getUrl() );
		$this->assertSame( 1.2, $result->getLcp() );
		$this->assertSame( 98, $result->getPerformanceScore() );
		$this->assertSame( 'desktop', $result->getDevice() );
	}

	public function testDeviceDefaultsToMobile(): void {
		$result = new LabResult( 'https://example.com', 0, 0, 0, 0, 0, 0, 0, 0 );

		$this->assertSame( 'mobile', $result->getDevice() );
	}

	public function testRecordedAtDefaultsToCurrentTime(): void {
		$result = new LabResult( 'https://example.com', 0, 0, 0, 0, 0, 0, 0, 0 );

		$this->assertNotEmpty( $result->getRecordedAt() );
	}
}
