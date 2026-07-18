<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Service;

class MinifyRollbackGuard {

	private const ERROR_COUNT_KEY = 'advik_optimizer_minify_errors';
	private const ROLLBACK_KEY    = 'advik_optimizer_minify_rollback';
	private const THRESHOLD       = 3;

	public function registerHooks(): void {
		add_action( 'wp_footer', [ $this, 'injectErrorBeacon' ] );
		add_action( 'admin_notices', [ $this, 'showAdminNotice' ] );
		add_action( 'wp_ajax_advik_minify_report_error', [ $this, 'reportError' ] );
		add_action( 'admin_post_advik_optimizer_reenable_minify', [ $this, 'reenable' ] );
	}

	public function injectErrorBeacon(): void {
		if ( ! $this->isActive() ) {
			return;
		}

		$nonce   = wp_create_nonce( 'advik_minify_report_error' );
		$ajaxUrl = admin_url( 'admin-ajax.php' );
		wp_add_inline_script(
			'advik-optimizer-minify-beacon',
			'window.addEventListener("error",function(e){var r=new XMLHttpRequest;r.open("POST","' . esc_js( $ajaxUrl ) . '",!0);r.setRequestHeader("Content-Type","application/x-www-form-urlencoded");r.send("action=advik_minify_report_error&nonce=' . esc_js( $nonce ) . '&url="+encodeURIComponent(location.href));});'
		);
	}

	public function reportError(): void {
		if ( ! wp_doing_ajax() ) {
			return;
		}

		$key  = self::ERROR_COUNT_KEY;
		$data = get_option( $key, [] );

		if ( ! is_array( $data ) ) {
			$data = [];
		}

		check_ajax_referer( 'advik_minify_report_error', 'nonce' );

		$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';

		if ( '' === $url ) {
			wp_die();
		}

		$count = 1;
		if ( isset( $data[ $url ] ) ) {
			$count = $data[ $url ] + 1;
		}
		$data[ $url ] = $count;

		update_option( $key, $data );

		if ( $count >= self::THRESHOLD ) {
			$this->doRollback( $url );
		}

		wp_die();
	}

	public function isRolledBack(): bool {
		return (bool) get_option( self::ROLLBACK_KEY, false );
	}

	public function getRollbackUrl(): string {
		return (string) get_option( self::ROLLBACK_KEY . '_url', '' );
	}

	public function showAdminNotice(): void {
		if ( ! $this->isRolledBack() ) {
			return;
		}

		$url   = $this->getRollbackUrl();
		/* translators: %s: URL where the error was detected */
		$label = '' !== $url ? sprintf( __( 'Minification was paused on %s after we detected a script error. Review and re-enable.', 'advik-optimizer' ), esc_html( $url ) ) : __( 'Minification was paused after we detected a script error. Review and re-enable.', 'advik-optimizer' );
		$reEnableUrl = wp_nonce_url(
			admin_url( 'admin-post.php?action=advik_optimizer_reenable_minify' ),
			'advik_optimizer_reenable_minify'
		);
		?>
		<div class="notice notice-warning is-dismissible">
			<p><?php echo wp_kses_post( $label ); ?></p>
			<p><a href="<?php echo esc_url( $reEnableUrl ); ?>" class="button button-secondary"><?php echo esc_html__( 'Re-enable Minification', 'advik-optimizer' ); ?></a></p>
		</div>
		<?php
	}

	public function reenable(): void {
		if ( ! current_user_can( 'manage_advik_optimizer' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'advik-optimizer' ) );
		}
		check_admin_referer( 'advik_optimizer_reenable_minify' );

		delete_option( self::ERROR_COUNT_KEY );
		delete_option( self::ROLLBACK_KEY );
		delete_option( self::ROLLBACK_KEY . '_url' );

		wp_safe_redirect(
			add_query_arg(
				[
					'page'       => 'advik-optimizer-settings',
					'tab'        => 'minify',
					're-enabled' => '1',
				],
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	private function isActive(): bool {
		$settings = get_option( 'advik_optimizer_settings', [] );
		return ! empty( $settings['module_minify'] ) && ! $this->isRolledBack();
	}

	private function doRollback( string $url ): void {
		update_option( self::ROLLBACK_KEY, true );
		update_option( self::ROLLBACK_KEY . '_url', $url );
	}
}
