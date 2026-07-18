<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Service;

class MinifySavingsService {

	public function summary(): array {
		$settings = get_option( 'advik_optimizer_settings', [] );

		if ( empty( $settings['module_minify'] ) ) {
			return [
				'savings'    => '&mdash;',
				'count'      => 0,
				'css_count'  => 0,
				'js_count'   => 0,
				'css_saved'  => 0,
				'js_saved'   => 0,
			];
		}

		$cssCount = $this->countCachedAssets( 'css' );
		$jsCount  = $this->countCachedAssets( 'js' );
		$cssSaved = $this->estimateSavings( 'css' );
		$jsSaved  = $this->estimateSavings( 'js' );
		$total    = $cssSaved + $jsSaved;

		return [
			'savings'   => $total > 0 ? size_format( $total ) : '&mdash;',
			'count'     => $cssCount + $jsCount,
			'css_count' => $cssCount,
			'js_count'  => $jsCount,
			'css_saved' => $cssSaved,
			'js_saved'  => $jsSaved,
		];
	}

	public function getTotalSavingsBytes(): int {
		$summary = $this->summary();
		return ( $summary['css_saved'] ?? 0 ) + ( $summary['js_saved'] ?? 0 );
	}

	public function getProcessedCount(): int {
		$summary = $this->summary();
		return $summary['count'] ?? 0;
	}

	private function getCacheDir(): string {
		$uploadDir = wp_upload_dir();
		return $uploadDir['basedir'] . '/advik-optimizer/cache/assets';
	}

	private function countCachedAssets( string $ext ): int {
		$dir = $this->getCacheDir();
		if ( ! is_dir( $dir ) ) {
			return 0;
		}

		$files = glob( $dir . '/*.' . $ext );
		return is_array( $files ) ? count( $files ) : 0;
	}

	private function estimateSavings( string $ext ): int {
		$dir = $this->getCacheDir();
		if ( ! is_dir( $dir ) ) {
			return 0;
		}

		$files = glob( $dir . '/*.' . $ext );
		if ( ! is_array( $files ) ) {
			return 0;
		}

		$total = 0;

		foreach ( $files as $file ) {
			$total += filesize( $file );
		}

		return $total;
	}
}
