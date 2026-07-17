<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Cache\Contract;

interface Warmable {

	public function warm( array $urls ): void;
}
