<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Minify\Repository;

use AdvikLabs\Optimizer\Domain\Minify\Model\CriticalCssRule;
use AdvikLabs\Optimizer\Domain\Minify\Repository\CriticalCssRepository;
use PHPUnit\Framework\TestCase;

class TestCriticalCssRepository extends TestCase {

	private \wpdb $wpdb;

	protected function setUp(): void {
		parent::setUp();
		$this->wpdb = new \wpdb();
	}

	public function testSaveReturnsInsertId(): void {
		$repo = new CriticalCssRepository( $this->wpdb );
		$rule = new CriticalCssRule( null, 'front_page', 'body{color:red}', '2024-01-01 00:00:00' );

		$id = $repo->save( $rule );

		$this->assertGreaterThan( 0, $id );
	}

	public function testFindByTemplateReturnsNullWhenEmpty(): void {
		$repo = new CriticalCssRepository( $this->wpdb );

		$result = $repo->findByTemplate( 'front_page' );

		$this->assertNull( $result );
	}

	public function testGetAllReturnsArray(): void {
		$repo = new CriticalCssRepository( $this->wpdb );

		$all = $repo->getAll();

		$this->assertIsArray( $all );
	}

	public function testDeleteByTemplateDoesNotThrow(): void {
		$repo = new CriticalCssRepository( $this->wpdb );

		$repo->deleteByTemplate( 'front_page' );

		$this->expectNotToPerformAssertions();
	}

	public function testGetLastScanTimeReturnsNullWhenEmpty(): void {
		$repo = new CriticalCssRepository( $this->wpdb );

		$this->assertNull( $repo->getLastScanTime() );
	}

	public function testGetRuleCountReturnsZeroWhenEmpty(): void {
		$repo = new CriticalCssRepository( $this->wpdb );

		$this->assertEquals( 0, $repo->getRuleCount() );
	}

	public function testGetTotalCssLengthReturnsZeroWhenEmpty(): void {
		$repo = new CriticalCssRepository( $this->wpdb );

		$this->assertEquals( 0, $repo->getTotalCssLength() );
	}
}