<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Minify\Service;

use AdvikLabs\Optimizer\Domain\Minify\Minifier\CssMinifier;
use AdvikLabs\Optimizer\Domain\Minify\Minifier\HtmlMinifier;
use AdvikLabs\Optimizer\Domain\Minify\Minifier\JsMinifier;
use AdvikLabs\Optimizer\Domain\Minify\Service\MinifyService;
use PHPUnit\Framework\TestCase;

class TestMinifyService extends TestCase {

	private string $uploadDir;
	private string $cssFile;
	private string $jsFile;

	protected function setUp(): void {
		parent::setUp();
		$this->uploadDir = sys_get_temp_dir() . '/advik-test-uploads-' . uniqid( '', true );
		wp_mkdir_p( $this->uploadDir );

		$this->cssFile = ABSPATH . 'test-source-' . uniqid() . '.css';
		file_put_contents( $this->cssFile, '/* c */ body { color: red; }' );

		$this->jsFile = ABSPATH . 'test-source-' . uniqid() . '.js';
		file_put_contents( $this->jsFile, '/* c */ var x = 1;' );

		\MockWP::set( 'wp_upload_basedir', $this->uploadDir );
		\MockWP::set( 'wp_upload_baseurl', 'http://example.com' );
	}

	protected function tearDown(): void {
		unset( $GLOBALS['wp_styles'] );
		unset( $GLOBALS['wp_scripts'] );

		if ( file_exists( $this->cssFile ) ) {
			unlink( $this->cssFile );
		}
		if ( file_exists( $this->jsFile ) ) {
			unlink( $this->jsFile );
		}

		$this->removeDir( $this->uploadDir );

		\MockWP::reset();
		parent::tearDown();
	}

	private function removeDir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		foreach ( glob( $dir . '/*' ) ?: [] as $file ) {
			is_dir( $file ) ? $this->removeDir( $file ) : unlink( $file );
		}
		@rmdir( $dir );
	}

	private function makeStyles(): \WP_Styles {
		$styles              = new \WP_Styles();
		$styles->queue       = [ 'source' ];
		$styles->registered  = [
			'source' => (object) [ 'src' => '/' . basename( $this->cssFile ) ],
		];
		$GLOBALS['wp_styles'] = $styles;
		return $styles;
	}

	private function makeScripts(): \WP_Scripts {
		$scripts               = new \WP_Scripts();
		$scripts->queue        = [ 'source' ];
		$scripts->registered   = [
			'source' => (object) [ 'src' => '/' . basename( $this->jsFile ) ],
		];
		$GLOBALS['wp_scripts'] = $scripts;
		return $scripts;
	}

	private function enableMinify( bool $css = true, bool $js = true ): void {
		\MockWP::set(
			'option_advik_optimizer_settings',
			[
				'module_minify' => true,
				'minify_css'    => $css,
				'minify_js'     => $js,
			]
		);
	}

	public function testMinifyHtmlReturnsContentWhenModuleOff(): void {
		\MockWP::set( 'option_advik_optimizer_settings', [] );
		$service = new MinifyService( new CssMinifier(), new JsMinifier(), new HtmlMinifier() );

		$content = "<div>\n  <p>Hello</p>\n</div>";
		$this->assertEquals( $content, $service->minifyHtml( $content ) );
	}

	public function testMinifyHtmlReturnsMinifiedWhenEnabled(): void {
		\MockWP::set(
			'option_advik_optimizer_settings',
			[
				'module_minify' => true,
				'minify_html'   => true,
			]
		);
		$service = new MinifyService( new CssMinifier(), new JsMinifier(), new HtmlMinifier() );

		$input  = "<div>\n\t<p>Hello</p>\n</div>";
		$output = $service->minifyHtml( $input );

		$this->assertLessThan( strlen( $input ), strlen( $output ) );
		$this->assertStringNotContainsString( "\n", $output );
	}

	public function testProcessStylesDoesNothingWhenNoQueue(): void {
		$this->enableMinify();
		$service = new MinifyService( new CssMinifier(), new JsMinifier(), new HtmlMinifier() );
		$styles  = new \WP_Styles();
		$GLOBALS['wp_styles'] = $styles;

		$service->processStyles();

		$this->assertEquals( [], $styles->registered );
	}

	public function testProcessStylesDoesNothingWhenModuleOff(): void {
		\MockWP::set( 'option_advik_optimizer_settings', [] );
		$service = new MinifyService( new CssMinifier(), new JsMinifier(), new HtmlMinifier() );
		$styles  = $this->makeStyles();

		$service->processStyles();

		$this->assertSame( '/' . basename( $this->cssFile ), $styles->registered['source']->src );
	}

	public function testProcessStylesRewritesSourceToCache(): void {
		$this->enableMinify();
		$service = new MinifyService( new CssMinifier(), new JsMinifier(), new HtmlMinifier() );
		$styles  = $this->makeStyles();

		$service->processStyles();

		$this->assertStringContainsString( 'advik-optimizer/cache/assets', $styles->registered['source']->src );
	}

	public function testProcessStylesSkipsExcludedHandle(): void {
		$this->enableMinify();
		\MockWP::set(
			'option_advik_optimizer_settings',
			[
				'module_minify'        => true,
				'minify_css'           => true,
				'minify_exclude_css'   => 'source',
			]
		);
		$service = new MinifyService( new CssMinifier(), new JsMinifier(), new HtmlMinifier() );
		$styles  = $this->makeStyles();

		$service->processStyles();

		$this->assertSame( '/' . basename( $this->cssFile ), $styles->registered['source']->src );
	}

	public function testProcessStylesSkipsExternalSource(): void {
		$this->enableMinify();
		$service = new MinifyService( new CssMinifier(), new JsMinifier(), new HtmlMinifier() );
		$styles  = new \WP_Styles();
		$styles->queue      = [ 'cdn' ];
		$styles->registered = [ 'cdn' => (object) [ 'src' => 'https://cdn.example.com/theme.css' ] ];
		$GLOBALS['wp_styles'] = $styles;

		$service->processStyles();

		$this->assertSame( 'https://cdn.example.com/theme.css', $styles->registered['cdn']->src );
	}

	public function testProcessStylesSkipsAlreadyMinified(): void {
		$this->enableMinify();
		$service = new MinifyService( new CssMinifier(), new JsMinifier(), new HtmlMinifier() );
		$styles  = new \WP_Styles();
		$styles->queue      = [ 'core' ];
		$styles->registered = [ 'core' => (object) [ 'src' => '/wp-includes/css/dist/foo.min.css' ] ];
		$GLOBALS['wp_styles'] = $styles;

		$service->processStyles();

		$this->assertSame( '/wp-includes/css/dist/foo.min.css', $styles->registered['core']->src );
	}

	public function testProcessScriptsRewritesSourceToCache(): void {
		$this->enableMinify( false, true );
		$service = new MinifyService( new CssMinifier(), new JsMinifier(), new HtmlMinifier() );
		$scripts = $this->makeScripts();

		$service->processScripts();

		$this->assertStringContainsString( 'advik-optimizer/cache/assets', $scripts->registered['source']->src );
	}

	public function testProcessScriptsSkipsWhenJsDisabled(): void {
		$this->enableMinify( true, false );
		$service = new MinifyService( new CssMinifier(), new JsMinifier(), new HtmlMinifier() );
		$scripts = $this->makeScripts();

		$service->processScripts();

		$this->assertSame( '/' . basename( $this->jsFile ), $scripts->registered['source']->src );
	}

	public function testProcessScriptsSkipsExcludedHandle(): void {
		$this->enableMinify( false, true );
		\MockWP::set(
			'option_advik_optimizer_settings',
			[
				'module_minify'        => true,
				'minify_js'            => true,
				'minify_exclude_js'    => 'source',
			]
		);
		$service = new MinifyService( new CssMinifier(), new JsMinifier(), new HtmlMinifier() );
		$scripts = $this->makeScripts();

		$service->processScripts();

		$this->assertSame( '/' . basename( $this->jsFile ), $scripts->registered['source']->src );
	}

	public function testRegisterHooksRegistersActions(): void {
		$service = new MinifyService( new CssMinifier(), new JsMinifier(), new HtmlMinifier() );

		$before = \MockWP::get( '_add_action_calls' ) ?? 0;
		$service->registerHooks();
		$after = \MockWP::get( '_add_action_calls' ) ?? 0;

		$this->assertEquals( 2, $after - $before );
	}

	public function testSetExcludedStylesStoresHandles(): void {
		$service = new MinifyService( new CssMinifier(), new JsMinifier(), new HtmlMinifier() );

		$service->setExcludedStyles( [ 'a', 'b' ] );

		$reflection = new \ReflectionProperty( $service, 'excludedStyles' );
		$reflection->setAccessible( true );
		$this->assertEquals( [ 'a', 'b' ], $reflection->getValue( $service ) );
	}

	public function testSetExcludedScriptsStoresHandles(): void {
		$service = new MinifyService( new CssMinifier(), new JsMinifier(), new HtmlMinifier() );

		$service->setExcludedScripts( [ 'a', 'b' ] );

		$reflection = new \ReflectionProperty( $service, 'excludedScripts' );
		$reflection->setAccessible( true );
		$this->assertEquals( [ 'a', 'b' ], $reflection->getValue( $service ) );
	}
}