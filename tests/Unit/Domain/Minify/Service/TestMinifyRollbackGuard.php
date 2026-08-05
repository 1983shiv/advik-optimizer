<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Minify\Service;

use AdvikLabs\Optimizer\Domain\Minify\Service\MinifyRollbackGuard;
use PHPUnit\Framework\TestCase;

class TestMinifyRollbackGuard extends TestCase {

	protected function tearDown(): void {
		unset( $_POST['url'] );
		\MockWP::reset();
		parent::tearDown();
	}

	public function testIsRolledBackFalseInitially(): void {
		$guard = new MinifyRollbackGuard();

		$this->assertFalse( $guard->isRolledBack() );
	}

	public function testGetRollbackUrlEmptyInitially(): void {
		$guard = new MinifyRollbackGuard();

		$this->assertEquals( '', $guard->getRollbackUrl() );
	}

	public function testReportErrorDoesNothingWhenNotAjax(): void {
		\MockWP::set( 'wp_doing_ajax', false );
		$guard = new MinifyRollbackGuard();

		$guard->reportError();

		$this->assertFalse( $guard->isRolledBack() );
	}

	public function testReportErrorDiesWhenUrlEmpty(): void {
		\MockWP::set( 'wp_doing_ajax', true );
		$_POST['url'] = '';
		$guard        = new MinifyRollbackGuard();

		$this->expectException( \RuntimeException::class );
		$guard->reportError();
	}

	public function testReportErrorDoesNotRollbackBelowThreshold(): void {
		\MockWP::set( 'wp_doing_ajax', true );
		$_POST['url'] = 'https://example.com/page';
		$guard        = new MinifyRollbackGuard();

		$this->expectException( \RuntimeException::class );
		$guard->reportError();
		$guard->reportError();
	}

	public function testReportErrorTracksCountPerUrl(): void {
		\MockWP::set( 'wp_doing_ajax', true );
		$_POST['url'] = 'https://example.com/page';
		$guard        = new MinifyRollbackGuard();

		try {
			$guard->reportError();
		} catch ( \RuntimeException $e ) {
			// wp_die expected; ignore.
		}

		$data = \MockWP::get( 'option_advik_optimizer_minify_errors' );
		$this->assertIsArray( $data );
		$this->assertEquals( 1, $data['https://example.com/page'] );
	}

	public function testReportErrorRollsBackAtThreshold(): void {
		\MockWP::set( 'wp_doing_ajax', true );
		$_POST['url'] = 'https://example.com/page';
		$guard        = new MinifyRollbackGuard();

		foreach ( [ 1, 2, 3 ] as $i ) {
			try {
				$guard->reportError();
			} catch ( \RuntimeException $e ) {
				// wp_die expected; ignore.
			}
		}

		$this->assertTrue( $guard->isRolledBack() );
		$this->assertEquals( 'https://example.com/page', $guard->getRollbackUrl() );
	}

	public function testRegisterHooksRegistersActions(): void {
		$guard = new MinifyRollbackGuard();

		$before = \MockWP::get( '_add_action_calls' ) ?? 0;
		$guard->registerHooks();
		$after = \MockWP::get( '_add_action_calls' ) ?? 0;

		$this->assertEquals( 4, $after - $before );
	}

	public function testReenableRedirectsAndClearsOptions(): void {
		\MockWP::set( 'option_advik_optimizer_minify_rollback', true );
		\MockWP::set( 'option_advik_optimizer_minify_rollback_url', 'https://example.com/page' );
		\MockWP::set( 'option_advik_optimizer_minify_errors', [ 'https://example.com/page' => 3 ] );
		$guard = new MinifyRollbackGuard();

		$this->expectException( \RuntimeException::class );
		$guard->reenable();
	}

	public function testShowAdminNoticeOutputsNoticeWhenRolledBack(): void {
		\MockWP::set( 'option_advik_optimizer_minify_rollback', true );
		$guard = new MinifyRollbackGuard();

		ob_start();
		$guard->showAdminNotice();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'notice-warning', $output );
		$this->assertStringContainsString( 'Re-enable Minification', $output );
	}

	public function testShowAdminNoticeEmptyWhenNotRolledBack(): void {
		$guard = new MinifyRollbackGuard();

		ob_start();
		$guard->showAdminNotice();
		$output = ob_get_clean();

		$this->assertEquals( '', $output );
	}
}