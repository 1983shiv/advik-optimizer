<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Vitals\Model;

use AdvikLabs\Optimizer\Domain\Vitals\Model\VitalMetric;
use PHPUnit\Framework\TestCase;

class TestVitalMetric extends TestCase {

	public function testConstructorSetsProperties(): void {
		$metric = new VitalMetric( 1, 'abc123', 'https://example.com', 'lcp', 1.5, 'mobile', 'lab', '2024-01-01 00:00:00' );

		$this->assertSame( 1, $metric->getId() );
		$this->assertSame( 'abc123', $metric->getUrlHash() );
		$this->assertSame( 'https://example.com', $metric->getUrl() );
		$this->assertSame( 'lcp', $metric->getMetricType() );
		$this->assertSame( 1.5, $metric->getValue() );
		$this->assertSame( 'mobile', $metric->getDevice() );
		$this->assertSame( 'lab', $metric->getSource() );
		$this->assertSame( '2024-01-01 00:00:00', $metric->getRecordedAt() );
	}

	public function testToArray(): void {
		$metric = new VitalMetric( 1, 'hash', 'https://example.com', 'cls', 0.05, 'desktop', 'field', '2024-01-01 00:00:00' );
		$data   = $metric->toArray();

		$this->assertSame( 1, $data['id'] );
		$this->assertSame( 'hash', $data['url_hash'] );
		$this->assertSame( 'cls', $data['metric_type'] );
		$this->assertSame( 0.05, $data['value'] );
	}

	public function testFromArray(): void {
		$metric = VitalMetric::fromArray( [
			'id'          => 2,
			'url_hash'    => 'def456',
			'url'         => 'https://example.com/page',
			'metric_type' => 'inp',
			'value'       => 150.5,
			'device'      => 'mobile',
			'source'      => 'lab',
			'recorded_at' => '2024-06-15 12:00:00',
		] );

		$this->assertSame( 2, $metric->getId() );
		$this->assertSame( 'inp', $metric->getMetricType() );
		$this->assertSame( 150.5, $metric->getValue() );
	}

	public function testFromArrayWithDefaults(): void {
		$metric = VitalMetric::fromArray( [
			'url'         => 'https://example.com',
			'metric_type' => 'lcp',
			'value'       => 2.0,
		] );

		$this->assertNull( $metric->getId() );
		$this->assertSame( 'desktop', $metric->getDevice() );
		$this->assertSame( 'lab', $metric->getSource() );
	}

	public function testNullId(): void {
		$metric = new VitalMetric( null, 'hash', 'url', 'ttfb', 500, 'mobile', 'lab', '2024-01-01 00:00:00' );

		$this->assertNull( $metric->getId() );
	}

	public function testFloatValuePrecision(): void {
		$metric = new VitalMetric( null, 'hash', 'url', 'cls', 0.001, 'mobile', 'lab', '2024-01-01 00:00:00' );

		$this->assertSame( 0.001, $metric->getValue() );
	}
}
