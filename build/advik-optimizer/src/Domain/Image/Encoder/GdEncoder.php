<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Image\Encoder;

use AdvikLabs\Optimizer\Domain\Image\Contract\ImageEncoderInterface;
use AdvikLabs\Optimizer\Domain\Image\Model\EncodedImage;
use InvalidArgumentException;
use RuntimeException;

class GdEncoder implements ImageEncoderInterface {

	public function encode( string $path, string $format, int $quality ): EncodedImage {
		if ( ! $this->supportsFormat( $format ) ) {
			throw new InvalidArgumentException( "Format {$format} is not supported by GD encoder." );
		}

		if ( ! file_exists( $path ) ) {
			throw new RuntimeException( "Source file not found: {$path}" );
		}

		$originalSize = filesize( $path );
		$image        = $this->loadImage( $path );

		if ( false === $image ) {
			throw new RuntimeException( "Failed to load image: {$path}" );
		}

		$width  = imagesx( $image );
		$height = imagesy( $image );

		$info     = pathinfo( $path );
		$ext      = 'webp' === $format ? 'webp' : $format;
		$outPath  = $info['dirname'] . '/' . $info['filename'] . '.' . $ext;

		if ( file_exists( $outPath ) ) {
			imagedestroy( $image );
			$optimizedSize = filesize( $outPath );
			return new EncodedImage( $outPath, $width, $height, $originalSize, $optimizedSize, $format );
		}

		$success = imagewebp( $image, $outPath, $quality );
		imagedestroy( $image );

		if ( false === $success ) {
			throw new RuntimeException( "Failed to encode WebP: {$path}" );
		}

		$optimizedSize = filesize( $outPath );

		return new EncodedImage( $outPath, $width, $height, $originalSize, $optimizedSize, $format );
	}

	public function supportsFormat( string $format ): bool {
		if ( 'webp' !== $format ) {
			return false;
		}

		return function_exists( 'imagewebp' );
	}

	private function loadImage( string $path ) {
		$info = getimagesize( $path );

		if ( false === $info ) {
			throw new RuntimeException( "Cannot read image info: {$path}" );
		}

		return match ( $info[2] ) {
			IMAGETYPE_JPEG => imagecreatefromjpeg( $path ),
			IMAGETYPE_PNG  => imagecreatefrompng( $path ),
			IMAGETYPE_GIF  => imagecreatefromgif( $path ),
			default        => throw new RuntimeException( 'Unsupported source format: ' . $info[2] ),
		};
	}
}
