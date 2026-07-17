<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Cache;

use AdvikLabs\Optimizer\Domain\Cache\Repository\CacheLogRepository;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheManager;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheWarmService;
use MockWP;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../mock/class-wp-error.php';

class TestCacheWarmService extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		MockWP::reset();
	}

	protected function tearDown(): void {
		MockWP::reset();
		parent::tearDown();
	}

	public function testWarmFetchesUrlsAndLogs(): void {
		MockWP::set( 'wp_remote_get_response', [ 'response' => [ 'code' => 200 ] ] );
		MockWP::set( 'is_wp_error_result', false );

		$manager = $this->createMock( CacheManager::class );

		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->expects( $this->exactly( 2 ) )
			->method( 'log' )
			->with( 'warm' );

		$service = new CacheWarmService( $manager, $logRepo );
		$service->warm( [ 'https://example.com/page1', 'https://example.com/page2' ] );
	}

	public function testWarmLogsEvenOnWpError(): void {
		MockWP::set( 'wp_remote_get_response', new \WP_Error( 'http_error', 'Error' ) );

		$manager = $this->createMock( CacheManager::class );

		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->expects( $this->once() )
			->method( 'log' )
			->with( 'warm', 'https://example.com/error' );

		$service = new CacheWarmService( $manager, $logRepo );
		$service->warm( [ 'https://example.com/error' ] );
	}

	public function testWarmFromSitemapParsesXmlAndWarms(): void {
		$sitemapXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc>https://example.com/page1</loc></url>
  <url><loc>https://example.com/page2</loc></url>
</urlset>
XML;

		MockWP::set( 'wp_remote_get_response', [ 'response' => [ 'code' => 200 ] ] );
		MockWP::set( 'is_wp_error_result', false );
		MockWP::set( 'wp_remote_retrieve_body_return', $sitemapXml );

		$manager = $this->createMock( CacheManager::class );

		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->expects( $this->exactly( 2 ) )
			->method( 'log' )
			->with( 'warm' );

		$service = new CacheWarmService( $manager, $logRepo );
		$service->warmFromSitemap( 'https://example.com/sitemap.xml' );
	}

	public function testWarmFromSitemapHandlesWpError(): void {
		MockWP::set( 'wp_remote_get_response', new \WP_Error( 'http_error', 'Error' ) );

		$manager = $this->createMock( CacheManager::class );
		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->expects( $this->never() )->method( 'log' );

		$service = new CacheWarmService( $manager, $logRepo );
		$service->warmFromSitemap( 'https://example.com/bad-sitemap.xml' );
	}

	public function testWarmFromSitemapHandlesEmptyBody(): void {
		MockWP::set( 'wp_remote_get_response', [ 'response' => [ 'code' => 200 ] ] );
		MockWP::set( 'is_wp_error_result', false );
		MockWP::set( 'wp_remote_retrieve_body_return', '' );

		$manager = $this->createMock( CacheManager::class );
		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->expects( $this->never() )->method( 'log' );

		$service = new CacheWarmService( $manager, $logRepo );
		$service->warmFromSitemap( 'https://example.com/empty-sitemap.xml' );
	}

	public function testWarmFromSitemapHandlesInvalidXml(): void {
		MockWP::set( 'wp_remote_get_response', [ 'response' => [ 'code' => 200 ] ] );
		MockWP::set( 'is_wp_error_result', false );
		MockWP::set( 'wp_remote_retrieve_body_return', 'not xml' );

		$manager = $this->createMock( CacheManager::class );
		$logRepo = $this->createMock( CacheLogRepository::class );
		$logRepo->expects( $this->never() )->method( 'log' );

		libxml_use_internal_errors( true );
		$service = new CacheWarmService( $manager, $logRepo );
		$service->warmFromSitemap( 'https://example.com/invalid-sitemap.xml' );
		libxml_use_internal_errors( false );
	}
}
