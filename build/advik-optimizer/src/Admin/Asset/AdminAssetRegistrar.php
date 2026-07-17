<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Admin\Asset;

class AdminAssetRegistrar {

	public function enqueue(): void {
		$screen = get_current_screen();

		if ( null === $screen || ! str_contains( $screen->id ?? '', 'advik-optimizer' ) ) {
			return;
		}

		wp_enqueue_style(
			'advik-optimizer-admin',
			ADVIK_OPTIMIZER_URL . 'assets/admin/css/advik-admin.css',
			[],
			ADVIK_OPTIMIZER_VERSION
		);

		wp_enqueue_script(
			'advik-optimizer-admin',
			ADVIK_OPTIMIZER_URL . 'assets/admin/js/advik-admin.js',
			[],
			ADVIK_OPTIMIZER_VERSION,
			true
		);
	}
}
