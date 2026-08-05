<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Minifier;

use AdvikLabs\Optimizer\Domain\Minify\Contract\RendererInterface;

class RemoteGetRenderer implements RendererInterface {

	public function render( string $url ): string {
		$response = wp_remote_get(
			$url,
			[
				'timeout'   => 15,
				'blocking'  => true,
				'sslverify' => false,
			]
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			return '';
		}

		$body = wp_remote_retrieve_body( $response );
		return is_string( $body ) ? $body : '';
	}
}
