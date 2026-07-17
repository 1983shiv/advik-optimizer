<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Vitals\Support;

use AdvikLabs\Optimizer\Domain\Vitals\Support\ScoreRubric;
use PHPUnit\Framework\TestCase;

class TestScoreRubric extends TestCase {

	private ScoreRubric $rubric;

	protected function setUp(): void {
		parent::setUp();
		$this->rubric = new ScoreRubric();
	}

	public function testScoreForMetricLcpGood(): void {
		$score = $this->rubric->scoreForMetric( 'lcp', 1500 );

		$this->assertGreaterThanOrEqual( 90, $score );
		$this->assertLessThanOrEqual( 100, $score );
	}

	public function testScoreForMetricLcpNeedsImprovement(): void {
		$score = $this->rubric->scoreForMetric( 'lcp', 3000 );

		$this->assertGreaterThanOrEqual( 50, $score );
		$this->assertLessThan( 90, $score );
	}

	public function testScoreForMetricLcpPoor(): void {
		$score = $this->rubric->scoreForMetric( 'lcp', 5000 );

		$this->assertLessThan( 50, $score );
	}

	public function testScoreForMetricClsGood(): void {
		$score = $this->rubric->scoreForMetric( 'cls', 0.05 );

		$this->assertGreaterThanOrEqual( 90, $score );
	}

	public function testScoreForMetricClsPoor(): void {
		$score = $this->rubric->scoreForMetric( 'cls', 0.3 );

		$this->assertLessThan( 50, $score );
	}

	public function testScoreForMetricInpGood(): void {
		$score = $this->rubric->scoreForMetric( 'inp', 100 );

		$this->assertGreaterThanOrEqual( 90, $score );
	}

	public function testScoreForMetricInpPoor(): void {
		$score = $this->rubric->scoreForMetric( 'inp', 600 );

		$this->assertLessThan( 50, $score );
	}

	public function testScoreForMetricTtfbGood(): void {
		$score = $this->rubric->scoreForMetric( 'ttfb', 400 );

		$this->assertGreaterThanOrEqual( 90, $score );
	}

	public function testScoreForMetricUnknownReturnsZero(): void {
		$score = $this->rubric->scoreForMetric( 'unknown', 100 );

		$this->assertSame( 0, $score );
	}

	public function testComputePerformanceScoreWithAllMetrics(): void {
		$score = $this->rubric->computePerformanceScore( [
			'lcp'  => 1500,
			'cls'  => 0.05,
			'inp'  => 100,
			'ttfb' => 400,
		] );

		$this->assertGreaterThanOrEqual( 90, $score );
	}

	public function testComputePerformanceScoreWithPartialMetrics(): void {
		$score = $this->rubric->computePerformanceScore( [
			'lcp' => 1500,
			'cls' => 0.05,
		] );

		$this->assertGreaterThan( 0, $score );
	}

	public function testComputePerformanceScoreWithNoMetrics(): void {
		$score = $this->rubric->computePerformanceScore( [] );

		$this->assertSame( 0, $score );
	}

	public function testScoreNeverExceeds100(): void {
		$score = $this->rubric->scoreForMetric( 'lcp', 0 );

		$this->assertLessThanOrEqual( 100, $score );
	}

	public function testScoreNeverBelow0(): void {
		$score = $this->rubric->scoreForMetric( 'lcp', 10000 );

		$this->assertGreaterThanOrEqual( 0, $score );
	}
}
