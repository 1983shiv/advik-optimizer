<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Admin\Menu;

use AdvikLabs\Optimizer\Admin\Controller\SettingsController;
use AdvikLabs\Optimizer\Admin\Controller\DashboardController;

class AdminMenuRegistrar {

	private SettingsController $settingsController;
	private DashboardController $dashboardController;

	public function __construct( SettingsController $settingsController, DashboardController $dashboardController ) {
		$this->settingsController   = $settingsController;
		$this->dashboardController  = $dashboardController;
	}

	public function register(): void {
		add_menu_page(
			__( 'Advik Optimizer', 'advik-optimizer' ),
			__( 'Advik Optimizer', 'advik-optimizer' ),
			'manage_advik_optimizer',
			'advik-optimizer',
			[ $this, 'renderPage' ],
			'dashicons-performance',
			3
		);

		add_submenu_page(
			'advik-optimizer',
			__( 'Dashboard', 'advik-optimizer' ),
			__( 'Dashboard', 'advik-optimizer' ),
			'manage_advik_optimizer',
			'advik-optimizer',
			[ $this, 'renderPage' ]
		);

		add_submenu_page(
			'advik-optimizer',
			__( 'Settings', 'advik-optimizer' ),
			__( 'Settings', 'advik-optimizer' ),
			'manage_advik_optimizer',
			'advik-optimizer-settings',
			[ $this, 'renderPage' ]
		);
	}

	public function renderPage(): void {
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		if ( 'advik-optimizer-settings' === $page ) {
			if ( '' === $tab ) {
				$tab = 'cache';
			}
			$_GET['tab'] = $tab;
			$this->settingsController->index();
			return;
		}

		$this->dashboardController->index();
	}
}
