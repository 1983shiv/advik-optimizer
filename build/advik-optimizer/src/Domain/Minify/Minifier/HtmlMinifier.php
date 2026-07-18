<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Minifier;

use AdvikLabs\Optimizer\Domain\Minify\Contract\MinifierInterface;

class HtmlMinifier implements MinifierInterface {

	public function minify( string $content ): string {
		$content = $this->stripComments( $content );
		$content = $this->collapseWhitespace( $content );

		return trim( $content );
	}

	private function stripComments( string $content ): string {
		return preg_replace( '/<!--[^>]*-->/', '', $content ) ?? $content;
	}

	private function collapseWhitespace( string $content ): string {
		$content = preg_replace( '/[\r\n\t]+/', ' ', $content ) ?? $content;
		$content = preg_replace( '/\s+/', ' ', $content ) ?? $content;
		$content = preg_replace( '/> </', '><', $content ) ?? $content;

		return trim( $content );
	}
}
