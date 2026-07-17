<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Support;

class SettingsRegistry {

	private array $fields = [];

	public function addField( string $key, array $config ): void {
		$this->fields[ $key ] = wp_parse_args(
			$config,
			[
				'type'         => 'string',
				'default'      => '',
				'sanitize'     => 'sanitize_text_field',
				'rest_exposed' => false,
				'label'        => '',
			]
		);
	}

	public function getField( string $key ): ?array {
		return $this->fields[ $key ] ?? null;
	}

	public function getFields(): array {
		return $this->fields;
	}

	public function getDefaults(): array {
		$defaults = [];

		foreach ( $this->fields as $key => $config ) {
			$defaults[ $key ] = $config['default'];
		}

		return $defaults;
	}
}
