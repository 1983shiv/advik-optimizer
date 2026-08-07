<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Minifier;

use AdvikLabs\Optimizer\Domain\Minify\Contract\MinifierInterface;

class JsMinifier implements MinifierInterface {

	public function minify( string $content ): string {
		return $this->render( $content );
	}

	private function render( string $content ): string {
		$out    = '';
		$len    = strlen( $content );
		$i      = 0;
		$prev   = '';
		$quote  = null;
		$regex  = false;
		$escape = false;

		while ( $i < $len ) {
			$ch   = $content[ $i ];
			$next = $i + 1 < $len ? $content[ $i + 1 ] : '';

			if ( null !== $quote ) {
				$out .= $ch;
				if ( $escape ) {
					$escape = false;
				} elseif ( '\\' === $ch ) {
					$escape = true;
				} elseif ( $ch === $quote ) {
					$quote = null;
				}
				$prev = $ch;
				++$i;
				continue;
			}

			if ( $regex ) {
				$out .= $ch;
				if ( $escape ) {
					$escape = false;
				} elseif ( '\\' === $ch ) {
					$escape = true;
				} elseif ( '/' === $ch ) {
					$regex = false;
				}
				$prev = $ch;
				++$i;
				continue;
			}

			if ( '/' === $ch && '/' === $next ) {
				$i += 2;
				while ( $i < $len && "\n" !== $content[ $i ] && "\r" !== $content[ $i ] ) {
					++$i;
				}
				continue;
			}

			if ( '/' === $ch && '*' === $next ) {
				$i += 2;
				while ( $i < $len && ! ( '*' === $content[ $i ] && '/' === ( $content[ $i + 1 ] ?? '' ) ) ) {
					++$i;
				}
				$i += 2;
				continue;
			}

			if ( "'" === $ch || '"' === $ch || '`' === $ch ) {
				$quote = $ch;
				$out  .= $ch;
				$prev  = $ch;
				++$i;
				continue;
			}

			if ( '/' === $ch && $this->canStartRegex( $prev ) ) {
				$regex = true;
				$out  .= $ch;
				$prev  = $ch;
				++$i;
				continue;
			}

			if ( $this->isWhitespace( $ch ) ) {
				$j = $i;
				while ( $j < $len && $this->isWhitespace( $content[ $j ] ) ) {
					++$j;
				}
				$nextChar = $content[ $j ] ?? '';
				if ( '' !== $nextChar && '' !== $out && $this->needsSpace( $prev, $nextChar ) ) {
					$out .= ' ';
				}
				$i = $j;
				continue;
			}

			if ( ';' === $ch ) {
				$j = $i + 1;
				while ( $j < $len && $this->isWhitespace( $content[ $j ] ) ) {
					++$j;
				}
				if ( '}' === ( $content[ $j ] ?? '' ) ) {
					++$i;
					continue;
				}
			}

			$out .= $ch;
			$prev = $ch;
			++$i;
		}

		return trim( $out );
	}

	private function isWhitespace( string $ch ): bool {
		return '' === $ch || strpos( " \t\n\r\f", $ch ) !== false;
	}

	private function needsSpace( string $prev, string $next ): bool {
		return $this->isWordChar( $prev ) && $this->isWordChar( $next );
	}

	private function isWordChar( string $ch ): bool {
		return '' !== $ch && (bool) preg_match( '/[A-Za-z0-9_$]/', $ch );
	}

	private function canStartRegex( string $prev ): bool {
		if ( '' === $prev ) {
			return true;
		}
		return (bool) preg_match( '/[([{=,:;!&|?+\-*%^~<>]/', $prev );
	}
}
