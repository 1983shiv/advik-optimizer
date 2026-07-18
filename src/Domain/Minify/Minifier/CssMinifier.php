<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Minifier;

use AdvikLabs\Optimizer\Domain\Minify\Contract\MinifierInterface;

class CssMinifier implements MinifierInterface {

	public function minify( string $content ): string {
		$content = $this->stripComments( $content );
		$content = $this->stripWhitespace( $content );

		return trim( $content );
	}

	private function stripComments( string $content ): string {
		return preg_replace( '#/\*.*?\*/#s', '', $content ) ?? $content;
	}

	private function stripWhitespace( string $content ): string {
		$content = preg_replace( '/[\r\n\t]+/', ' ', $content ) ?? $content;
		$content = preg_replace( '/\s*([{}:;,])\s*/', '$1', $content ) ?? $content;
		$content = preg_replace( '/\s+/', ' ', $content ) ?? $content;
		$content = preg_replace( '/;}/', '}', $content ) ?? $content;

		return trim( $content );
	}
}
