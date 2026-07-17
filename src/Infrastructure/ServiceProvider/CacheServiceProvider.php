<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Infrastructure\ServiceProvider;

use AdvikLabs\Optimizer\Container\ContainerInterface;
use AdvikLabs\Optimizer\Domain\Cache\Repository\CacheLogRepository;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheManager;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheReadService;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheWriteService;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheEligibility;
use AdvikLabs\Optimizer\Domain\Cache\Service\CachePurgeService;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheWarmService;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheStatsService;
use AdvikLabs\Optimizer\Hook\Listener\ServeCacheListener;
use AdvikLabs\Optimizer\Hook\Listener\ContentChangeListener;
use AdvikLabs\Optimizer\Admin\Controller\SettingsController;
use AdvikLabs\Optimizer\Admin\View\SettingsView;
use AdvikLabs\Optimizer\Rest\Controller\CacheController;
use AdvikLabs\Optimizer\Rest\RestKernel;
use AdvikLabs\Optimizer\Cli\Command\CacheCommand;
use AdvikLabs\Optimizer\Support\SettingsRegistry;

class CacheServiceProvider extends AbstractServiceProvider {

	public function register( ContainerInterface $container ): void {
		$container->singleton(
			CacheLogRepository::class,
			function () {
				global $wpdb;
				return new CacheLogRepository( $wpdb );
			}
		);

		$container->singleton(
			CacheManager::class,
			function () {
				$settings = get_option( 'advik_optimizer_settings', [] );
				return new CacheManager( $settings );
			}
		);

		$container->singleton(
			CacheReadService::class,
			function ( ContainerInterface $c ) {
				return new CacheReadService( $c->get( CacheManager::class ) );
			}
		);

		$container->singleton(
			CacheWriteService::class,
			function ( ContainerInterface $c ) {
				return new CacheWriteService(
					$c->get( CacheManager::class ),
					$c->get( CacheLogRepository::class )
				);
			}
		);

		$container->singleton(
			CacheEligibility::class,
			function () {
				$settings = get_option( 'advik_optimizer_settings', [] );
				return new CacheEligibility( $settings );
			}
		);

		$container->singleton(
			CachePurgeService::class,
			function ( ContainerInterface $c ) {
				return new CachePurgeService(
					$c->get( CacheManager::class ),
					$c->get( CacheLogRepository::class )
				);
			}
		);

		$container->singleton(
			CacheWarmService::class,
			function ( ContainerInterface $c ) {
				return new CacheWarmService(
					$c->get( CacheManager::class ),
					$c->get( CacheLogRepository::class )
				);
			}
		);

		$container->singleton(
			CacheStatsService::class,
			function ( ContainerInterface $c ) {
				return new CacheStatsService(
					$c->get( CacheManager::class ),
					$c->get( CacheLogRepository::class )
				);
			}
		);

		$container->singleton(
			ServeCacheListener::class,
			function ( ContainerInterface $c ) {
				return new ServeCacheListener(
					$c->get( CacheReadService::class ),
					$c->get( CacheWriteService::class ),
					$c->get( CacheEligibility::class )
				);
			}
		);

		$container->singleton(
			ContentChangeListener::class,
			function ( ContainerInterface $c ) {
				return new ContentChangeListener( $c->get( CachePurgeService::class ) );
			}
		);

		$container->singleton(
			SettingsView::class,
			function () {
				return new SettingsView();
			}
		);

		$container->singleton(
			SettingsController::class,
			function ( ContainerInterface $c ) {
				$registry = $c->get( SettingsRegistry::class );
				return new SettingsController(
					$registry,
					$c->get( SettingsView::class ),
					$c->get( CachePurgeService::class )
				);
			}
		);

		$container->singleton(
			CacheController::class,
			function ( ContainerInterface $c ) {
				return new CacheController(
					$c->get( CachePurgeService::class ),
					$c->get( CacheWarmService::class ),
					$c->get( CacheStatsService::class )
				);
			}
		);

		$container->singleton(
			RestKernel::class,
			function ( ContainerInterface $c ) {
				return new RestKernel( $c->get( CacheController::class ) );
			}
		);

		$container->singleton(
			CacheCommand::class,
			function ( ContainerInterface $c ) {
				return new CacheCommand(
					$c->get( CachePurgeService::class ),
					$c->get( CacheWarmService::class ),
					$c->get( CacheStatsService::class )
				);
			}
		);
	}
}
