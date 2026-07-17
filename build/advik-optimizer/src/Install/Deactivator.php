<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Install;

class Deactivator {

	public static function deactivate(): void {
		wp_unschedule_hook( 'advik_optimizer_vitals_scan' );
		wp_unschedule_hook( 'advik_optimizer_db_cleanup' );
		wp_unschedule_hook( 'advik_optimizer_cache_warm' );
	}
}
