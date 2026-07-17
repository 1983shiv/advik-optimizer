<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Container;

use RuntimeException;

class Container implements ContainerInterface {

	private array $bindings   = [];
	private array $singletons = [];
	private array $instances  = [];

	public function bind( string $abstract, callable $factory ): void {
		$this->bindings[ $abstract ] = $factory;
	}

	public function singleton( string $abstract, callable $factory ): void {
		$this->singletons[ $abstract ] = $factory;
	}

	public function get( string $id ): object {
		if ( isset( $this->instances[ $id ] ) ) {
			return $this->instances[ $id ];
		}

		if ( isset( $this->singletons[ $id ] ) ) {
			$instance               = ( $this->singletons[ $id ] )( $this );
			$this->instances[ $id ] = $instance;
			return $instance;
		}

		if ( isset( $this->bindings[ $id ] ) ) {
			return ( $this->bindings[ $id ] )( $this );
		}

		throw new RuntimeException( "Container: no binding found for {$id}" );
	}

	public function has( string $id ): bool {
		return isset( $this->bindings[ $id ] ) || isset( $this->singletons[ $id ] );
	}
}
