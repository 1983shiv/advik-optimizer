<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Vitals\Client;

use AdvikLabs\Optimizer\Domain\Vitals\Client\PsiApiClient;
use MockWP;
use PHPUnit\Framework\TestCase;

class TestPsiApiClient extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		MockWP::reset();
	}

	protected function tearDown(): void {
		MockWP::reset();
		parent::tearDown();
	}

	public function testScanReturnsLabResultForValidResponse(): void {
		$mockResponse = [
			'lighthouseResult' => [
				'audits' => [
					'largest-contentful-paint' => [ 'numericValue' => 1500 ],
					'cumulative-layout-shift' => [ 'numericValue' => 0.05 ],
					'interaction-to-next-paint' => [ 'numericValue' => 100 ],
					'server-response-time' => [ 'numericValue' => 400 ],
				],
				'categories' => [
					'performance'     => [ 'score' => 0.95 ],
					'seo'             => [ 'score' => 0.90 ],
					'accessibility'   => [ 'score' => 0.85 ],
					'best-practices'  => [ 'score' => 0.80 ],
				],
			],
		];

		MockWP::set( 'wp_remote_get_response', [ 'response' => [ 'code' => 200 ] ] );
		MockWP::set( 'is_wp_error_result', false );
		MockWP::set( 'wp_remote_retrieve_body_return', json_encode( $mockResponse ) );

		$client = new PsiApiClient( 'test-api-key' );
		$result = $client->scan( 'https://example.com', 'mobile' );

		$this->assertSame( 'https://example.com', $result->getUrl() );
		$this->assertSame( 1.5, $result->getLcp() );
		$this->assertSame( 0.05, $result->getCls() );
		$this->assertSame( 100.0, $result->getInp() );
		$this->assertSame( 0.4, $result->getTtfb() );
		$this->assertSame( 95, $result->getPerformanceScore() );
		$this->assertSame( 90, $result->getSeoScore() );
		$this->assertSame( 85, $result->getAccessibilityScore() );
		$this->assertSame( 80, $result->getBestPracticesScore() );
		$this->assertSame( 'mobile', $result->getDevice() );
	}

	public function testScanReturnsEmptyResultOnEmptyApiKey(): void {
		$client = new PsiApiClient( '' );
		$result = $client->scan( 'https://example.com', 'mobile' );

		$this->assertSame( 0.0, $result->getLcp() );
		$this->assertSame( 0, $result->getPerformanceScore() );
	}

	public function testScanReturnsEmptyResultOnNon200Response(): void {
		MockWP::set( 'wp_remote_get_response', [ 'response' => [ 'code' => 403 ] ] );
		MockWP::set( 'is_wp_error_result', false );
		MockWP::set( 'wp_remote_retrieve_response_code_return', 403 );

		$client = new PsiApiClient( 'test-api-key' );
		$result = $client->scan( 'https://example.com', 'mobile' );

		$this->assertSame( 0.0, $result->getLcp() );
		$this->assertSame( 0, $result->getPerformanceScore() );
	}

	public function testScanReturnsEmptyResultOnWpError(): void {
		MockWP::set( 'wp_remote_get_response', new \WP_Error( 'http_error', 'HTTP error' ) );
		MockWP::set( 'is_wp_error_result', true );

		$client = new PsiApiClient( 'test-api-key' );
		$result = $client->scan( 'https://example.com', 'desktop' );

		$this->assertSame( 'https://example.com', $result->getUrl() );
		$this->assertSame( 0.0, $result->getLcp() );
		$this->assertSame( 0, $result->getPerformanceScore() );
		$this->assertSame( 'desktop', $result->getDevice() );
	}

	public function testScanReturnsEmptyResultOnInvalidJson(): void {
		MockWP::set( 'wp_remote_get_response', [ 'response' => [ 'code' => 200 ] ] );
		MockWP::set( 'is_wp_error_result', false );
		MockWP::set( 'wp_remote_retrieve_body_return', 'invalid json' );

		$client = new PsiApiClient( 'test-api-key' );
		$result = $client->scan( 'https://example.com' );

		$this->assertSame( 0.0, $result->getLcp() );
	}

	public function testScanReturnsEmptyResultOnMissingLighthouseResult(): void {
		MockWP::set( 'wp_remote_get_response', [ 'response' => [ 'code' => 200 ] ] );
		MockWP::set( 'is_wp_error_result', false );
		MockWP::set( 'wp_remote_retrieve_body_return', json_encode( [ 'error' => 'not found' ] ) );

		$client = new PsiApiClient( 'test-api-key' );
		$result = $client->scan( 'https://example.com' );

		$this->assertSame( 0.0, $result->getLcp() );
	}

	public function testScanUrlIncludesAllFourCategories(): void {
		MockWP::set( 'wp_remote_get_response', [ 'response' => [ 'code' => 200 ] ] );
		MockWP::set( 'is_wp_error_result', false );
		MockWP::set( 'wp_remote_retrieve_body_return', json_encode( [
			'lighthouseResult' => [
				'audits'     => [],
				'categories' => [],
			],
		] ) );

		$client = new PsiApiClient( 'test-api-key' );
		$client->scan( 'https://example.com' );

		$calledUrl = MockWP::get( '_last_remote_get_url' );
		$this->assertNotFalse( strpos( $calledUrl, 'category=performance' ), 'Missing performance category' );
		$this->assertNotFalse( strpos( $calledUrl, 'category=seo' ), 'Missing seo category' );
		$this->assertNotFalse( strpos( $calledUrl, 'category=accessibility' ), 'Missing accessibility category' );
		$this->assertNotFalse( strpos( $calledUrl, 'category=best-practices' ), 'Missing best-practices category' );
	}

	public function testScanHandlesMissingAuditValues(): void {
		$mockResponse = [
			'lighthouseResult' => [
				'audits'     => [],
				'categories' => [],
			],
		];

		MockWP::set( 'wp_remote_get_response', [ 'response' => [ 'code' => 200 ] ] );
		MockWP::set( 'is_wp_error_result', false );
		MockWP::set( 'wp_remote_retrieve_body_return', json_encode( $mockResponse ) );

		$client = new PsiApiClient( 'test-api-key' );
		$result = $client->scan( 'https://example.com' );

		$this->assertSame( 0.0, $result->getLcp() );
		$this->assertSame( 0, $result->getPerformanceScore() );
	}
}
