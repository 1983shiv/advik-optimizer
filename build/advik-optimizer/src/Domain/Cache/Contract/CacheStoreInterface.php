<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Cache\Contract;

interface CacheStoreInterface {

	public function get( string $key ): ?string;
	public function put( string $key, string $data, int $ttl ): bool;
	public function delete( string $key ): bool;
	public function flush(): bool;
}
