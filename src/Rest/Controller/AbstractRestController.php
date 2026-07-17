<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Rest\Controller;

use AdvikLabs\Optimizer\Rest\Http\RestResponder;

abstract class AbstractRestController {

	use RestResponder;

	protected function permissionCheck(): bool {
		return current_user_can( 'manage_advik_optimizer' );
	}
}
