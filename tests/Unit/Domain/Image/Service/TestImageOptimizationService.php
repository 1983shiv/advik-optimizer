<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Image\Service;

use AdvikLabs\Optimizer\Domain\Image\Encoder\EncoderFactory;
use AdvikLabs\Optimizer\Domain\Image\Service\ImageOptimizationService;
use AdvikLabs\Optimizer\Support\SettingsRegistry;
use PHPUnit\Framework\TestCase;

class TestImageOptimizationService extends TestCase {

	public function testConstructor(): void {
		$registry = new SettingsRegistry();
		$factory  = new EncoderFactory();
		$service  = new ImageOptimizationService( $factory, $registry );

		$this->assertInstanceOf( ImageOptimizationService::class, $service );
	}

	public function testGetEncoderReturnsEncoder(): void {
		$registry = new SettingsRegistry();
		$factory  = new EncoderFactory();
		$service  = new ImageOptimizationService( $factory, $registry );

		$encoder = $service->getEncoder();

		$this->assertInstanceOf(
			\AdvikLabs\Optimizer\Domain\Image\Contract\ImageEncoderInterface::class,
			$encoder
		);
	}
}
