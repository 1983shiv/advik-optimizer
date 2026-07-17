<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Cache;

use AdvikLabs\Optimizer\Domain\Cache\Service\CacheEligibility;
use MockWP;
use PHPUnit\Framework\TestCase;

class TestCacheEligibility extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		MockWP::reset();
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI']    = '/test-page';
	}

	protected function tearDown(): void {
		MockWP::reset();
		unset( $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'] );
		parent::tearDown();
	}

	public function testIsEligibleByDefault(): void {
		$eligibility = new CacheEligibility( [] );

		$this->assertTrue( $eligibility->isEligible() );
	}

	public function testNotEligibleWhenModuleDisabled(): void {
		$eligibility = new CacheEligibility( [ 'module_cache' => false ] );

		$this->assertFalse( $eligibility->isEligible() );
	}

	public function testNotEligibleForPostRequest(): void {
		$_SERVER['REQUEST_METHOD'] = 'POST';

		$eligibility = new CacheEligibility( [] );

		$this->assertFalse( $eligibility->isEligible() );
	}

	public function testNotEligibleWhenLoggedIn(): void {
		MockWP::set( 'is_user_logged_in', true );

		$eligibility = new CacheEligibility( [] );

		$this->assertFalse( $eligibility->isEligible() );
	}

	public function testNotEligibleWhenLoggedInWithExcludeSetting(): void {
		MockWP::set( 'is_user_logged_in', true );

		$eligibility = new CacheEligibility( [ 'exclude_logged_in' => true ] );

		$this->assertFalse( $eligibility->isEligible() );
	}

	public function testNotEligibleForExcludedUrl(): void {
		$_SERVER['REQUEST_URI'] = '/wp-admin/admin.php';

		$eligibility = new CacheEligibility( [
			'excluded_urls' => '/wp-admin',
		] );

		$this->assertFalse( $eligibility->isEligible() );
	}

	public function testNotEligibleForExcludedUrlWithWildcard(): void {
		$_SERVER['REQUEST_URI'] = '/wp-admin/some-page';

		$eligibility = new CacheEligibility( [
			'excluded_urls' => '/wp-admin/*',
		] );

		$this->assertFalse( $eligibility->isEligible() );
	}

	public function testEligibleWhenUrlNotExcluded(): void {
		$_SERVER['REQUEST_URI'] = '/blog/post-title';

		$eligibility = new CacheEligibility( [
			'excluded_urls' => '/wp-admin',
		] );

		$this->assertTrue( $eligibility->isEligible() );
	}

	public function testExcludedUrlWithMultiplePatterns(): void {
		$_SERVER['REQUEST_URI'] = '/cart';

		$eligibility = new CacheEligibility( [
			'excluded_urls' => "/wp-admin\n/cart\n/checkout",
		] );

		$this->assertFalse( $eligibility->isEligible() );
	}

	public function testExcludedUrlWithTrailingWhitespace(): void {
		$_SERVER['REQUEST_URI'] = '/checkout';

		$eligibility = new CacheEligibility( [
			'excluded_urls' => "  /wp-admin  \n  /checkout  ",
		] );

		$this->assertFalse( $eligibility->isEligible() );
	}
}
