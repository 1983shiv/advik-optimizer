<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Vitals\Support;

class ScoreRubric {

	public function scoreForMetric( string $metricType, float $value ): int {
		return match ( $metricType ) {
			'lcp'   => $this->scoreLcp( $value ),
			'cls'   => $this->scoreCls( $value ),
			'inp'   => $this->scoreInp( $value ),
			'ttfb'  => $this->scoreTtfb( $value ),
			default => 0,
		};
	}

	public function computePerformanceScore( array $metrics ): int {
		$weights = [
			'lcp'  => 0.25,
			'cls'  => 0.15,
			'inp'  => 0.30,
			'ttfb' => 0.10,
		];

		$totalWeight = 0;
		$weightedSum = 0;

		foreach ( $weights as $metric => $weight ) {
			if ( isset( $metrics[ $metric ] ) ) {
				$weightedSum += $this->scoreForMetric( $metric, $metrics[ $metric ] ) * $weight;
				$totalWeight += $weight;
			}
		}

		if ( 0 === $totalWeight ) {
			return 0;
		}

		return (int) round( $weightedSum / $totalWeight );
	}

	private function scoreLcp( float $lcp ): int {
		if ( $lcp <= 2500 ) {
			return 90 + (int) round( ( 2500 - $lcp ) / 2500 * 10 );
		}
		if ( $lcp <= 4000 ) {
			return 50 + (int) round( ( 4000 - $lcp ) / 1500 * 39 );
		}
		return max( 0, 50 - (int) round( ( $lcp - 4000 ) / 1000 * 10 ) );
	}

	private function scoreCls( float $cls ): int {
		if ( $cls <= 0.1 ) {
			return 90 + (int) round( ( 0.1 - $cls ) / 0.1 * 10 );
		}
		if ( $cls <= 0.25 ) {
			return 50 + (int) round( ( 0.25 - $cls ) / 0.15 * 39 );
		}
		return max( 0, 50 - (int) round( ( $cls - 0.25 ) / 0.05 * 10 ) );
	}

	private function scoreInp( float $inp ): int {
		if ( $inp <= 200 ) {
			return 90 + (int) round( ( 200 - $inp ) / 200 * 10 );
		}
		if ( $inp <= 500 ) {
			return 50 + (int) round( ( 500 - $inp ) / 300 * 39 );
		}
		return max( 0, 50 - (int) round( ( $inp - 500 ) / 100 * 10 ) );
	}

	private function scoreTtfb( float $ttfb ): int {
		if ( $ttfb <= 800 ) {
			return 90 + (int) round( ( 800 - $ttfb ) / 800 * 10 );
		}
		if ( $ttfb <= 1800 ) {
			return 50 + (int) round( ( 1800 - $ttfb ) / 1000 * 39 );
		}
		return max( 0, 50 - (int) round( ( $ttfb - 1800 ) / 200 * 10 ) );
	}
}
