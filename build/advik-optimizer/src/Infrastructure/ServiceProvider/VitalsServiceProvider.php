<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Infrastructure\ServiceProvider;

use AdvikLabs\Optimizer\Container\ContainerInterface;
use AdvikLabs\Optimizer\Domain\Vitals\Repository\VitalsRepository;
use AdvikLabs\Optimizer\Domain\Vitals\Support\ScoreRubric;
use AdvikLabs\Optimizer\Domain\Vitals\Client\PsiApiClient;
use AdvikLabs\Optimizer\Domain\Vitals\Contract\LighthouseClientInterface;
use AdvikLabs\Optimizer\Domain\Vitals\Service\VitalsIngestService;
use AdvikLabs\Optimizer\Domain\Vitals\Service\VitalsScanService;
use AdvikLabs\Optimizer\Domain\Vitals\Service\ScoreAggregatorService;
use AdvikLabs\Optimizer\Infrastructure\Cron\VitalsScanJob;

class VitalsServiceProvider extends AbstractServiceProvider {

	public function register( ContainerInterface $container ): void {
		$container->singleton(
			VitalsRepository::class,
			function () {
				global $wpdb;
				return new VitalsRepository( $wpdb );
			}
		);

		$container->singleton(
			ScoreRubric::class,
			function () {
				return new ScoreRubric();
			}
		);

		$container->singleton(
			LighthouseClientInterface::class,
			function () {
				$settings = get_option( 'advik_optimizer_settings', [] );
				$apiKey   = $settings['vitals_psi_api_key'] ?? '';
				return new PsiApiClient( $apiKey );
			}
		);

		$container->singleton(
			VitalsIngestService::class,
			function ( ContainerInterface $c ) {
				return new VitalsIngestService(
					$c->get( VitalsRepository::class )
				);
			}
		);

		$container->singleton(
			VitalsScanService::class,
			function ( ContainerInterface $c ) {
				return new VitalsScanService(
					$c->get( LighthouseClientInterface::class ),
					$c->get( VitalsIngestService::class )
				);
			}
		);

		$container->singleton(
			ScoreAggregatorService::class,
			function ( ContainerInterface $c ) {
				return new ScoreAggregatorService(
					$c->get( VitalsRepository::class ),
					$c->get( ScoreRubric::class )
				);
			}
		);

		$container->singleton(
			VitalsScanJob::class,
			function ( ContainerInterface $c ) {
				return new VitalsScanJob(
					$c->get( VitalsScanService::class )
				);
			}
		);
	}
}
