<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Minify\Service;

use AdvikLabs\Optimizer\Domain\Minify\Contract\RendererInterface;
use AdvikLabs\Optimizer\Domain\Minify\Repository\CriticalCssRepository;
use AdvikLabs\Optimizer\Domain\Minify\Service\CriticalCssService;
use PHPUnit\Framework\TestCase;

class TestCriticalCssService extends TestCase {

	private \wpdb $wpdb;
	private CriticalCssRepository $repository;

	protected function setUp(): void {
		parent::setUp();
		$this->wpdb       = new \wpdb();
		$this->repository = new CriticalCssRepository( $this->wpdb );
	}

	private function makeRenderer( string $html ): RendererInterface {
		return new class( $html ) implements RendererInterface {
			private string $html;

			public function __construct( string $html ) {
				$this->html = $html;
			}

			public function render( string $url ): string {
				return $this->html;
			}
		};
	}

	public function testScanReturnsZeroWhenNoRenderer(): void {
		$service = new CriticalCssService( $this->repository, null );

		$count = $service->scan( [ 'front_page' => 'https://example.com' ] );

		$this->assertEquals( 0, $count );
	}

	public function testScanReturnsZeroForEmptyUrlList(): void {
		$service = new CriticalCssService( $this->repository, $this->makeRenderer( '' ) );

		$this->assertEquals( 0, $service->scan( [] ) );
	}

	public function testScanCountsRuleWhenInlineStyleFound(): void {
		$renderer = $this->makeRenderer( '<html><head><style>body{color:red}</style></head></html>' );
		$service  = new CriticalCssService( $this->repository, $renderer );

		$count = $service->scan( [ 'front_page' => 'https://example.com' ] );

		$this->assertEquals( 1, $count );
	}

	public function testScanSkipsWhenHtmlEmpty(): void {
		$renderer = $this->makeRenderer( '' );
		$service  = new CriticalCssService( $this->repository, $renderer );

		$count = $service->scan( [ 'front_page' => 'https://example.com' ] );

		$this->assertEquals( 0, $count );
	}

	public function testScanSkipsWhenNoStyleTag(): void {
		$renderer = $this->makeRenderer( '<html><head></head><body>no css</body></html>' );
		$service  = new CriticalCssService( $this->repository, $renderer );

		$count = $service->scan( [ 'front_page' => 'https://example.com' ] );

		$this->assertEquals( 0, $count );
	}

	public function testScanCountsMultipleTemplates(): void {
		$renderer = $this->makeRenderer( '<style>a{color:blue}</style>' );
		$service  = new CriticalCssService( $this->repository, $renderer );

		$count = $service->scan(
			[
				'front_page' => 'https://example.com/',
				'archive'    => 'https://example.com/archives/',
			]
		);

		$this->assertEquals( 2, $count );
	}

	public function testGetRuleReturnsNullWhenNotPersisted(): void {
		$service = new CriticalCssService( $this->repository, $this->makeRenderer( '' ) );

		$this->assertNull( $service->getRule( 'front_page' ) );
	}

	public function testGetLastScanTimeReturnsNullWhenEmpty(): void {
		$service = new CriticalCssService( $this->repository, $this->makeRenderer( '' ) );

		$this->assertNull( $service->getLastScanTime() );
	}

	public function testGetRuleCountDelegatesToRepository(): void {
		$service = new CriticalCssService( $this->repository, $this->makeRenderer( '' ) );

		$this->assertEquals( 0, $service->getRuleCount() );
	}

	public function testGetTotalCssLengthDelegatesToRepository(): void {
		$service = new CriticalCssService( $this->repository, $this->makeRenderer( '' ) );

		$this->assertEquals( 0, $service->getTotalCssLength() );
	}
}