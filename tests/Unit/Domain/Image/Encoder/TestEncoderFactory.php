<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Image\Encoder;

use AdvikLabs\Optimizer\Domain\Image\Encoder\EncoderFactory;
use PHPUnit\Framework\TestCase;

class TestEncoderFactory extends TestCase {

	public function testCreateReturnsEncoder(): void {
		$factory = new EncoderFactory();

		$encoder = $factory->create();

		$this->assertInstanceOf(
			\AdvikLabs\Optimizer\Domain\Image\Contract\ImageEncoderInterface::class,
			$encoder
		);
	}

	public function testEncoderSupportsWebp(): void {
		$factory = new EncoderFactory();
		$encoder = $factory->create();

		$supports = function_exists( 'imagewebp' )
			? $encoder->supportsFormat( 'webp' )
			: false;

		$this->assertIsBool( $supports );
	}
}
