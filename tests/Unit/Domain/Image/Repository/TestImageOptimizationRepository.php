<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Image\Repository;

use AdvikLabs\Optimizer\Domain\Image\Repository\ImageOptimizationRepository;
use PHPUnit\Framework\TestCase;

class TestImageOptimizationRepository extends TestCase {

	private \wpdb $wpdb;

	protected function setUp(): void {
		parent::setUp();
		$this->wpdb = new \wpdb();
	}

	public function testInsertCreatesRecord(): void {
		$repo = new ImageOptimizationRepository( $this->wpdb );

		$id = $repo->insert( 1, 102400 );

		$this->assertGreaterThan( 0, $id );
	}

	public function testInsertWithCustomStatus(): void {
		$repo = new ImageOptimizationRepository( $this->wpdb );

		$id = $repo->insert( 2, 204800, 'processing' );

		$this->assertGreaterThan( 0, $id );
	}

	public function testFindByAttachmentIdReturnsNullWhenNotFound(): void {
		$repo = new ImageOptimizationRepository( $this->wpdb );

		$record = $repo->findByAttachmentId( 99999 );

		$this->assertNull( $record );
	}

	public function testFindByIdReturnsNullWhenNotFound(): void {
		$repo = new ImageOptimizationRepository( $this->wpdb );

		$record = $repo->findById( 99999 );

		$this->assertNull( $record );
	}

	public function testGetPendingReturnsArray(): void {
		$repo = new ImageOptimizationRepository( $this->wpdb );

		$pending = $repo->getPending();

		$this->assertIsArray( $pending );
	}

	public function testGetAllReturnsArray(): void {
		$repo = new ImageOptimizationRepository( $this->wpdb );

		$all = $repo->getAll();

		$this->assertIsArray( $all );
	}

	public function testGetByStatusReturnsArray(): void {
		$repo = new ImageOptimizationRepository( $this->wpdb );

		$done = $repo->getByStatus( 'done' );

		$this->assertIsArray( $done );
	}

	public function testGetTotalSavingsReturnsZeroWhenNoData(): void {
		$repo = new ImageOptimizationRepository( $this->wpdb );

		$savings = $repo->getTotalSavings();

		$this->assertEquals( 0, $savings );
	}

	public function testGetDoneCountReturnsZeroWhenNoData(): void {
		$repo = new ImageOptimizationRepository( $this->wpdb );

		$count = $repo->getDoneCount();

		$this->assertEquals( 0, $count );
	}

	public function testGetTotalOriginalSizeReturnsZeroWhenNoData(): void {
		$repo = new ImageOptimizationRepository( $this->wpdb );

		$total = $repo->getTotalOriginalSize();

		$this->assertEquals( 0, $total );
	}

	public function testDeleteByAttachmentId(): void {
		$repo = new ImageOptimizationRepository( $this->wpdb );

		$repo->deleteByAttachmentId( 1 );

		$this->expectNotToPerformAssertions();
	}

	public function testUpdate(): void {
		$repo = new ImageOptimizationRepository( $this->wpdb );

		$repo->update( 1, [ 'status' => 'done' ] );

		$this->expectNotToPerformAssertions();
	}
}
