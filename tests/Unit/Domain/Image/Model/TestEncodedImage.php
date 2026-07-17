<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Image\Model;

use AdvikLabs\Optimizer\Domain\Image\Model\EncodedImage;
use PHPUnit\Framework\TestCase;

class TestEncodedImage extends TestCase {

	public function testConstructorAndGetters(): void {
		$image = new EncodedImage( '/path/to/image.webp', 800, 600, 102400, 51200, 'webp' );

		$this->assertSame( '/path/to/image.webp', $image->getPath() );
		$this->assertSame( 800, $image->getWidth() );
		$this->assertSame( 600, $image->getHeight() );
		$this->assertSame( 102400, $image->getOriginalSize() );
		$this->assertSame( 51200, $image->getOptimizedSize() );
		$this->assertSame( 'webp', $image->getFormat() );
	}

	public function testGetSavingsReturnsDifference(): void {
		$image = new EncodedImage( '/path/to/image.webp', 800, 600, 102400, 51200, 'webp' );

		$this->assertSame( 51200, $image->getSavings() );
	}

	public function testGetSavingsWhenOptimizedIsLarger(): void {
		$image = new EncodedImage( '/path/to/image.webp', 800, 600, 51200, 102400, 'webp' );

		$this->assertSame( -51200, $image->getSavings() );
	}
}
