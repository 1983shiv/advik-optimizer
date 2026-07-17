<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Image\Encoder;

use AdvikLabs\Optimizer\Domain\Image\Contract\ImageEncoderInterface;
use RuntimeException;

class EncoderFactory {

	public function create(): ImageEncoderInterface {
		if ( function_exists( 'imagewebp' ) ) {
			return new GdEncoder();
		}

		throw new RuntimeException( 'No supported image encoder found on this server.' );
	}
}
