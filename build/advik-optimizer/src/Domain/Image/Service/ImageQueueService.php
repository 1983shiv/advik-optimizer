<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Image\Service;

use AdvikLabs\Optimizer\Domain\Image\Repository\ImageOptimizationRepository;

class ImageQueueService {

	private ImageOptimizationRepository $repository;
	private ImageOptimizationService $optimizationService;

	public function __construct(
		ImageOptimizationRepository $repository,
		ImageOptimizationService $optimizationService
	) {
		$this->repository         = $repository;
		$this->optimizationService = $optimizationService;
	}

	public function enqueue( int $attachmentId ): int {
		$existing = $this->repository->findByAttachmentId( $attachmentId );

		if ( null !== $existing && 'done' === $existing->getStatus() ) {
			return $existing->getId();
		}

		$file = get_attached_file( $attachmentId );

		if ( false === $file || ! file_exists( $file ) ) {
			return 0;
		}

		$originalSize = filesize( $file );
		$id           = $this->repository->insert( $attachmentId, $originalSize );

		$this->processNow( $id, $attachmentId, $file, $originalSize );

		return $id;
	}

	public function processBatch( int $limit = 5 ): array {
		$pending = $this->repository->getPending();
		$results = [];

		foreach ( array_slice( $pending, 0, $limit ) as $record ) {
			$file = get_attached_file( $record->getAttachmentId() );

			if ( false === $file || ! file_exists( $file ) ) {
				$this->repository->update( $record->getId(), [ 'status' => 'failed' ] );
				$results[] = [
					'id' => $record->getId(),
					'status' => 'failed',
				];
				continue;
			}

			$result = $this->processNow(
				$record->getId(),
				$record->getAttachmentId(),
				$file,
				$record->getOriginalSize()
			);

			$results[] = $result;
		}

		return $results;
	}

	private function processNow( int $recordId, int $attachmentId, string $file, int $originalSize ): array {
		$this->repository->update( $recordId, [ 'status' => 'processing' ] );

		try {
			$mimeType = get_post_mime_type( $attachmentId );

			if ( false === $mimeType || ! in_array( $mimeType, [ 'image/jpeg', 'image/png', 'image/gif' ], true ) ) {
				$this->repository->update(
					$recordId,
					[
						'status' => 'done',
						'format' => 'original',
					]
				);
				return [
					'id' => $recordId,
					'status' => 'skipped',
					'reason' => 'unsupported mime',
				];
			}

			$encoded    = $this->optimizationService->process( $attachmentId, $file );
			$savings    = $encoded->getSavings();
			$finalSize  = $encoded->getOptimizedSize();

			$this->repository->update(
				$recordId,
				[
					'status'         => 'done',
					'optimized_size' => $finalSize,
					'format'         => $encoded->getFormat(),
				]
			);

			return [
				'id'          => $recordId,
				'status'      => 'done',
				'original'    => $originalSize,
				'optimized'   => $finalSize,
				'savings'     => $savings,
			];
		} catch ( \Throwable $e ) {
			$this->repository->update( $recordId, [ 'status' => 'failed' ] );

			return [
				'id' => $recordId,
				'status' => 'failed',
				'error' => $e->getMessage(),
			];
		}
	}
}
