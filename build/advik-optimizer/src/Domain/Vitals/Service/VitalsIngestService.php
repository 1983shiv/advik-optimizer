<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Vitals\Service;

use AdvikLabs\Optimizer\Domain\Vitals\Model\LabResult;
use AdvikLabs\Optimizer\Domain\Vitals\Model\VitalMetric;
use AdvikLabs\Optimizer\Domain\Vitals\Repository\VitalsRepository;

class VitalsIngestService {

	private VitalsRepository $repository;

	public function __construct( VitalsRepository $repository ) {
		$this->repository = $repository;
	}

	public function ingestLabData( LabResult $labResult ): void {
		if ( $labResult->isEmpty() ) {
			return;
		}
		$metrics = $labResult->toMetrics();
		$this->repository->storeBatch( $metrics );
	}

	public function ingestFieldData( array $payload ): void {
		$url      = $payload['url'] ?? '';
		$lcp      = isset( $payload['lcp'] ) ? (float) $payload['lcp'] : null;
		$cls      = isset( $payload['cls'] ) ? (float) $payload['cls'] : null;
		$inp      = isset( $payload['inp'] ) ? (float) $payload['inp'] : null;
		$ttfb     = isset( $payload['ttfb'] ) ? (float) $payload['ttfb'] : null;
		$device   = $payload['device'] ?? 'mobile';
		$recorded = $payload['recorded_at'] ?? current_time( 'mysql' );

		if ( empty( $url ) ) {
			return;
		}

		$hash = md5( $url );

		$metrics = [];
		if ( null !== $lcp ) {
			$metrics[] = new VitalMetric( null, $hash, $url, 'lcp', $lcp, $device, 'field', $recorded );
		}
		if ( null !== $cls ) {
			$metrics[] = new VitalMetric( null, $hash, $url, 'cls', $cls, $device, 'field', $recorded );
		}
		if ( null !== $inp ) {
			$metrics[] = new VitalMetric( null, $hash, $url, 'inp', $inp, $device, 'field', $recorded );
		}
		if ( null !== $ttfb ) {
			$metrics[] = new VitalMetric( null, $hash, $url, 'ttfb', $ttfb, $device, 'field', $recorded );
		}

		if ( ! empty( $metrics ) ) {
			$this->repository->storeBatch( $metrics );
		}
	}
}
