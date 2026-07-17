<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Cache\Service;

class CacheEligibility {

	private array $settings;

	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	public function isEligible(): bool {
		if ( ! ( $this->settings['module_cache'] ?? true ) ) {
			return false;
		}

		if ( ! $this->isGetRequest() ) {
			return false;
		}

		if ( $this->isLoggedIn() ) {
			return false;
		}

		if ( $this->isExcludedUrl() ) {
			return false;
		}

		return true;
	}

	private function isGetRequest(): bool {
		return isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' === strtoupper( $_SERVER['REQUEST_METHOD'] );
	}

	private function isLoggedIn(): bool {
		if ( ! empty( $this->settings['exclude_logged_in'] ) ) {
			return is_user_logged_in();
		}

		return is_user_logged_in();
	}

	private function isExcludedUrl(): bool {
		$patterns = $this->settings['excluded_urls'] ?? '';

		if ( empty( $patterns ) ) {
			return false;
		}

		$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
		$lines      = explode( "\n", $patterns );

		foreach ( $lines as $pattern ) {
			$pattern = trim( $pattern );

			if ( empty( $pattern ) ) {
				continue;
			}

			$regex = preg_quote( $pattern, '#' );
			$regex = str_replace( '\*', '.*', $regex );

			if ( preg_match( '#^' . $regex . '#', $requestUri ) ) {
				return true;
			}
		}

		return false;
	}
}
