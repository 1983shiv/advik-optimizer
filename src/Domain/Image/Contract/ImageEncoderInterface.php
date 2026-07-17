<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Image\Contract;

use AdvikLabs\Optimizer\Domain\Image\Model\EncodedImage;

interface ImageEncoderInterface {

	public function encode( string $path, string $format, int $quality ): EncodedImage;

	public function supportsFormat( string $format ): bool;
}
