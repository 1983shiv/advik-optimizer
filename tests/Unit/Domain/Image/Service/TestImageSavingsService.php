<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Image\Service;

use AdvikLabs\Optimizer\Domain\Image\Repository\ImageOptimizationRepository;
use AdvikLabs\Optimizer\Domain\Image\Service\ImageSavingsService;
use PHPUnit\Framework\TestCase;

class TestImageSavingsService extends TestCase {

	public function testTotalSavingsBytesReturnsZeroWhenNoData(): void {
		$repo    = new ImageOptimizationRepository( new \wpdb() );
		$service = new ImageSavingsService( $repo );

		$bytes = $service->totalSavingsBytes();

		$this->assertEquals( 0, $bytes );
	}

	public function testTotalSavingsFormattedReturnsZeroB(): void {
		$repo    = new ImageOptimizationRepository( new \wpdb() );
		$service = new ImageSavingsService( $repo );

		$formatted = $service->totalSavingsFormatted();

		$this->assertStringContainsString( 'B', $formatted );
	}

	public function testOptimizedCountReturnsZeroWhenNoData(): void {
		$repo    = new ImageOptimizationRepository( new \wpdb() );
		$service = new ImageSavingsService( $repo );

		$count = $service->optimizedCount();

		$this->assertEquals( 0, $count );
	}

	public function testSummaryReturnsExpectedKeys(): void {
		$repo    = new ImageOptimizationRepository( new \wpdb() );
		$service = new ImageSavingsService( $repo );

		$summary = $service->summary();

		$this->assertArrayHasKey( 'savings_bytes', $summary );
		$this->assertArrayHasKey( 'savings', $summary );
		$this->assertArrayHasKey( 'count', $summary );
		$this->assertEquals( 0, $summary['savings_bytes'] );
		$this->assertEquals( 0, $summary['count'] );
	}
}
