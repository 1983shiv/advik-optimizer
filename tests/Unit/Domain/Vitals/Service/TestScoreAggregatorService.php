<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Vitals\Service;

use AdvikLabs\Optimizer\Domain\Vitals\Repository\VitalsRepository;
use AdvikLabs\Optimizer\Domain\Vitals\Support\ScoreRubric;
use AdvikLabs\Optimizer\Domain\Vitals\Service\ScoreAggregatorService;
use PHPUnit\Framework\TestCase;

class TestScoreAggregatorService extends TestCase {

	public function testCurrentScoresReturnsLabScoresWhenAvailable(): void {
		$repository = $this->createMock( VitalsRepository::class );
		$repository->method( 'getLatestScores' )
			->with( 'mobile', 'lab' )
			->willReturn( [
				'performance'    => 95,
				'seo'            => 90,
				'accessibility'  => 85,
				'best_practices' => 80,
			] );

		$rubric = new ScoreRubric();
		$service = new ScoreAggregatorService( $repository, $rubric );
		$scores  = $service->currentScores();

		$this->assertSame( 95, $scores['performance'] );
		$this->assertSame( 90, $scores['seo'] );
		$this->assertSame( 85, $scores['accessibility'] );
		$this->assertSame( 80, $scores['best_practices'] );
	}

	public function testCurrentScoresReturnsDefaultsWhenNoData(): void {
		$repository = $this->createMock( VitalsRepository::class );
		$repository->method( 'getLatestScores' )->willReturn( [] );
		$repository->method( 'getLatestMetricValue' )->willReturn( null );

		$rubric  = new ScoreRubric();
		$service = new ScoreAggregatorService( $repository, $rubric );
		$scores  = $service->currentScores();

		$this->assertSame( 0, $scores['performance'] );
		$this->assertSame( 0, $scores['seo'] );
		$this->assertSame( 0, $scores['accessibility'] );
		$this->assertSame( 0, $scores['best_practices'] );
	}

	public function testCurrentScoresComputesFromFieldDataWhenNoLabScores(): void {
		$repository = $this->createMock( VitalsRepository::class );
		$repository->method( 'getLatestScores' )->willReturn( [] );
		$repository->method( 'getLatestMetricValue' )
			->willReturnMap( [
				[ 'lcp', 'mobile', 'field', 1500.0 ],
				[ 'cls', 'mobile', 'field', 0.05 ],
				[ 'inp', 'mobile', 'field', 100.0 ],
				[ 'ttfb', 'mobile', 'field', 400.0 ],
			] );

		$rubric  = new ScoreRubric();
		$service = new ScoreAggregatorService( $repository, $rubric );
		$scores  = $service->currentScores();

		$this->assertGreaterThan( 0, $scores['performance'] );
		$this->assertGreaterThanOrEqual( 0, $scores['seo'] );
		$this->assertSame( 100, $scores['accessibility'] );
		$this->assertSame( 100, $scores['best_practices'] );
	}

	public function testLatestMetricsReturnsValues(): void {
		$repository = $this->createMock( VitalsRepository::class );
		$repository->method( 'getLatestMetricValue' )
			->willReturnMap( [
				[ 'lcp', 'mobile', 'lab', 1.5 ],
				[ 'cls', 'mobile', 'lab', 0.05 ],
				[ 'inp', 'mobile', 'lab', 100.0 ],
				[ 'ttfb', 'mobile', 'lab', 400.0 ],
			] );

		$rubric  = new ScoreRubric();
		$service = new ScoreAggregatorService( $repository, $rubric );
		$metrics = $service->latestMetrics();

		$this->assertSame( 1.5, $metrics['lcp'] );
		$this->assertSame( 0.05, $metrics['cls'] );
		$this->assertSame( 100.0, $metrics['inp'] );
		$this->assertSame( 400.0, $metrics['ttfb'] );
	}

	public function testLatestMetricsReturnsFieldDataWhenNoLab(): void {
		$repository = $this->createMock( VitalsRepository::class );
		$repository->method( 'getLatestMetricValue' )
			->willReturnMap( [
				[ 'lcp', 'mobile', 'lab', null ],
				[ 'lcp', 'mobile', 'field', 2.0 ],
				[ 'cls', 'mobile', 'lab', null ],
				[ 'cls', 'mobile', 'field', 0.1 ],
				[ 'inp', 'mobile', 'lab', null ],
				[ 'inp', 'mobile', 'field', 200.0 ],
				[ 'ttfb', 'mobile', 'lab', null ],
				[ 'ttfb', 'mobile', 'field', 600.0 ],
			] );

		$rubric  = new ScoreRubric();
		$service = new ScoreAggregatorService( $repository, $rubric );
		$metrics = $service->latestMetrics();

		$this->assertSame( 2.0, $metrics['lcp'] );
		$this->assertSame( 0.1, $metrics['cls'] );
	}

	public function testTrendReturnsLabDataWhenAvailable(): void {
		$labData = [
			[ 'value' => 2.0, 'recorded_at' => '2024-01-01 00:00:00' ],
			[ 'value' => 1.8, 'recorded_at' => '2024-01-02 00:00:00' ],
		];

		$repository = $this->createMock( VitalsRepository::class );
		$repository->method( 'getTrend' )
			->with( 'lcp', '7d', 'mobile', 'lab' )
			->willReturn( $labData );

		$rubric  = new ScoreRubric();
		$service = new ScoreAggregatorService( $repository, $rubric );
		$trend   = $service->trend( 'lcp', '7d' );

		$this->assertCount( 2, $trend );
		$this->assertSame( 2.0, $trend[0]['value'] );
	}

	public function testTrendReturnsFieldDataWhenNoLab(): void {
		$fieldData = [
			[ 'value' => 2.5, 'recorded_at' => '2024-01-01 00:00:00' ],
		];

		$repository = $this->createMock( VitalsRepository::class );
		$repository->method( 'getTrend' )
			->willReturnMap( [
				[ 'lcp', '7d', 'mobile', 'lab', [] ],
				[ 'lcp', '7d', 'mobile', 'field', $fieldData ],
			] );

		$rubric  = new ScoreRubric();
		$service = new ScoreAggregatorService( $repository, $rubric );
		$trend   = $service->trend( 'lcp', '7d' );

		$this->assertCount( 1, $trend );
		$this->assertSame( 2.5, $trend[0]['value'] );
	}
}
