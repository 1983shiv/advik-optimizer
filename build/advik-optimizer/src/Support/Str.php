<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Support;

class Str {

	public static function slugify( string $text ): string {
		$text = mb_strtolower( $text, 'UTF-8' );
		$text = preg_replace( '/[^\w\s-]/u', '', $text );
		$text = preg_replace( '/[\s_]+/', '-', $text );
		$text = trim( $text, '-' );
		$text = preg_replace( '/-+/', '-', $text );

		return $text;
	}

	public static function truncate( string $text, int $length = 100, string $append = '…' ): string {
		if ( mb_strlen( $text ) <= $length ) {
			return $text;
		}

		$truncated = mb_substr( $text, 0, $length );
		$lastSpace = mb_strrpos( $truncated, ' ' );

		if ( false !== $lastSpace ) {
			$truncated = mb_substr( $truncated, 0, $lastSpace );
		}

		return $truncated . $append;
	}
}
