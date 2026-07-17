<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Container;

interface ContainerInterface {

	public function bind( string $abstract, callable $factory ): void;
	public function singleton( string $abstract, callable $factory ): void;
	public function get( string $id ): object;
	public function has( string $id ): bool;
}
