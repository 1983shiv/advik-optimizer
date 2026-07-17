<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Image\Service;

use AdvikLabs\Optimizer\Domain\Image\Repository\ImageOptimizationRepository;
use AdvikLabs\Optimizer\Domain\Image\Service\ImageQueueService;
use AdvikLabs\Optimizer\Domain\Image\Service\ImageOptimizationService;
use PHPUnit\Framework\TestCase;

class TestImageQueueService extends TestCase {

	public function testProcessBatchReturnsArray(): void {
		$repo  = new ImageOptimizationRepository( new \wpdb() );
		$optim = $this->createMock( ImageOptimizationService::class );
		$queue = new ImageQueueService( $repo, $optim );

		$results = $queue->processBatch( 5 );

		$this->assertIsArray( $results );
	}

	public function testProcessBatchWithPendingRecords(): void {
		$repo = $this->createMock( ImageOptimizationRepository::class );
		$repo->method( 'getPending' )->willReturn( [] );

		$optim  = $this->createMock( ImageOptimizationService::class );
		$queue  = new ImageQueueService( $repo, $optim );

		$results = $queue->processBatch( 5 );

		$this->assertIsArray( $results );
		$this->assertCount( 0, $results );
	}
}
