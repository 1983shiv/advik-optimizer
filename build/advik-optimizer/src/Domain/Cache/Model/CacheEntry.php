<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Cache\Model;

class CacheEntry {

	private string $key;
	private string $html;
	private array $headers;
	private int $expiresAt;

	public function __construct( string $key, string $html, array $headers, int $ttl ) {
		$this->key       = $key;
		$this->html      = $html;
		$this->headers   = $headers;
		$this->expiresAt = time() + $ttl;
	}

	public function getKey(): string {
		return $this->key;
	}

	public function getHtml(): string {
		return $this->html;
	}

	public function getHeaders(): array {
		return $this->headers;
	}

	public function getExpiresAt(): int {
		return $this->expiresAt;
	}

	public function isExpired(): bool {
		return time() > $this->expiresAt;
	}

	public static function fromArray( array $data ): self {
		return new self(
			$data['key'],
			$data['html'],
			$data['headers'] ?? [],
			( $data['expires_at'] ?? 0 ) - time()
		);
	}

	public function toArray(): array {
		return [
			'key'        => $this->key,
			'html'       => $this->html,
			'headers'    => $this->headers,
			'expires_at' => $this->expiresAt,
		];
	}
}
