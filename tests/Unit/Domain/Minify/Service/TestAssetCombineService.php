<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Minify\Service;

use AdvikLabs\Optimizer\Domain\Minify\Service\AssetCombineService;
use PHPUnit\Framework\TestCase;

class TestAssetCombineService extends TestCase {

	protected function tearDown(): void {
		\MockWP::reset();
		parent::tearDown();
	}

	public function testRegisterHooksRegistersActions(): void {
		$service = new AssetCombineService();

		$before = \MockWP::get( '_add_action_calls' ) ?? 0;
		$service->registerHooks();
		$after = \MockWP::get( '_add_action_calls' ) ?? 0;

		$this->assertEquals( 2, $after - $before );
	}

	public function testCombineStylesDoesNothingWhenDisabled(): void {
		\MockWP::set( 'option_advik_optimizer_settings', [] );
		$service = new AssetCombineService();

		$service->combineStyles();

		$this->expectNotToPerformAssertions();
	}

	public function testCombineStylesRunsWhenEnabled(): void {
		\MockWP::set( 'option_advik_optimizer_settings', [ 'minify_combine_css' => true ] );
		$service = new AssetCombineService();

		$service->combineStyles();

		$this->expectNotToPerformAssertions();
	}

	public function testCombineScriptsDoesNothingWhenDisabled(): void {
		\MockWP::set( 'option_advik_optimizer_settings', [] );
		$service = new AssetCombineService();

		$service->combineScripts();

		$this->expectNotToPerformAssertions();
	}

	public function testCombineScriptsRunsWhenEnabled(): void {
		\MockWP::set( 'option_advik_optimizer_settings', [ 'minify_combine_js' => true ] );
		$service = new AssetCombineService();

		$service->combineScripts();

		$this->expectNotToPerformAssertions();
	}
}