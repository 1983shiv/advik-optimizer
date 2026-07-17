<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Admin\Controller;

use AdvikLabs\Optimizer\Support\SettingsRegistry;
use AdvikLabs\Optimizer\Admin\View\SettingsView;
use AdvikLabs\Optimizer\Domain\Cache\Service\CachePurgeService;
use AdvikLabs\Optimizer\Domain\Image\Repository\ImageOptimizationRepository;

class SettingsController extends AbstractController {

	private SettingsRegistry $registry;
	private SettingsView $view;
	private CachePurgeService $purgeService;
	private ?ImageOptimizationRepository $imageRepo;

	public function __construct(
		SettingsRegistry $registry,
		SettingsView $view,
		CachePurgeService $purgeService,
		?ImageOptimizationRepository $imageRepo = null
	) {
		$this->registry     = $registry;
		$this->view         = $view;
		$this->purgeService = $purgeService;
		$this->imageRepo    = $imageRepo;
	}

	public function index(): void {
		$this->verifyCapability();

		$tab      = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'cache';
		$settings = get_option( 'advik_optimizer_settings', [] );
		$template = match ( $tab ) {
			'vitals' => 'settings-vitals',
			'images' => 'settings-images',
			default  => 'settings-cache',
		};

		$extra = [];

		if ( 'images' === $tab && null !== $this->imageRepo ) {
			$extra['queue'] = $this->imageRepo->getAll();
		}

		$this->view->render(
			$template,
			array_merge(
				[
					'tab'      => $tab,
					'settings' => $settings,
					'fields'   => $this->registry->getFields(),
				],
				$extra
			)
		);
	}

	public function save(): void {
		$this->verifyCapability();
		check_admin_referer( 'advik_optimizer_save_settings' );

		$tab      = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( $_POST['tab'] ) ) : 'cache';
		$settings = get_option( 'advik_optimizer_settings', [] );
		$fields   = $this->registry->getFields();

		foreach ( $fields as $key => $config ) {
			if ( isset( $_POST[ $key ] ) ) {
				$raw   = wp_unslash( $_POST[ $key ] );
				$value = $raw;

				if ( is_callable( $config['sanitize'] ?? '' ) ) {
					$value = call_user_func( $config['sanitize'], $value );
				} else {
					$value = sanitize_text_field( $value );
				}

				if ( 'checkbox' === $config['type'] ) {
					$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
				}

				$settings[ $key ] = $value;
			} elseif ( 'checkbox' === ( $config['type'] ?? '' ) ) {
				$settings[ $key ] = false;
			}
		}

		update_option( 'advik_optimizer_settings', $settings );

		$purgeCache = isset( $_POST['purge_cache'] ) ? sanitize_key( wp_unslash( $_POST['purge_cache'] ) ) : '';
		if ( ! empty( $purgeCache ) ) {
			$scope = isset( $_POST['purge_scope'] ) ? sanitize_key( wp_unslash( $_POST['purge_scope'] ) ) : 'all';
			$this->purgeService->purge( [ 'scope' => $scope ] );
		}

		wp_safe_redirect(
			add_query_arg(
				[
					'page'  => 'advik-optimizer',
					'tab'   => $tab,
					'saved' => '1',
				],
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}
