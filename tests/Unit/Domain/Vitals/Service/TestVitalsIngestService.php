<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Vitals\Service;

use AdvikLabs\Optimizer\Domain\Vitals\Model\LabResult;
use AdvikLabs\Optimizer\Domain\Vitals\Repository\AuditRepository;
use AdvikLabs\Optimizer\Domain\Vitals\Repository\VitalsRepository;
use AdvikLabs\Optimizer\Domain\Vitals\Service\VitalsIngestService;
use PHPUnit\Framework\TestCase;

class TestVitalsIngestService extends TestCase {

	private function createService( $vitalsRepo ): VitalsIngestService {
		$auditRepo = $this->createMock( AuditRepository::class );
		return new VitalsIngestService( $vitalsRepo, $auditRepo );
	}

	public function testIngestLabDataStoresMetrics(): void {
		$repository = $this->createMock( VitalsRepository::class );
		$repository->expects( $this->once() )
			->method( 'storeBatch' )
			->with( $this->callback( fn( $metrics ) => 8 === count( $metrics ) ) );

		$labResult = new LabResult( 'https://example.com', 1.5, 0.05, 100, 300, 95, 90, 85, 80, 'mobile' );

		$service = $this->createService( $repository );
		$service->ingestLabData( $labResult );
	}

	public function testIngestFieldDataStoresMetrics(): void {
		$repository = $this->createMock( VitalsRepository::class );
		$repository->expects( $this->once() )
			->method( 'storeBatch' )
			->with( $this->callback( fn( $metrics ) => 4 === count( $metrics ) ) );

		$payload = [
			'url'   => 'https://example.com',
			'lcp'   => 2.0,
			'cls'   => 0.1,
			'inp'   => 150,
			'ttfb'  => 500,
			'device' => 'mobile',
		];

		$service = $this->createService( $repository );
		$service->ingestFieldData( $payload );
	}

	public function testIngestFieldDataWithPartialMetrics(): void {
		$repository = $this->createMock( VitalsRepository::class );
		$repository->expects( $this->once() )
			->method( 'storeBatch' )
			->with( $this->callback( fn( $metrics ) => 2 === count( $metrics ) ) );

		$payload = [
			'url' => 'https://example.com',
			'lcp' => 1.5,
			'cls' => 0.05,
		];

		$service = $this->createService( $repository );
		$service->ingestFieldData( $payload );
	}

	public function testIngestFieldDataWithEmptyUrlDoesNothing(): void {
		$repository = $this->createMock( VitalsRepository::class );
		$repository->expects( $this->never() )
			->method( 'storeBatch' );

		$payload = [
			'url' => '',
			'lcp' => 1.5,
		];

		$service = $this->createService( $repository );
		$service->ingestFieldData( $payload );
	}

	public function testIngestFieldDataWithAllNullMetrics(): void {
		$repository = $this->createMock( VitalsRepository::class );
		$repository->expects( $this->never() )
			->method( 'storeBatch' );

		$payload = [
			'url' => 'https://example.com',
		];

		$service = $this->createService( $repository );
		$service->ingestFieldData( $payload );
	}
}
