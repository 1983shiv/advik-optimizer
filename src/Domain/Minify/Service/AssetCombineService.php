<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Service;

class AssetCombineService {

	public function registerHooks(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'combineStyles' ], 1000 );
		add_action( 'wp_enqueue_scripts', [ $this, 'combineScripts' ], 1000 );
	}

	public function combineStyles(): void {
		$settings = get_option( 'advik_optimizer_settings', [] );
		if ( empty( $settings['minify_combine_css'] ) ) {
			return;
		}
	}

	public function combineScripts(): void {
		$settings = get_option( 'advik_optimizer_settings', [] );
		if ( empty( $settings['minify_combine_js'] ) ) {
			return;
		}
	}
}
