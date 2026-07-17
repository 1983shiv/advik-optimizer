<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Cache\Service;

use AdvikLabs\Optimizer\Domain\Cache\Contract\Warmable;
use AdvikLabs\Optimizer\Domain\Cache\Repository\CacheLogRepository;

class CacheWarmService implements Warmable {

	private CacheManager $manager;
	private CacheLogRepository $logRepository;

	public function __construct( CacheManager $manager, CacheLogRepository $logRepository ) {
		$this->manager       = $manager;
		$this->logRepository = $logRepository;
	}

	public function warm( array $urls ): void {
		foreach ( $urls as $url ) {
			$response = wp_remote_get(
				$url,
				[
					'timeout'  => 5,
					'blocking' => true,
					'headers'  => [
						'X-Advik-Warm' => '1',
					],
				]
			);

			if ( is_wp_error( $response ) ) {
				$this->logRepository->log( 'warm', $url );
				continue;
			}

			$this->logRepository->log( 'warm', $url );
		}
	}

	public function warmFromSitemap( string $sitemapUrl ): void {
		$response = wp_remote_get( $sitemapUrl, [ 'timeout' => 10 ] );

		if ( is_wp_error( $response ) ) {
			return;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return;
		}

		$xml = simplexml_load_string( $body );

		if ( false === $xml ) {
			return;
		}

		$urls = [];

		foreach ( $xml->url as $urlNode ) {
			$loc = (string) $urlNode->loc;

			if ( ! empty( $loc ) ) {
				$urls[] = $loc;
			}
		}

		$this->warm( $urls );
	}
}
