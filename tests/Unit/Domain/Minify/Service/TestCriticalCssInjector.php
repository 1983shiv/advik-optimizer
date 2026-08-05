<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Minify\Service;

use AdvikLabs\Optimizer\Domain\Minify\Minifier\CssMinifier;
use AdvikLabs\Optimizer\Domain\Minify\Model\CriticalCssRule;
use AdvikLabs\Optimizer\Domain\Minify\Repository\CriticalCssRepository;
use AdvikLabs\Optimizer\Domain\Minify\Service\CriticalCssInjector;
use AdvikLabs\Optimizer\Domain\Minify\Service\CriticalCssService;
use PHPUnit\Framework\TestCase;

class TestCriticalCssInjector extends TestCase {

	private \wpdb $wpdb;

	protected function setUp(): void {
		parent::setUp();
		$this->wpdb = new \wpdb();
	}

	protected function tearDown(): void {
		\MockWP::reset();
		parent::tearDown();
	}

	private function makeService( ?CriticalCssRule $rule ): CriticalCssService {
		return new class( $this->wpdb, $rule ) extends CriticalCssService {
			private ?CriticalCssRule $rule;
			public array $requestedTemplates = [];

			public function __construct( \wpdb $wpdb, ?CriticalCssRule $rule ) {
				parent::__construct( new CriticalCssRepository( $wpdb ) );
				$this->rule = $rule;
			}

			public function getRule( string $template ): ?CriticalCssRule {
				$this->requestedTemplates[] = $template;
				return $this->rule;
			}
		};
	}

	public function testInjectOutputsNothingWhenNoTemplateMatches(): void {
		$service = $this->makeService( null );
		$injector = new CriticalCssInjector( $service, new CssMinifier() );

		ob_start();
		$injector->inject();
		$output = ob_get_clean();

		$this->assertEquals( '', $output );
	}

	public function testInjectOutputsNothingWhenRuleMissing(): void {
		\MockWP::set( 'is_front_page', true );

		$service  = $this->makeService( null );
		$injector = new CriticalCssInjector( $service, new CssMinifier() );

		ob_start();
		$injector->inject();
		$output = ob_get_clean();

		$this->assertEquals( '', $output );
		$this->assertContains( 'front_page', $service->requestedTemplates );
	}

	public function testInjectOutputsStyleWhenRulePresent(): void {
		\MockWP::set( 'is_front_page', true );
		$rule = new CriticalCssRule( 1, 'front_page', 'body{color:red}', '2024-01-01 00:00:00' );

		$service  = $this->makeService( $rule );
		$injector = new CriticalCssInjector( $service, new CssMinifier() );

		ob_start();
		$injector->inject();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'advik-critical-css', $output );
		$this->assertStringContainsString( 'body{color:red}', $output );
	}

	public function testInjectMinifiesCss(): void {
		\MockWP::set( 'is_singular', true );
		\MockWP::set( 'get_post_type', 'post' );
		$rule = new CriticalCssRule( 1, 'singular_post', "body {\n color: red;\n}", '2024-01-01 00:00:00' );

		$service  = $this->makeService( $rule );
		$injector = new CriticalCssInjector( $service, new CssMinifier() );

		ob_start();
		$injector->inject();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'body{color:red}', $output );
		$this->assertContains( 'singular_post', $service->requestedTemplates );
	}

	public function testInjectOutputsNothingForEmptyCss(): void {
		\MockWP::set( 'is_singular', true );
		$rule = new CriticalCssRule( 1, 'singular_post', '   ', '2024-01-01 00:00:00' );

		$service  = $this->makeService( $rule );
		$injector = new CriticalCssInjector( $service, new CssMinifier() );

		ob_start();
		$injector->inject();
		$output = ob_get_clean();

		$this->assertEquals( '', $output );
	}

	public function testInjectResolvesSingularTemplateWithPostType(): void {
		\MockWP::set( 'is_singular', true );
		\MockWP::set( 'get_post_type', 'product' );
		$rule = new CriticalCssRule( 1, 'singular_product', 'a{color:red}', '2024-01-01 00:00:00' );

		$service  = $this->makeService( $rule );
		$injector = new CriticalCssInjector( $service, new CssMinifier() );

		ob_start();
		$injector->inject();
		ob_get_clean();

		$this->assertContains( 'singular_product', $service->requestedTemplates );
	}

	public function testInjectResolvesArchiveTemplate(): void {
		\MockWP::set( 'is_archive', true );
		$rule = new CriticalCssRule( 1, 'archive', 'a{color:red}', '2024-01-01 00:00:00' );

		$service  = $this->makeService( $rule );
		$injector = new CriticalCssInjector( $service, new CssMinifier() );

		ob_start();
		$injector->inject();
		ob_get_clean();

		$this->assertContains( 'archive', $service->requestedTemplates );
	}

	public function testInjectResolvesSearchTemplate(): void {
		\MockWP::set( 'is_search', true );
		$rule = new CriticalCssRule( 1, 'search', 'a{color:red}', '2024-01-01 00:00:00' );

		$service  = $this->makeService( $rule );
		$injector = new CriticalCssInjector( $service, new CssMinifier() );

		ob_start();
		$injector->inject();
		ob_get_clean();

		$this->assertContains( 'search', $service->requestedTemplates );
	}

	public function testInjectResolves404Template(): void {
		\MockWP::set( 'is_404', true );
		$rule = new CriticalCssRule( 1, '404', 'a{color:red}', '2024-01-01 00:00:00' );

		$service  = $this->makeService( $rule );
		$injector = new CriticalCssInjector( $service, new CssMinifier() );

		ob_start();
		$injector->inject();
		ob_get_clean();

		$this->assertContains( '404', $service->requestedTemplates );
	}
}