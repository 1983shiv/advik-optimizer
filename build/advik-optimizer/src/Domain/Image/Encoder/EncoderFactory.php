<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Image\Encoder;

use AdvikLabs\Optimizer\Domain\Image\Contract\ImageEncoderInterface;
use RuntimeException;

class EncoderFactory {

	public function create(): ImageEncoderInterface {
		if ( function_exists( 'imagewebp' ) ) {
			return new GdEncoder();
		}

		throw new RuntimeException( 'No supported image encoder found on this server.' );
	}

	public function isWebpAvailable(): bool {
		if ( ! function_exists( 'imagewebp' ) ) {
			return false;
		}

		if ( ! function_exists( 'imagecreatefromstring' ) ) {
			return false;
		}

		ob_start();
		$testImage = imagecreatetruecolor( 10, 10 );
		if ( false === $testImage ) {
			ob_end_clean();
			return false;
		}

		$testPath = tempnam( sys_get_temp_dir(), 'webp_test_' );
		if ( false === $testPath ) {
			imagedestroy( $testImage );
			ob_end_clean();
			return false;
		}

		$result = imagewebp( $testImage, $testPath, 80 );
		imagedestroy( $testImage );

		if ( file_exists( $testPath ) ) {
			unlink( $testPath );
		}

		ob_end_clean();

		return $result;
	}
}
