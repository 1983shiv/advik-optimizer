<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Install;

class Activator {

	/**
	 * Plugin version stored in options — used by upgrade() to detect schema changes.
	 */
	private const VERSION_OPTION = 'advik_optimizer_version';

	public static function activate(): void {
		self::createTables();
		self::setDefaultOptions();
		self::registerCapability();
		self::createUploadDir();
		update_option( self::VERSION_OPTION, ADVIK_OPTIMIZER_VERSION );
	}

	/**
	 * Run pending schema / data upgrades on every plugin boot.
	 * Compares stored version with ADVIK_OPTIMIZER_VERSION and applies
	 * incremental changes as needed.
	 */
	public static function upgrade(): void {
		$stored = get_option( self::VERSION_OPTION, '0.0.0' );

		if ( version_compare( $stored, ADVIK_OPTIMIZER_VERSION, '>=' ) ) {
			return;
		}

		global $wpdb;

		self::upgradeCwvMetricsEnum( $wpdb );

		update_option( self::VERSION_OPTION, ADVIK_OPTIMIZER_VERSION );
	}

	/**
	 * Ensure the metric_type ENUM on wp_advik_cwv_metrics includes
	 * category-score values (performance, seo, accessibility, best_practices).
	 */
	private static function upgradeCwvMetricsEnum( $wpdb ): void {
		$table = $wpdb->prefix . 'advik_cwv_metrics';
		$row   = $wpdb->get_row( "SHOW COLUMNS FROM {$table} WHERE Field = 'metric_type'" );

		if ( ! $row ) {
			return;
		}

		$type = $row->Type;

		if ( false !== strpos( $type, 'best_practices' ) ) {
			return;
		}

		$wpdb->query(
			"ALTER TABLE {$table}
             MODIFY COLUMN metric_type
             ENUM('lcp','cls','inp','ttfb','performance','seo','accessibility','best_practices')
             NOT NULL"
		);

		$wpdb->query( "DELETE FROM {$table} WHERE metric_type = ''" );
	}

	private static function createTables(): void {
		global $wpdb;

		$charsetCollate = $wpdb->get_charset_collate();
		$prefix         = $wpdb->prefix;

		$tables = [
			"CREATE TABLE {$prefix}advik_cwv_metrics (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                url_hash CHAR(32) NOT NULL,
                url TEXT NOT NULL,
                metric_type ENUM('lcp','cls','inp','ttfb','performance','seo','accessibility','best_practices') NOT NULL,
                value DECIMAL(10,3) NOT NULL,
                device ENUM('mobile','desktop') NOT NULL,
                source ENUM('field','lab') NOT NULL,
                recorded_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY recorded_at (recorded_at)
            ) {$charsetCollate};",

			"CREATE TABLE {$prefix}advik_cache_log (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                object_id BIGINT UNSIGNED DEFAULT NULL,
                action ENUM('purge','warm','write') NOT NULL,
                url TEXT DEFAULT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id)
            ) {$charsetCollate};",

			"CREATE TABLE {$prefix}advik_image_optimizations (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                attachment_id BIGINT UNSIGNED NOT NULL,
                original_size INT UNSIGNED NOT NULL,
                optimized_size INT UNSIGNED DEFAULT NULL,
                format ENUM('webp','avif','original') DEFAULT NULL,
                status ENUM('pending','processing','done','failed','restored') NOT NULL DEFAULT 'pending',
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (id)
            ) {$charsetCollate};",

			"CREATE TABLE {$prefix}advik_waitlist (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                email VARCHAR(191) NOT NULL,
                status ENUM('pending','confirmed','unsubscribed') NOT NULL DEFAULT 'pending',
                consent_at DATETIME DEFAULT NULL,
                confirmed_at DATETIME DEFAULT NULL,
                ip_hash CHAR(64) DEFAULT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY email (email)
            ) {$charsetCollate};",

			"CREATE TABLE {$prefix}advik_db_cleanup_log (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                task VARCHAR(64) NOT NULL,
                rows_affected INT UNSIGNED NOT NULL,
                dry_run TINYINT(1) NOT NULL,
                run_at DATETIME NOT NULL,
                PRIMARY KEY (id)
            ) {$charsetCollate};",
		];

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		foreach ( $tables as $sql ) {
			dbDelta( $sql );
		}
	}

	private static function setDefaultOptions(): void {
		$defaults = [
			'module_cache'          => true,
			'module_images'         => true,
			'module_minify'         => true,
			'module_vitals'         => true,
			'module_seo'            => true,
			'module_cdn'            => false,
			'module_database'       => true,
			'cache_ttl'             => 3600,
			'image_quality'         => 82,
			'vitals_sampling_rate'  => 10,
			'vitals_psi_api_key'    => '',
			'vitals_alert_lcp'      => 2.5,
			'vitals_alert_cls'      => 0.1,
			'vitals_alert_inp'      => 200,
			'vitals_alert_email'    => false,
			'vitals_alert_webhook'  => false,
			'vitals_webhook_url'    => '',
			'uninstall_keep_data'   => false,
		];

		add_option( 'advik_optimizer_settings', $defaults );
		add_option( 'advik_optimizer_version', ADVIK_OPTIMIZER_VERSION );
		add_option( 'advik_optimizer_activation_time', time() );
	}

	private static function registerCapability(): void {
		$role = get_role( 'administrator' );

		if ( null !== $role ) {
			$role->add_cap( 'manage_advik_optimizer' );
		}
	}

	private static function createUploadDir(): void {
		$uploadDir    = wp_upload_dir();
		$baseDir      = $uploadDir['basedir'] . '/advik-optimizer';
		$cacheDir     = $baseDir . '/cache';
		$assetsDir    = $baseDir . '/assets';

		foreach ( [ $baseDir, $cacheDir, $assetsDir ] as $dir ) {
			if ( ! is_dir( $dir ) ) {
				wp_mkdir_p( $dir );
			}
		}
	}
}
