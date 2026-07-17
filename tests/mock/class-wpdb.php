<?php

declare(strict_types=1);

class wpdb {
	public string $prefix = 'wp_';
	public $insert_id = 1;

	public function __construct( ...$args ) {
	}

	public function insert( string $table, array $data, array $formats = [] ): int|false {
		return 1;
	}

	public function prepare( string $query, ...$args ): string|false {
		$sql = $query;
		foreach ( $args as $arg ) {
			$pos = strpos( $sql, '%d' );
			if ( false !== $pos ) {
				$sql = substr_replace( $sql, (string) (int) $arg, $pos, 2 );
				continue;
			}
			$pos = strpos( $sql, '%s' );
			if ( false !== $pos ) {
				$sql = substr_replace( $sql, "'" . addslashes( (string) $arg ) . "'", $pos, 2 );
			}
		}
		return $sql;
	}

	public function get_results( string $query, string $output = OBJECT ): array|null|object {
		return [];
	}

	public function get_var( string $query = null, int $x = 0, int $y = 0 ): string|null {
		return null;
	}

	public function query( string $query ): int|bool {
		return true;
	}

	public function get_charset_collate(): string {
		return 'DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	}
}
