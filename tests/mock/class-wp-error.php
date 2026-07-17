<?php

declare(strict_types=1);

class WP_Error {
	private array $errors = [];

	public function __construct( string $code = '', string $message = '', mixed $data = '' ) {
		if ( ! empty( $code ) ) {
			$this->errors[ $code ][] = $message;
		}
	}

	public function get_error_code(): string {
		return array_key_first( $this->errors ) ?? '';
	}

	public function get_error_message(): string {
		$code = $this->get_error_code();
		return $this->errors[ $code ][0] ?? '';
	}
}
