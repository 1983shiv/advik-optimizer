<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Install;

class Uninstaller {

	public static function uninstall(): void {
		$settings = get_option( 'advik_optimizer_settings', [] );

		if ( ! empty( $settings['uninstall_keep_data'] ) ) {
			return;
		}

		global $wpdb;
		$prefix = $wpdb->prefix;

		$tables = [
			"{$prefix}advik_cwv_metrics",
			"{$prefix}advik_cache_log",
			"{$prefix}advik_image_optimizations",
			"{$prefix}advik_waitlist",
			"{$prefix}advik_db_cleanup_log",
		];

		foreach ( $tables as $table ) {
			$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table ) );
		}

		delete_option( 'advik_optimizer_settings' );
		delete_option( 'advik_optimizer_version' );
		delete_option( 'advik_optimizer_activation_time' );
	}
}
