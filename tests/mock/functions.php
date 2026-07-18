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

function apply_filters( string $hook, mixed $value, mixed ...$args ): mixed {
	return $value;
}

function wp_die( string $message = '', string $title = '', array $args = [] ): void {
	throw new \RuntimeException( $message ?: 'wp_die called' );
}

function wp_create_nonce( string $action ): string {
	return md5( $action . '|test' );
}

function check_ajax_referer( string $action, string $query_arg = '_ajax_nonce', bool $die = true ): bool {
	return true;
}

function check_admin_referer( string $action, string $query_arg = '_wpnonce' ): bool {
	return true;
}

function wp_nonce_url( string $url, string $action, string $name = '_wpnonce' ): string {
	return add_query_arg( [ $name => wp_create_nonce( $action ) ], $url );
}

function current_user_can( string $capability, mixed ...$args ): bool {
	return true;
}

function sanitize_key( string $key ): string {
	return preg_replace( '/[^a-zA-Z0-9_\-]/', '', $key ) ?? '';
}

function wp_unslash( mixed $value ): mixed {
	return $value;
}

function esc_url_raw( string $url ): string {
	return $url;
}

function esc_html( string $text ): string {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

function esc_attr( string $text ): string {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

function wp_kses_post( string $data ): string {
	return $data;
}

function wp_safe_redirect( string $url, int $status = 302 ): void {
	throw new \RuntimeException( 'Redirect: ' . $url );
}

function wp_doing_ajax(): bool {
	return MockWP::get( 'wp_doing_ajax' ) ?? false;
}

function wp_add_inline_script( string $handle, string $data, string $position = 'after' ): bool {
	return true;
}

function is_front_page(): bool {
	return MockWP::get( 'is_front_page' ) ?? false;
}

function is_home(): bool {
	return MockWP::get( 'is_home' ) ?? false;
}

function is_singular( mixed $post_types = '' ): bool {
	return MockWP::get( 'is_singular' ) ?? false;
}

function is_archive(): bool {
	return MockWP::get( 'is_archive' ) ?? false;
}

function is_search(): bool {
	return MockWP::get( 'is_search' ) ?? false;
}

function is_404(): bool {
	return MockWP::get( 'is_404' ) ?? false;
}

function get_post_type( mixed $post = null ): string|false {
	return MockWP::get( 'get_post_type' ) ?: 'post';
}

function site_url(): string {
	return MockWP::get( 'site_url' ) ?: 'https://example.com';
}

function content_url(): string {
	return MockWP::get( 'content_url' ) ?: 'https://example.com/wp-content';
}

function home_url(): string {
	return MockWP::get( 'home_url' ) ?: 'https://example.com';
}

function __( string $text, string $domain = 'default' ): string {
	return $text;
}

function _e( string $text, string $domain = 'default' ): void {
	echo $text; // phpcs:ignore
}

function _n( string $single, string $plural, int $number, string $domain = 'default' ): string {
	return $number === 1 ? $single : $plural;
}

function _x( string $text, string $context, string $domain = 'default' ): string {
	return $text;
}

function esc_html__( string $text, string $domain = 'default' ): string {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

function esc_html_x( string $text, string $context, string $domain = 'default' ): string {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

function esc_attr__( string $text, string $domain = 'default' ): string {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

function human_time_diff( int $from, int $to = 0 ): string {
	$diff = $to > 0 ? $to - $from : time() - $from;
	return $diff < 60 ? 'a few seconds' : ( $diff < 3600 ? floor( $diff / 60 ) . ' min' : floor( $diff / 3600 ) . ' hours' );
}

function wp_next_scheduled( string $hook ): int|false {
	return false;
}

function get_option( string $option, mixed $default = false ): mixed {
	$key = 'option_' . $option;
	$val = MockWP::get( $key );
	return null !== $val ? $val : $default;
}

function update_option( string $option, mixed $value, bool $autoload = false ): bool {
	MockWP::set( 'option_' . $option, $value );
	return true;
}

function delete_option( string $option ): bool {
	MockWP::set( 'option_' . $option, null );
	return true;
}

function size_format( int|float $bytes, int $precision = 2 ): string|false {
	$units = [ 'B', 'KB', 'MB', 'GB', 'TB' ];
	$i     = 0;
	while ( $bytes >= 1024 && $i < count( $units ) - 1 ) {
		$bytes /= 1024;
		++$i;
	}
	return round( $bytes, $precision ) . ' ' . $units[ $i ];
}

function admin_url( string $path = '', string $scheme = 'admin' ): string {
	return 'https://example.com/wp-admin/' . ltrim( $path, '/' );
}

function esc_js( string $text ): string {
	return str_replace( [ '"', "'", '&', '<', '>' ], [ '\"', "\'", '&amp;', '&lt;', '&gt;' ], $text );
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

