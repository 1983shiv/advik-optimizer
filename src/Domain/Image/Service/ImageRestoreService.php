<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Image\Service;

use AdvikLabs\Optimizer\Domain\Image\Repository\ImageOptimizationRepository;

class ImageRestoreService {

	private ImageOptimizationRepository $repository;

	public function __construct( ImageOptimizationRepository $repository ) {
		$this->repository = $repository;
	}

	public function restore( int $attachmentId ): bool {
		$record = $this->repository->findByAttachmentId( $attachmentId );

		if ( null === $record ) {
			return false;
		}

		if ( 'done' !== $record->getStatus() ) {
			return false;
		}

		$deleted = $this->deleteWebpVariants( $attachmentId );

		$this->repository->update( $record->getId(), [ 'status' => 'restored' ] );

		return $deleted;
	}

	private function deleteWebpVariants( int $attachmentId ): bool {
		$file = get_attached_file( $attachmentId );

		if ( false === $file ) {
			return false;
		}

		$info   = pathinfo( $file );
		$webp   = $info['dirname'] . '/' . $info['filename'] . '.webp';
		$deleted = false;

		if ( file_exists( $webp ) ) {
			$deleted = wp_delete_file( $webp );
		}

		$metadata = wp_get_attachment_metadata( $attachmentId );

		if ( is_array( $metadata ) && ! empty( $metadata['sizes'] ) ) {
			$uploadDir = wp_upload_dir();
			$baseDir   = $uploadDir['basedir'] . '/' . dirname( $metadata['file'] ?? '' );

			foreach ( $metadata['sizes'] as $size ) {
				$subsizePath = $baseDir . '/' . ( $size['file'] ?? '' );
				$info2       = pathinfo( $subsizePath );
				$webpSubsize = $info2['dirname'] . '/' . $info2['filename'] . '.webp';

				if ( file_exists( $webpSubsize ) ) {
					wp_delete_file( $webpSubsize );
					$deleted = true;
				}
			}
		}

		return $deleted;
	}
}
