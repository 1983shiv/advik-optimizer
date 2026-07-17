<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Cache\Contract;

interface Purgeable {

	public function purge( array $context ): bool;
}
