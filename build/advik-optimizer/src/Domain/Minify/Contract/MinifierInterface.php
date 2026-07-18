<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Contract;

interface MinifierInterface {

	public function minify( string $content ): string;
}
