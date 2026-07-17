<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Vitals\Service;

use AdvikLabs\Optimizer\Domain\Vitals\Model\VitalMetric;
use AdvikLabs\Optimizer\Domain\Vitals\Repository\VitalsRepository;
use AdvikLabs\Optimizer\Domain\Vitals\Support\ScoreRubric;

class ScoreAggregatorService {

	private VitalsRepository $repository;
	private ScoreRubric $rubric;

	public function __construct( VitalsRepository $repository, ScoreRubric $rubric ) {
		$this->repository = $repository;
		$this->rubric     = $rubric;
	}

	public function currentScores( string $device = 'mobile' ): array {
		$labScores = $this->repository->getLatestScores( $device, 'lab' );

		if ( ! empty( $labScores ) ) {
			return array_merge(
				[
					'performance'     => 0,
					'seo'             => 0,
					'accessibility'   => 0,
					'best_practices'  => 0,
				],
				$labScores
			);
		}

		$fieldLcp  = $this->repository->getLatestMetricValue( 'lcp', $device, 'field' );
		$fieldCls  = $this->repository->getLatestMetricValue( 'cls', $device, 'field' );
		$fieldInp  = $this->repository->getLatestMetricValue( 'inp', $device, 'field' );
		$fieldTtfb = $this->repository->getLatestMetricValue( 'ttfb', $device, 'field' );

		$metrics = array_filter(
			[
				'lcp'  => $fieldLcp,
				'cls'  => $fieldCls,
				'inp'  => $fieldInp,
				'ttfb' => $fieldTtfb,
			],
			fn ( $v ) => null !== $v
		);

			if ( empty( $metrics ) ) {
				return [
					'performance'    => 0,
					'seo'            => 0,
					'accessibility'  => 0,
					'best_practices' => 0,
				];
			}

			$perf = $this->rubric->computePerformanceScore( $metrics );

			return [
				'performance'    => $perf,
				'seo'            => $perf,
				'accessibility'  => 100,
				'best_practices' => 100,
			];
	}

	public function latestMetrics( string $device = 'mobile' ): array {
		return [
			'lcp'  => $this->resolveLatestMetric( 'lcp', $device ),
			'cls'  => $this->resolveLatestMetric( 'cls', $device ),
			'inp'  => $this->resolveLatestMetric( 'inp', $device ),
			'ttfb' => $this->resolveLatestMetric( 'ttfb', $device ),
		];
	}

	public function trend( string $metricType, string $range = '7d', string $device = 'mobile' ): array {
		$labData = $this->repository->getTrend( $metricType, $range, $device, 'lab' );

		if ( ! empty( $labData ) ) {
			return $labData;
		}

		return $this->repository->getTrend( $metricType, $range, $device, 'field' );
	}

	private function resolveLatestMetric( string $metricType, string $device ): ?float {
		$value = $this->repository->getLatestMetricValue( $metricType, $device, 'lab' );

		if ( null !== $value ) {
			return $value;
		}

		return $this->repository->getLatestMetricValue( $metricType, $device, 'field' );
	}
}
