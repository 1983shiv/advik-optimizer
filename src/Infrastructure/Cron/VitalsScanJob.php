<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Infrastructure\Cron;

use AdvikLabs\Optimizer\Domain\Vitals\Service\VitalsScanService;

class VitalsScanJob {

	private VitalsScanService $scanService;

	public function __construct( VitalsScanService $scanService ) {
		$this->scanService = $scanService;
	}

	public static function getHook(): string {
		return 'advik_optimizer_vitals_scan';
	}

	public static function getRecurrence(): string {
		return 'advik_optimizer_hourly';
	}

	public static function schedule(): void {
		if ( ! wp_next_scheduled( self::getHook() ) ) {
			wp_schedule_event( time(), self::getRecurrence(), self::getHook() );
		}
	}

	public static function unschedule(): void {
		$timestamp = wp_next_scheduled( self::getHook() );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::getHook() );
		}
	}

	public function execute(): void {
		$settings = get_option( 'advik_optimizer_settings', [] );

		if ( empty( $settings['module_vitals'] ) ) {
			return;
		}

		$apiKey = $settings['vitals_psi_api_key'] ?? '';
		if ( empty( $apiKey ) ) {
			return;
		}

		$this->scanService->scanBoth( home_url() );
	}
}
