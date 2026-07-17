<?php

declare(strict_types=1);

class MockWP {
	private static array $state = [
		'is_user_logged_in' => false,
		'wp_upload_basedir' => '',
		'wp_upload_baseurl' => 'http://example.com',
		'wp_json_encode_args' => null,
		'wp_delete_file_result' => true,
		'wp_mkdir_p_result' => true,
		'current_time_return' => '2024-01-01 00:00:00',
		'wp_remote_get_response' => null,
		'is_wp_error_result' => false,
		'wp_remote_retrieve_body_return' => '',
	];

	private static array $calls = [];

	public static function set( string $key, $value ): void {
		self::$state[ $key ] = $value;
	}

	public static function get( string $key ) {
		return self::$state[ $key ] ?? null;
	}

	public static function wasCalled( string $function ): bool {
		return isset( self::$calls[ $function ] ) && self::$calls[ $function ] > 0;
	}

	public static function reset(): void {
		self::$state = [
			'is_user_logged_in' => false,
			'wp_upload_basedir' => sys_get_temp_dir(),
			'wp_upload_baseurl' => 'http://example.com',
			'wp_json_encode_args' => null,
			'wp_delete_file_result' => true,
			'wp_mkdir_p_result' => true,
			'current_time_return' => '2024-01-01 00:00:00',
			'wp_remote_get_response' => null,
			'is_wp_error_result' => false,
			'wp_remote_retrieve_body_return' => '',
		];
		self::$calls = [];
	}
}

function is_user_logged_in(): bool {
	MockWP::set( '_last_called', 'is_user_logged_in' );
	return (bool) MockWP::get( 'is_user_logged_in' );
}

function wp_upload_dir(): array {
	return [
		'basedir' => MockWP::get( 'wp_upload_basedir' ) ?: sys_get_temp_dir(),
		'baseurl' => MockWP::get( 'wp_upload_baseurl' ) ?: 'http://example.com',
	];
}

function wp_json_encode( $data, int $options = 0, int $depth = 512 ): string|false {
	return json_encode( $data, $options, $depth );
}

function wp_delete_file( string $file ): bool {
	if ( file_exists( $file ) ) {
		@unlink( $file );
		return ! file_exists( $file );
	}
	return true;
}

function wp_mkdir_p( string $path ): bool {
	if ( ! is_dir( $path ) ) {
		@mkdir( $path, 0777, true );
	}
	return is_dir( $path );
}

function current_time( string $type ): string {
	if ( 'mysql' === $type ) {
		return MockWP::get( 'current_time_return' ) ?: '2024-01-01 00:00:00';
	}
	return (string) time();
}

function wp_remote_get( string $url, array $args = [] ) {
	MockWP::set( '_last_remote_get_url', $url );
	return MockWP::get( 'wp_remote_get_response' );
}

function is_wp_error( $thing ): bool {
	return (bool) MockWP::get( 'is_wp_error_result' );
}

function wp_remote_retrieve_body( $response ): string {
	return MockWP::get( 'wp_remote_retrieve_body_return' ) ?: '';
}

function wp_remote_retrieve_response_code( $response ): int {
	$val = MockWP::get( 'wp_remote_retrieve_response_code_return' );
	if ( null !== $val ) {
		return (int) $val;
	}
	if ( is_array( $response ) && isset( $response['response']['code'] ) ) {
		return (int) $response['response']['code'];
	}
	return 0;
}

function do_action( ...$args ): void {
}

function home_url(): string {
	return MockWP::get( 'home_url' ) ?: 'https://example.com';
}

function rest_url(): string {
	return MockWP::get( 'rest_url' ) ?: 'https://example.com/wp-json/';
}

function add_query_arg( ...$args ): string {
	$base = 'https://example.com';
	$params = [];

	if ( 3 === count( $args ) ) {
		$params = [ $args[0] => $args[1] ];
	} elseif ( isset( $args[0] ) && is_array( $args[0] ) ) {
		$params = $args[0];
	}

	$query = http_build_query( $params );
	return '' !== $query ? $base . '/?' . $query : $base;
}

function wp_rand( int $min = 0, int $max = 100 ): int {
	return MockWP::get( 'wp_rand_return' ) ?? rand( $min, $max );
}

defined( 'HOUR_IN_SECONDS' ) || define( 'HOUR_IN_SECONDS', 3600 );

