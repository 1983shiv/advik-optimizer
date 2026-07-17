<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Support;

class Arr {

	public static function get( array $array, string $key, mixed $default = null ): mixed {
		if ( str_contains( $key, '.' ) ) {
			$keys  = explode( '.', $key );
			$value = $array;

			foreach ( $keys as $segment ) {
				if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
					return $default;
				}
				$value = $value[ $segment ];
			}

			return $value;
		}

		return array_key_exists( $key, $array ) ? $array[ $key ] : $default;
	}

	public static function only( array $array, array $keys ): array {
		$result = [];

		foreach ( $keys as $key ) {
			if ( array_key_exists( $key, $array ) ) {
				$result[ $key ] = $array[ $key ];
			}
		}

		return $result;
	}
}
