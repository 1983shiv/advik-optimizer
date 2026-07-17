<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Hook;

use AdvikLabs\Optimizer\Hook\Contract\ListenerInterface;

class HookRegistrar {

	private array $listeners = [];

	public function addListener( ListenerInterface $listener ): void {
		$this->listeners[] = $listener;
	}

	public function register(): void {
		foreach ( $this->listeners as $listener ) {
			foreach ( $listener->subscribedEvents() as $event => $config ) {
				$priority     = 10;
				$acceptedArgs = 1;

				if ( is_array( $config ) ) {
					$method       = $config[0];
					$priority     = $config[1] ?? 10;
					$acceptedArgs = $config[2] ?? 1;
				} else {
					$method = $config;
				}

				add_filter( $event, [ $listener, $method ], $priority, $acceptedArgs );
			}
		}
	}

	public function getListeners(): array {
		return $this->listeners;
	}
}
