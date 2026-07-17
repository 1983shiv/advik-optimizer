<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Image\Service;

use AdvikLabs\Optimizer\Domain\Image\Repository\ImageOptimizationRepository;
use AdvikLabs\Optimizer\Domain\Image\Service\ImageRestoreService;
use PHPUnit\Framework\TestCase;

class TestImageRestoreService extends TestCase {

	public function testRestoreReturnsFalseWhenNoRecord(): void {
		$repo    = new ImageOptimizationRepository( new \wpdb() );
		$service = new ImageRestoreService( $repo );

		$result = $service->restore( 99999 );

		$this->assertFalse( $result );
	}

	public function testRestoreReturnsFalseWhenNotDone(): void {
		$repo = $this->createMock( ImageOptimizationRepository::class );
		$repo->method( 'findByAttachmentId' )
			->willReturn( $this->createMock( \AdvikLabs\Optimizer\Domain\Image\Model\OptimizationRecord::class ) );

		$record = new \AdvikLabs\Optimizer\Domain\Image\Model\OptimizationRecord(
			1, 1, 1024, null, null, 'pending', '2024-01-01 00:00:00'
		);
		$repo2 = $this->createMock( ImageOptimizationRepository::class );
		$repo2->method( 'findByAttachmentId' )->willReturn( $record );

		$service = new ImageRestoreService( $repo2 );

		$result = $service->restore( 1 );

		$this->assertFalse( $result );
	}
}
