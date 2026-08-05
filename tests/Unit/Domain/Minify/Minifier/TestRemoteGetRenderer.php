<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Minify\Minifier;

use AdvikLabs\Optimizer\Domain\Minify\Minifier\RemoteGetRenderer;
use PHPUnit\Framework\TestCase;

class TestRemoteGetRenderer extends TestCase {

	protected function tearDown(): void {
		\MockWP::reset();
		parent::tearDown();
	}

	public function testRenderReturnsBodyOnSuccess(): void {
		\MockWP::set( 'wp_remote_retrieve_response_code_return', 200 );
		\MockWP::set( 'wp_remote_retrieve_body_return', '<html><style>body{color:red}</style></html>' );

		$renderer = new RemoteGetRenderer();
		$body     = $renderer->render( 'https://example.com/' );

		$this->assertEquals( '<html><style>body{color:red}</style></html>', $body );
	}

	public function testRenderReturnsEmptyOnWpError(): void {
		\MockWP::set( 'is_wp_error_result', true );

		$renderer = new RemoteGetRenderer();
		$body     = $renderer->render( 'https://example.com/' );

		$this->assertEquals( '', $body );
	}

	public function testRenderReturnsEmptyOnNon200(): void {
		\MockWP::set( 'wp_remote_retrieve_response_code_return', 500 );
		\MockWP::set( 'wp_remote_retrieve_body_return', 'oops' );

		$renderer = new RemoteGetRenderer();
		$body     = $renderer->render( 'https://example.com/' );

		$this->assertEquals( '', $body );
	}

	public function testRenderReturnsEmptyBodyWhenBodyEmpty(): void {
		\MockWP::set( 'wp_remote_retrieve_response_code_return', 200 );
		\MockWP::set( 'wp_remote_retrieve_body_return', '' );

		$renderer = new RemoteGetRenderer();
		$body     = $renderer->render( 'https://example.com/' );

		$this->assertEquals( '', $body );
	}
}