<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Admin\Controller;

use AdvikLabs\Optimizer\Domain\Image\Service\ImageQueueService;
use AdvikLabs\Optimizer\Domain\Image\Service\ImageRestoreService;
use AdvikLabs\Optimizer\Domain\Image\Repository\ImageOptimizationRepository;

class ImageController extends AbstractController {

	private ImageQueueService $queueService;
	private ImageRestoreService $restoreService;
	private ImageOptimizationRepository $repository;

	public function __construct(
		ImageQueueService $queueService,
		ImageRestoreService $restoreService,
		ImageOptimizationRepository $repository
	) {
		$this->queueService   = $queueService;
		$this->restoreService = $restoreService;
		$this->repository     = $repository;
	}

	public function bulkOptimize(): void {
		$this->verifyCapability();
		check_admin_referer( 'advik_optimizer_bulk_optimize' );

		$processed = 0;
		$pending   = $this->repository->getPending();

		if ( empty( $pending ) ) {
			$attachmentIds = $this->getUnprocessedAttachmentIds();
			$count         = count( $attachmentIds );

			if ( 0 === $count ) {
				wp_safe_redirect(
					add_query_arg(
						[
							'page'      => 'advik-optimizer-settings',
							'tab'       => 'images',
							'bulk_done' => 0,
						],
						admin_url( 'admin.php' )
					)
				);
				exit;
			}

			foreach ( $attachmentIds as $id ) {
				$file = get_attached_file( $id );
				if ( false === $file || ! file_exists( $file ) ) {
					continue;
				}

				$this->queueService->enqueue( $id );
				$processed++;

				if ( $processed >= 50 ) {
					break;
				}
			}
		} else {
			$results  = $this->queueService->processBatch( 50 );
			$processed = count( $results );
		}

		wp_safe_redirect(
			add_query_arg(
				[
					'page'      => 'advik-optimizer-settings',
					'tab'       => 'images',
					'bulk_done' => $processed,
				],
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	public function restore(): void {
		$this->verifyCapability();

		$attachmentId = isset( $_GET['attachment_id'] ) ? (int) $_GET['attachment_id'] : 0;

		if ( $attachmentId <= 0 ) {
			wp_die( esc_html__( 'Invalid attachment ID.', 'advik-optimizer' ) );
		}

		check_admin_referer( 'advik_optimizer_restore_image_' . $attachmentId );

		$this->restoreService->restore( $attachmentId );

		wp_safe_redirect(
			add_query_arg(
				[
					'page'    => 'advik-optimizer-settings',
					'tab'     => 'images',
					'restored' => '1',
				],
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	private function getUnprocessedAttachmentIds(): array {
		return $this->repository->getUnprocessedAttachmentIds( 50 );
	}
}
