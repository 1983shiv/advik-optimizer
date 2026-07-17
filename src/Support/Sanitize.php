<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Support;

class Sanitize {

	public static function email( string $value ): string {
		return sanitize_email( $value );
	}

	public static function url( string $value ): string {
		return esc_url_raw( $value );
	}

	public static function text( string $value ): string {
		return sanitize_text_field( $value );
	}

	public static function key( string $value ): string {
		return sanitize_key( $value );
	}

	public static function html( string $value ): string {
		return wp_kses_post( $value );
	}
}
