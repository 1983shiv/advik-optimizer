<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Hook\Listener;

use AdvikLabs\Optimizer\Domain\Cache\Service\CacheReadService;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheWriteService;
use AdvikLabs\Optimizer\Domain\Cache\Service\CacheEligibility;

class ServeCacheListener {

	private CacheReadService $readService;
	private CacheWriteService $writeService;
	private CacheEligibility $eligibility;
	private ?string $cacheKey = null;

	public function __construct(
		CacheReadService $readService,
		CacheWriteService $writeService,
		CacheEligibility $eligibility
	) {
		$this->readService  = $readService;
		$this->writeService = $writeService;
		$this->eligibility  = $eligibility;
	}

	public function serve(): void {
		if ( ! $this->eligibility->isEligible() ) {
			return;
		}

		$this->cacheKey = $this->buildCacheKey();
		$cached         = $this->readService->get( $this->cacheKey );

		if ( null !== $cached ) {
			header( 'X-Advik-Cache: HIT' );
			header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset', 'UTF-8' ) );
			echo $cached;
			exit;
		}

		header( 'X-Advik-Cache: MISS' );
		ob_start();
	}

	public function onShutdown(): void {
		if ( null === $this->cacheKey ) {
			return;
		}

		if ( ! $this->eligibility->isEligible() ) {
			return;
		}

		$html = ob_get_clean();

		if ( false === $html || empty( $html ) ) {
			return;
		}

		$httpCode = http_response_code();

		if ( 200 !== $httpCode ) {
			return;
		}

		$this->writeService->put( $this->cacheKey, $html );
	}

	private function buildCacheKey(): string {
		$scheme = is_ssl() ? 'https' : 'http';
		$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
		$uri    = $_SERVER['REQUEST_URI'] ?? '/';
		$device = wp_is_mobile() ? 'mobile' : 'desktop';

		return "{$scheme}://{$host}{$uri}|{$device}";
	}
}
