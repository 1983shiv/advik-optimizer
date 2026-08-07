<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Minifier;

use AdvikLabs\Optimizer\Domain\Minify\Contract\MinifierInterface;

class HtmlMinifier implements MinifierInterface {

	public function minify( string $content ): string {
		$protected = [];

		$content = $this->protect( '#(<script\b[^>]*>)(.*?)(</script>)#is', $content, $protected );
		$content = $this->protect( '#(<style\b[^>]*>)(.*?)(</style>)#is', $content, $protected );
		$content = $this->protect( '#(<pre\b[^>]*>)(.*?)(</pre>)#is', $content, $protected );

		$content = $this->stripComments( $content );
		$content = $this->collapseWhitespace( $content );
		$content = preg_replace( '/\s+(ADVIK_HOLD_\d+)\s+/', '$1', $content ) ?? $content;

		foreach ( $protected as $key => $value ) {
			$content = str_replace( $key, $value, $content );
		}

		return trim( $content );
	}

	private function protect( string $pattern, string $content, array &$protected ): string {
		return preg_replace_callback(
			$pattern,
			function ( array $m ) use ( &$protected ): string {
				$key               = 'ADVIK_HOLD_' . count( $protected );
				$protected[ $key ] = $m[1] . $m[2] . $m[3];
				return $key;
			},
			$content
		) ?? $content;
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
