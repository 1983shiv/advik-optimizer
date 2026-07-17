<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Rest\Http;

trait RestResponder {

	protected function success( $data = null, array $meta = [] ): \WP_REST_Response {
		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => $data,
				'meta'    => $meta,
			],
			200
		);
	}

	protected function error( string $code, string $message, int $status = 400 ): \WP_REST_Response {
		return new \WP_REST_Response(
			[
				'success' => false,
				'error'   => [
					'code'    => $code,
					'message' => $message,
				],
			],
			$status
		);
	}
}
