<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Image\Service;

use AdvikLabs\Optimizer\Domain\Image\Contract\ImageEncoderInterface;
use AdvikLabs\Optimizer\Domain\Image\Encoder\EncoderFactory;
use AdvikLabs\Optimizer\Domain\Image\Model\EncodedImage;
use AdvikLabs\Optimizer\Support\SettingsRegistry;

class ImageOptimizationService {

	private EncoderFactory $encoderFactory;
	private SettingsRegistry $settingsRegistry;
	private ?ImageEncoderInterface $encoder = null;

	public function __construct(
		EncoderFactory $encoderFactory,
		SettingsRegistry $settingsRegistry
	) {
		$this->encoderFactory   = $encoderFactory;
		$this->settingsRegistry = $settingsRegistry;
	}

	public function process( int $attachmentId, string $file ): EncodedImage {
		$settings = get_option( 'advik_optimizer_settings', [] );
		$quality  = $settings['image_quality'] ?? 82;

		$encoder = $this->getEncoder();

		if ( ! $encoder->supportsFormat( 'webp' ) ) {
			throw new \RuntimeException( 'WebP encoding is not supported by the active encoder.' );
		}

		$encoded = $encoder->encode( $file, 'webp', $quality );

		$this->generateSubsizeWebps( $attachmentId, $quality, $encoder );

		return $encoded;
	}

	public function getEncoder(): ImageEncoderInterface {
		if ( null === $this->encoder ) {
			$this->encoder = $this->encoderFactory->create();
		}

		return $this->encoder;
	}

	private function generateSubsizeWebps( int $attachmentId, int $quality, ImageEncoderInterface $encoder ): void {
		$metadata = wp_get_attachment_metadata( $attachmentId );

		if ( ! is_array( $metadata ) || empty( $metadata['sizes'] ) ) {
			return;
		}

		$uploadDir = wp_upload_dir();
		$baseDir   = $uploadDir['basedir'] . '/' . dirname( $metadata['file'] ?? '' );

		foreach ( $metadata['sizes'] as $size ) {
			$subsizePath = $baseDir . '/' . ( $size['file'] ?? '' );

			if ( file_exists( $subsizePath ) && is_file( $subsizePath ) ) {
				try {
					$encoder->encode( $subsizePath, 'webp', $quality );
				} catch ( \Throwable $e ) {
					continue;
				}
			}
		}
	}
}
