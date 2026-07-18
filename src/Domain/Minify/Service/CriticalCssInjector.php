<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Service;

use AdvikLabs\Optimizer\Domain\Minify\Minifier\CssMinifier;

class CriticalCssInjector {

	private CriticalCssService $criticalCssService;
	private CssMinifier $cssMinifier;

	public function __construct( CriticalCssService $criticalCssService, CssMinifier $cssMinifier ) {
		$this->criticalCssService = $criticalCssService;
		$this->cssMinifier        = $cssMinifier;
	}

	public function inject(): void {
		$template = $this->resolveTemplate();

		if ( null === $template ) {
			return;
		}

		$rule = $this->criticalCssService->getRule( $template );

		if ( null === $rule ) {
			return;
		}

		$css = $rule->getCss();

		if ( '' === trim( $css ) ) {
			return;
		}

		$minified = $this->cssMinifier->minify( $css );

		echo "\n<!-- Advik Optimizer Critical CSS -->\n";
		echo '<style id="advik-critical-css">' . $minified . "</style>\n";
	}

	private function resolveTemplate(): ?string {
		if ( is_front_page() || is_home() ) {
			return 'front_page';
		}

		if ( is_singular() ) {
			$postType = get_post_type();
			return 'singular_' . ( $postType ? $postType : 'post' );
		}

		if ( is_archive() ) {
			return 'archive';
		}

		if ( is_search() ) {
			return 'search';
		}

		if ( is_404() ) {
			return '404';
		}

		return null;
	}
}
