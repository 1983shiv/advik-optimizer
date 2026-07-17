<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Vitals\Contract;

use AdvikLabs\Optimizer\Domain\Vitals\Model\LabResult;

interface LighthouseClientInterface {

	public function scan( string $url, string $device = 'mobile' ): LabResult;
}
