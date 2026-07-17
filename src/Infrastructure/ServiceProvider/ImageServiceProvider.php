<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Infrastructure\ServiceProvider;

use AdvikLabs\Optimizer\Container\ContainerInterface;
use AdvikLabs\Optimizer\Domain\Image\Encoder\EncoderFactory;
use AdvikLabs\Optimizer\Domain\Image\Repository\ImageOptimizationRepository;
use AdvikLabs\Optimizer\Domain\Image\Service\ImageOptimizationService;
use AdvikLabs\Optimizer\Domain\Image\Service\ImageQueueService;
use AdvikLabs\Optimizer\Domain\Image\Service\ImageRestoreService;
use AdvikLabs\Optimizer\Domain\Image\Service\ImageSavingsService;
use AdvikLabs\Optimizer\Frontend\ImageRewriter;
use AdvikLabs\Optimizer\Support\SettingsRegistry;

class ImageServiceProvider extends AbstractServiceProvider {

	public function register( ContainerInterface $container ): void {
		$container->singleton(
			ImageOptimizationRepository::class,
			function () {
				global $wpdb;
				return new ImageOptimizationRepository( $wpdb );
			}
		);

		$container->singleton(
			EncoderFactory::class,
			function () {
				return new EncoderFactory();
			}
		);

		$container->singleton(
			ImageOptimizationService::class,
			function ( ContainerInterface $c ) {
				return new ImageOptimizationService(
					$c->get( EncoderFactory::class ),
					$c->get( SettingsRegistry::class )
				);
			}
		);

		$container->singleton(
			ImageQueueService::class,
			function ( ContainerInterface $c ) {
				return new ImageQueueService(
					$c->get( ImageOptimizationRepository::class ),
					$c->get( ImageOptimizationService::class )
				);
			}
		);

		$container->singleton(
			ImageRestoreService::class,
			function ( ContainerInterface $c ) {
				return new ImageRestoreService(
					$c->get( ImageOptimizationRepository::class )
				);
			}
		);

		$container->singleton(
			ImageSavingsService::class,
			function ( ContainerInterface $c ) {
				return new ImageSavingsService(
					$c->get( ImageOptimizationRepository::class )
				);
			}
		);

		$container->singleton(
			ImageRewriter::class,
			function () {
				return new ImageRewriter();
			}
		);
	}
}
