<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Service;

use AdvikLabs\Optimizer\Domain\Minify\Minifier\CssMinifier;
use AdvikLabs\Optimizer\Domain\Minify\Minifier\JsMinifier;
use AdvikLabs\Optimizer\Domain\Minify\Minifier\HtmlMinifier;

class MinifyService {

	private CssMinifier $cssMinifier;
	private JsMinifier $jsMinifier;
	private HtmlMinifier $htmlMinifier;

	private array $excludedStyles  = [];
	private array $excludedScripts = [];

	public function __construct(
		CssMinifier $cssMinifier,
		JsMinifier $jsMinifier,
		HtmlMinifier $htmlMinifier
	) {
		$this->cssMinifier  = $cssMinifier;
		$this->jsMinifier   = $jsMinifier;
		$this->htmlMinifier = $htmlMinifier;
	}

	public function setExcludedStyles( array $handles ): void {
		$this->excludedStyles = $handles;
	}

	public function setExcludedScripts( array $handles ): void {
		$this->excludedScripts = $handles;
	}

	public function registerHooks(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'processStyles' ], 999 );
		add_action( 'wp_enqueue_scripts', [ $this, 'processScripts' ], 999 );
	}

	public function processStyles(): void {
		global $wp_styles;

		if ( ! is_a( $wp_styles, 'WP_Styles' ) || empty( $wp_styles->queue ) ) {
			return;
		}

		$settings = get_option( 'advik_optimizer_settings', [] );
		if ( empty( $settings['module_minify'] ) || empty( $settings['minify_css'] ) ) {
			return;
		}

		$exclusions = $this->getExclusionList( 'minify_exclude_css' );

		foreach ( $wp_styles->queue as $handle ) {
			if ( in_array( $handle, $exclusions, true ) ) {
				continue;
			}

			$src = $wp_styles->registered[ $handle ]->src ?? '';
			if ( '' === $src ) {
				continue;
			}

			if ( $this->isExternal( $src ) ) {
				continue;
			}

			$filePath = $this->resolveFilePath( $src );
			if ( null === $filePath ) {
				continue;
			}

			$originalContent = file_get_contents( $filePath );
			if ( false === $originalContent ) {
				continue;
			}

			$cacheKey = $this->getCacheKey( $filePath );
			$cached   = $this->getCachedAsset( $cacheKey, 'css' );

			if ( null !== $cached ) {
				$wp_styles->registered[ $handle ]->src = $cached;
				continue;
			}

			$minified = $this->cssMinifier->minify( $originalContent );
			$url      = $this->writeCachedAsset( $cacheKey, $minified, 'css' );

			if ( null !== $url ) {
				$wp_styles->registered[ $handle ]->src = $url;
			}
		}
	}

	public function processScripts(): void {
		global $wp_scripts;

		if ( ! is_a( $wp_scripts, 'WP_Scripts' ) || empty( $wp_scripts->queue ) ) {
			return;
		}

		$settings = get_option( 'advik_optimizer_settings', [] );
		if ( empty( $settings['module_minify'] ) || empty( $settings['minify_js'] ) ) {
			return;
		}

		$exclusions = $this->getExclusionList( 'minify_exclude_js' );

		foreach ( $wp_scripts->queue as $handle ) {
			if ( in_array( $handle, $exclusions, true ) ) {
				continue;
			}

			$src = $wp_scripts->registered[ $handle ]->src ?? '';
			if ( '' === $src ) {
				continue;
			}

			if ( $this->isExternal( $src ) ) {
				continue;
			}

			$filePath = $this->resolveFilePath( $src );
			if ( null === $filePath ) {
				continue;
			}

			$originalContent = file_get_contents( $filePath );
			if ( false === $originalContent ) {
				continue;
			}

			$cacheKey = $this->getCacheKey( $filePath );
			$cached   = $this->getCachedAsset( $cacheKey, 'js' );

			if ( null !== $cached ) {
				$wp_scripts->registered[ $handle ]->src = $cached;
				continue;
			}

			$minified = $this->jsMinifier->minify( $originalContent );
			$url      = $this->writeCachedAsset( $cacheKey, $minified, 'js' );

			if ( null !== $url ) {
				$wp_scripts->registered[ $handle ]->src = $url;
			}
		}
	}

	public function minifyHtml( string $content ): string {
		$settings = get_option( 'advik_optimizer_settings', [] );
		if ( empty( $settings['module_minify'] ) || empty( $settings['minify_html'] ) ) {
			return $content;
		}

		return $this->htmlMinifier->minify( $content );
	}

	private function getExclusionList( string $optionKey ): array {
		$settings = get_option( 'advik_optimizer_settings', [] );
		$raw      = $settings[ $optionKey ] ?? '';
		if ( is_string( $raw ) && '' !== $raw ) {
			return array_map( 'trim', explode( ',', $raw ) );
		}
		if ( is_array( $raw ) ) {
			return $raw;
		}
		return [];
	}

	private function isExternal( string $url ): bool {
		$siteUrl = site_url();
		return 0 !== stripos( $url, $siteUrl ) && 0 !== strpos( $url, '/' ) && 0 !== strpos( $url, ABSPATH );
	}

	private function resolveFilePath( string $src ): ?string {
		if ( 0 === strpos( $src, '/' ) ) {
			$path = ABSPATH . ltrim( $src, '/' );
		} elseif ( 0 === strpos( $src, content_url() ) ) {
			$path = str_replace( content_url(), WP_CONTENT_DIR, $src );
		} elseif ( 0 === strpos( $src, site_url() ) ) {
			$path = str_replace( site_url(), ABSPATH, $src );
		} else {
			return null;
		}

		return file_exists( $path ) ? $path : null;
	}

	private function getCacheDir(): string {
		$uploadDir = wp_upload_dir();
		return $uploadDir['basedir'] . '/advik-optimizer/cache/assets';
	}

	private function getCacheUrl(): string {
		$uploadDir = wp_upload_dir();
		return $uploadDir['baseurl'] . '/advik-optimizer/cache/assets';
	}

	private function getCacheKey( string $filePath ): string {
		return md5( $filePath . filemtime( $filePath ) );
	}

	private function getCachedAsset( string $key, string $ext ): ?string {
		$dir  = $this->getCacheDir();
		$path = $dir . '/' . $key . '.' . $ext;

		if ( file_exists( $path ) ) {
			return $this->getCacheUrl() . '/' . $key . '.' . $ext;
		}

		return null;
	}

	private function writeCachedAsset( string $key, string $content, string $ext ): ?string {
		$dir = $this->getCacheDir();

		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		$path = $dir . '/' . $key . '.' . $ext;
		$written = file_put_contents( $path, $content );

		if ( false === $written ) {
			return null;
		}

		return $this->getCacheUrl() . '/' . $key . '.' . $ext;
	}
}
