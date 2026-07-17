<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Vitals\Service;

use AdvikLabs\Optimizer\Domain\Vitals\Contract\LighthouseClientInterface;
use AdvikLabs\Optimizer\Domain\Vitals\Model\LabResult;
use AdvikLabs\Optimizer\Domain\Vitals\Service\VitalsIngestService;
use AdvikLabs\Optimizer\Domain\Vitals\Service\VitalsScanService;
use MockWP;
use PHPUnit\Framework\TestCase;

class TestVitalsScanService extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		MockWP::reset();
	}

	protected function tearDown(): void {
		MockWP::reset();
		parent::tearDown();
	}

	public function testScanUrlDelegatesToClientAndIngests(): void {
		$expected = new LabResult( 'https://example.com', 1.5, 0.05, 100, 300, 95, 90, 85, 80, 'mobile' );

		$client = $this->createMock( LighthouseClientInterface::class );
		$client->expects( $this->once() )
			->method( 'scan' )
			->with( 'https://example.com', 'mobile' )
			->willReturn( $expected );

		$ingest = $this->createMock( VitalsIngestService::class );
		$ingest->expects( $this->once() )
			->method( 'ingestLabData' )
			->with( $expected );

		MockWP::set( 'home_url', 'https://example.com' );

		$service = new VitalsScanService( $client, $ingest );
		$result  = $service->scanUrl( 'https://example.com', 'mobile' );

		$this->assertSame( $expected, $result );
	}

	public function testScanHomepageUsesHomeUrl(): void {
		$expected = new LabResult( 'https://testsite.com', 2.0, 0.1, 200, 600, 80, 85, 90, 85, 'desktop' );

		$client = $this->createMock( LighthouseClientInterface::class );
		$client->expects( $this->once() )
			->method( 'scan' )
			->with( 'https://testsite.com', 'desktop' )
			->willReturn( $expected );

		$ingest = $this->createMock( VitalsIngestService::class );
		$ingest->expects( $this->once() )
			->method( 'ingestLabData' );

		MockWP::set( 'home_url', 'https://testsite.com' );

		$service = new VitalsScanService( $client, $ingest );
		$result  = $service->scanHomepage( 'desktop' );

		$this->assertSame( $expected, $result );
	}

	public function testScanBothReturnsMobileAndDesktopResults(): void {
		$mobile  = new LabResult( 'https://example.com', 1.5, 0.05, 100, 300, 95, 90, 85, 80, 'mobile' );
		$desktop = new LabResult( 'https://example.com', 2.0, 0.1, 200, 600, 80, 85, 90, 85, 'desktop' );

		$client = $this->createMock( LighthouseClientInterface::class );
		$client->expects( $this->exactly( 2 ) )
			->method( 'scan' )
			->willReturnMap( [
				[ 'https://example.com', 'mobile', $mobile ],
				[ 'https://example.com', 'desktop', $desktop ],
			] );

		$ingest = $this->createMock( VitalsIngestService::class );

		MockWP::set( 'home_url', 'https://example.com' );

		$service = new VitalsScanService( $client, $ingest );
		$results = $service->scanBoth( 'https://example.com' );

		$this->assertSame( $mobile, $results['mobile'] );
		$this->assertSame( $desktop, $results['desktop'] );
	}
}
