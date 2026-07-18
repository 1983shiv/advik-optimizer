<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Minify\Service;

use AdvikLabs\Optimizer\Domain\Minify\Service\MinifySavingsService;
use PHPUnit\Framework\TestCase;

class TestMinifySavingsService extends TestCase {

	public function tearDown(): void {
		\MockWP::reset();
	}

	public function testSummaryReturnsExpectedKeysWhenModuleOff(): void {
		\MockWP::set( 'option_advik_optimizer_settings', [] );

		$service = new MinifySavingsService();
		$summary = $service->summary();

		$this->assertArrayHasKey( 'savings', $summary );
		$this->assertArrayHasKey( 'count', $summary );
		$this->assertArrayHasKey( 'css_count', $summary );
		$this->assertArrayHasKey( 'js_count', $summary );
		$this->assertEquals( 0, $summary['count'] );
	}

	public function testSummaryReturnsExpectedKeysWhenModuleOn(): void {
		\MockWP::set( 'option_advik_optimizer_settings', [ 'module_minify' => true ] );

		$service = new MinifySavingsService();
		$summary = $service->summary();

		$this->assertArrayHasKey( 'savings', $summary );
		$this->assertArrayHasKey( 'count', $summary );
	}

	public function testTotalSavingsBytesWhenModuleOff(): void {
		\MockWP::set( 'option_advik_optimizer_settings', [] );

		$service = new MinifySavingsService();

		$this->assertEquals( 0, $service->getTotalSavingsBytes() );
	}

	public function testProcessedCountWhenModuleOff(): void {
		\MockWP::set( 'option_advik_optimizer_settings', [] );

		$service = new MinifySavingsService();

		$this->assertEquals( 0, $service->getProcessedCount() );
	}
}
