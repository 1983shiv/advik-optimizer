<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Contract;

interface RendererInterface {

	public function render( string $url ): string;
}
