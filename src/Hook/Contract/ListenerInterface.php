<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Hook\Contract;

interface ListenerInterface {

	public function subscribedEvents(): array;
}
