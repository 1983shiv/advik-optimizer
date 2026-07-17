<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Admin\View;

abstract class AbstractView {

	protected string $templateDir;

	public function __construct() {
		$this->templateDir = ADVIK_OPTIMIZER_DIR . 'templates/admin/';
	}

	protected function render( string $template, array $data = [] ): void {
		$file = $this->templateDir . $template . '.php';

		if ( ! file_exists( $file ) ) {
			return;
		}

		$settings = $data['settings'] ?? [];
		$tab      = $data['tab'] ?? 'cache';
		$fields   = $data['fields'] ?? [];

		foreach ( $data as $key => $value ) {
			if ( in_array( $key, [ 'settings', 'tab', 'fields' ], true ) ) {
				continue;
			}
			${$key} = $value;
		}

		include $file;
	}
}
