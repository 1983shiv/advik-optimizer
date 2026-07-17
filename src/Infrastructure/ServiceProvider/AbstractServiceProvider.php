<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Infrastructure\ServiceProvider;

use AdvikLabs\Optimizer\Container\ContainerInterface;

abstract class AbstractServiceProvider {

	abstract public function register( ContainerInterface $container ): void;
}
