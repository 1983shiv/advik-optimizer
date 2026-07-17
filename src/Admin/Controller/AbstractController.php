<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Admin\Controller;

abstract class AbstractController {

	protected function verifyCapability(): void {
		if ( ! current_user_can( 'manage_advik_optimizer' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'advik-optimizer' ) );
		}
	}

	protected function verifyNonce( string $action, string $nonceField = '_wpnonce' ): void {
		if ( ! isset( $_POST[ $nonceField ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ $nonceField ] ), $action ) ) {
			wp_die( esc_html__( 'Security check failed.', 'advik-optimizer' ) );
		}
	}
}
