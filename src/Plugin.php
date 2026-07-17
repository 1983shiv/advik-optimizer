<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer;

use AdvikLabs\Optimizer\Container\Container;
use AdvikLabs\Optimizer\Container\ContainerInterface;
use AdvikLabs\Optimizer\Admin\Menu\AdminMenuRegistrar;
use AdvikLabs\Optimizer\Admin\Asset\AdminAssetRegistrar;
use AdvikLabs\Optimizer\Admin\Controller\SettingsController;
use AdvikLabs\Optimizer\Admin\Controller\DashboardController;
use AdvikLabs\Optimizer\Admin\View\DashboardView;
use AdvikLabs\Optimizer\Admin\View\SettingsView;
use AdvikLabs\Optimizer\Hook\HookRegistrar;
use AdvikLabs\Optimizer\Hook\Listener\ServeCacheListener;
use AdvikLabs\Optimizer\Hook\Listener\ContentChangeListener;
use AdvikLabs\Optimizer\Infrastructure\ServiceProvider\CacheServiceProvider;
use AdvikLabs\Optimizer\Infrastructure\ServiceProvider\VitalsServiceProvider;
use AdvikLabs\Optimizer\Infrastructure\Cron\VitalsScanJob;
use AdvikLabs\Optimizer\Rest\RestKernel;
use AdvikLabs\Optimizer\Rest\Controller\AuditController;
use AdvikLabs\Optimizer\Rest\Controller\CacheController;
use AdvikLabs\Optimizer\Rest\Controller\ScoreController;
use AdvikLabs\Optimizer\Rest\Controller\VitalsController;
use AdvikLabs\Optimizer\Install\Activator;
use AdvikLabs\Optimizer\Support\SettingsRegistry;

class Plugin {

	private ContainerInterface $container;
	private bool $booted = false;

	public function __construct() {
		$this->container = new Container();
	}

	public function boot(): void {
		if ( $this->booted ) {
			return;
		}

		$this->booted = true;

		Activator::upgrade();

		$this->registerDefaults();
		$this->registerServiceProviders();
		$this->registerControllers();
		$this->registerHooks();
		$this->bootSubKernels();
	}

	private function registerDefaults(): void {
		$this->container->singleton( ContainerInterface::class, fn () => $this->container );
		$this->container->singleton( HookRegistrar::class, fn () => new HookRegistrar() );
		$this->container->singleton(
			SettingsRegistry::class,
			function () {
				$registry = new SettingsRegistry();

				$registry->addField(
					'module_cache',
					[
						'type'     => 'checkbox',
						'default'  => true,
						'sanitize' => fn ( $v ) => filter_var( $v, FILTER_VALIDATE_BOOLEAN ),
						'label'    => __( 'Enable page caching', 'advik-optimizer' ),
					]
				);
				$registry->addField(
					'cache_ttl',
					[
						'type'    => 'number',
						'default' => 3600,
						'label'   => __( 'Cache TTL (seconds)', 'advik-optimizer' ),
					]
				);
				$registry->addField(
					'exclude_logged_in',
					[
						'type'     => 'checkbox',
						'default'  => true,
						'sanitize' => fn ( $v ) => filter_var( $v, FILTER_VALIDATE_BOOLEAN ),
						'label'    => __( 'Never cache pages for logged-in visitors', 'advik-optimizer' ),
					]
				);
				$registry->addField(
					'excluded_urls',
					[
						'type'    => 'textarea',
						'default' => '',
						'label'   => __( 'Never cache these URLs', 'advik-optimizer' ),
					]
				);
				$registry->addField(
					'cache_warming',
					[
						'type'     => 'checkbox',
						'default'  => false,
						'sanitize' => fn ( $v ) => filter_var( $v, FILTER_VALIDATE_BOOLEAN ),
						'label'    => __( 'Automatically rebuild cache after it is cleared', 'advik-optimizer' ),
					]
				);

				$registry->addField(
					'module_vitals',
					[
						'type'     => 'checkbox',
						'default'  => true,
						'sanitize' => fn ( $v ) => filter_var( $v, FILTER_VALIDATE_BOOLEAN ),
						'label'    => __( 'Enable Core Web Vitals tracking', 'advik-optimizer' ),
					]
				);

				$registry->addField(
					'vitals_sampling_rate',
					[
						'type'    => 'number',
						'default' => 10,
						'label'   => __( 'RUM sampling rate (%)', 'advik-optimizer' ),
					]
				);
				$registry->addField(
					'vitals_psi_api_key',
					[
						'type'    => 'text',
						'default' => '',
						'label'   => __( 'PageSpeed Insights API Key', 'advik-optimizer' ),
					]
				);
				$registry->addField(
					'vitals_alert_lcp',
					[
						'type'    => 'number',
						'default' => 2.5,
						'label'   => __( 'LCP alert threshold (s)', 'advik-optimizer' ),
					]
				);
				$registry->addField(
					'vitals_alert_cls',
					[
						'type'    => 'number',
						'default' => 0.1,
						'label'   => __( 'CLS alert threshold', 'advik-optimizer' ),
					]
				);
				$registry->addField(
					'vitals_alert_inp',
					[
						'type'    => 'number',
						'default' => 200,
						'label'   => __( 'INP alert threshold (ms)', 'advik-optimizer' ),
					]
				);
				$registry->addField(
					'vitals_alert_email',
					[
						'type'     => 'checkbox',
						'default'  => false,
						'sanitize' => fn ( $v ) => filter_var( $v, FILTER_VALIDATE_BOOLEAN ),
						'label'    => __( 'Email alert', 'advik-optimizer' ),
					]
				);
				$registry->addField(
					'vitals_alert_email_address',
					[
						'type'    => 'text',
						'default' => '',
						'label'   => __( 'Alert email address', 'advik-optimizer' ),
					]
				);
				$registry->addField(
					'vitals_alert_webhook',
					[
						'type'     => 'checkbox',
						'default'  => false,
						'sanitize' => fn ( $v ) => filter_var( $v, FILTER_VALIDATE_BOOLEAN ),
						'label'    => __( 'Webhook alert', 'advik-optimizer' ),
					]
				);
				$registry->addField(
					'vitals_webhook_url',
					[
						'type'    => 'text',
						'default' => '',
						'label'   => __( 'Webhook URL', 'advik-optimizer' ),
					]
				);

				return $registry;
			}
		);

		$this->container->singleton( AdminAssetRegistrar::class, fn () => new AdminAssetRegistrar() );

		$this->container->singleton(
			DashboardView::class,
			function () {
				return new DashboardView();
			}
		);

		$this->container->singleton(
			SettingsView::class,
			function () {
				return new SettingsView();
			}
		);
	}

	private function registerServiceProviders(): void {
		( new CacheServiceProvider() )->register( $this->container );
		( new VitalsServiceProvider() )->register( $this->container );
	}

	private function registerControllers(): void {
		$this->container->singleton(
			DashboardController::class,
			function ( ContainerInterface $c ) {
				return new DashboardController(
					$c->get( DashboardView::class ),
					$c->get( \AdvikLabs\Optimizer\Domain\Vitals\Service\ScoreAggregatorService::class ),
					$c->get( \AdvikLabs\Optimizer\Domain\Cache\Service\CacheStatsService::class ),
					$c->get( \AdvikLabs\Optimizer\Domain\Vitals\Repository\AuditRepository::class )
				);
			}
		);

		$this->container->singleton(
			SettingsController::class,
			function ( ContainerInterface $c ) {
				return new SettingsController(
					$c->get( SettingsRegistry::class ),
					$c->get( SettingsView::class ),
					$c->get( \AdvikLabs\Optimizer\Domain\Cache\Service\CachePurgeService::class )
				);
			}
		);

		$this->container->singleton(
			AdminMenuRegistrar::class,
			function ( ContainerInterface $c ) {
				return new AdminMenuRegistrar(
					$c->get( SettingsController::class ),
					$c->get( DashboardController::class )
				);
			}
		);

		$this->container->singleton(
			ScoreController::class,
			function ( ContainerInterface $c ) {
				return new ScoreController(
					$c->get( \AdvikLabs\Optimizer\Domain\Vitals\Service\ScoreAggregatorService::class )
				);
			}
		);

		$this->container->singleton(
			VitalsController::class,
			function ( ContainerInterface $c ) {
				return new VitalsController(
					$c->get( \AdvikLabs\Optimizer\Domain\Vitals\Service\ScoreAggregatorService::class ),
					$c->get( \AdvikLabs\Optimizer\Domain\Vitals\Service\VitalsIngestService::class )
				);
			}
		);

		$this->container->singleton(
			RestKernel::class,
			function ( ContainerInterface $c ) {
				return new RestKernel(
					$c->get( AuditController::class ),
					$c->get( CacheController::class ),
					$c->get( ScoreController::class ),
					$c->get( VitalsController::class )
				);
			}
		);
	}

	private function registerHooks(): void {
		$registrar = $this->container->get( HookRegistrar::class );
		$registrar->register();

		$serveListener = $this->container->get( ServeCacheListener::class );
		add_action( 'template_redirect', [ $serveListener, 'serve' ], 0 );
		add_action( 'shutdown', [ $serveListener, 'onShutdown' ], 0 );

		$changeListener = $this->container->get( ContentChangeListener::class );
		add_action( 'save_post', [ $changeListener, 'onPostChange' ] );
		add_action( 'wp_trash_post', [ $changeListener, 'onPostChange' ] );
		add_action( 'comment_post', [ $changeListener, 'onCommentChange' ] );
		add_action( 'upgrader_process_complete', [ $changeListener, 'onThemeOrPluginChange' ] );
		add_action( 'wp_update_nav_menu', [ $changeListener, 'onMenuChange' ] );

		add_action(
			'admin_post_advik_optimizer_save_settings',
			function () {
				$controller = $this->container->get( SettingsController::class );
				$controller->save();
			}
		);

		add_action(
			'admin_post_advik_optimizer_scan_now',
			function () {
				if ( ! current_user_can( 'manage_advik_optimizer' ) ) {
					wp_die( esc_html__( 'You do not have sufficient permissions.', 'advik-optimizer' ) );
				}
				check_admin_referer( 'advik_optimizer_scan_now' );

				$scanService = $this->container->get( \AdvikLabs\Optimizer\Domain\Vitals\Service\VitalsScanService::class );
				$scanService->scanBoth( home_url() );

				update_option( 'advik_optimizer_last_scan_at', current_time( 'mysql' ) );

				wp_safe_redirect(
					add_query_arg(
						[
							'page' => 'advik-optimizer',
							'scanned' => '1',
						],
						admin_url( 'admin.php' )
					)
				);
				exit;
			}
		);

		add_filter(
			'cron_schedules',
			function ( array $schedules ): array {
				$schedules[ VitalsScanJob::getRecurrence() ] = [
					'interval' => HOUR_IN_SECONDS,
					'display'  => __( 'Every hour', 'advik-optimizer' ),
				];
				return $schedules;
			}
		);

		add_action(
			VitalsScanJob::getHook(),
			function () {
				$this->container->get( VitalsScanJob::class )->execute();
			}
		);

		if ( ! wp_next_scheduled( VitalsScanJob::getHook() ) ) {
			VitalsScanJob::schedule();
		}

		add_action(
			'wp_enqueue_scripts',
			function () {
				$settings = get_option( 'advik_optimizer_settings', [] );
				if ( empty( $settings['module_vitals'] ) ) {
					return;
				}

				$samplingRate = (int) ( $settings['vitals_sampling_rate'] ?? 10 );
				if ( $samplingRate <= 0 ) {
					return;
				}

				$assetUrl = ADVIK_OPTIMIZER_URL . 'assets/public/js/advik-vitals-beacon.js';
				$filemtime = filemtime( ADVIK_OPTIMIZER_DIR . 'assets/public/js/advik-vitals-beacon.js' );
				$version   = false !== $filemtime ? $filemtime : ADVIK_OPTIMIZER_VERSION;

				wp_enqueue_script( 'advik-optimizer-vitals-beacon', $assetUrl, [], $version, true );
				wp_localize_script(
					'advik-optimizer-vitals-beacon',
					'advikVitals',
					[
						'restUrl' => rest_url(),
					]
				);
			}
		);
	}

	private function bootSubKernels(): void {
		if ( is_admin() ) {
			add_action(
				'admin_menu',
				function () {
					$this->container->get( AdminMenuRegistrar::class )->register();
				}
			);

			$assetRegistrar = $this->container->get( AdminAssetRegistrar::class );
			add_action( 'admin_enqueue_scripts', [ $assetRegistrar, 'enqueue' ] );
		}

		add_action(
			'rest_api_init',
			function () {
				$this->container->get( RestKernel::class )->registerRoutes();
			}
		);
	}

	public function getContainer(): ContainerInterface {
		return $this->container;
	}
}
