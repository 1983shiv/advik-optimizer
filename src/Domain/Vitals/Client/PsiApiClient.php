<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Vitals\Client;

use AdvikLabs\Optimizer\Domain\Vitals\Contract\LighthouseClientInterface;
use AdvikLabs\Optimizer\Domain\Vitals\Model\LabResult;

class PsiApiClient implements LighthouseClientInterface {

	private string $apiKey;
	private string $endpoint;

	public function __construct( string $apiKey ) {
		$this->apiKey   = $apiKey;
		$this->endpoint = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';
	}

	public function scan( string $url, string $device = 'mobile' ): LabResult {
		if ( '' === $this->apiKey ) {
			return $this->emptyResult( $url, $device );
		}

		$strategy = 'mobile' === $device ? 'mobile' : 'desktop';
		$categories = [ 'performance', 'seo', 'accessibility', 'best-practices' ];

		$requestUrl = add_query_arg(
			[
				'url'      => $url,
				'key'      => $this->apiKey,
				'strategy' => $strategy,
			],
			$this->endpoint
		);

		$requestUrl .= '&category=' . implode( '&category=', $categories );

		$response = wp_remote_get( $requestUrl, [ 'timeout' => 60 ] );

		if ( is_wp_error( $response ) ) {
			return $this->emptyResult( $url, $device );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			return $this->emptyResult( $url, $device );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) || ! isset( $data['lighthouseResult'] ) ) {
			return $this->emptyResult( $url, $device );
		}

		$lighthouse = $data['lighthouseResult'];
		$audits     = $lighthouse['audits'] ?? [];

		$lcp  = isset( $audits['largest-contentful-paint']['numericValue'] ) ? (float) $audits['largest-contentful-paint']['numericValue'] / 1000 : 0;
		$cls  = isset( $audits['cumulative-layout-shift']['numericValue'] ) ? (float) $audits['cumulative-layout-shift']['numericValue'] : 0;
		$inp  = isset( $audits['interaction-to-next-paint']['numericValue'] ) ? (float) $audits['interaction-to-next-paint']['numericValue'] : 0;
		$ttfb = isset( $audits['server-response-time']['numericValue'] ) ? (float) $audits['server-response-time']['numericValue'] / 1000 : 0;

		$categories = $lighthouse['categories'] ?? [];
		$perf       = isset( $categories['performance']['score'] ) ? (int) round( $categories['performance']['score'] * 100 ) : 0;
		$seo        = isset( $categories['seo']['score'] ) ? (int) round( $categories['seo']['score'] * 100 ) : 0;
		$a11y       = isset( $categories['accessibility']['score'] ) ? (int) round( $categories['accessibility']['score'] * 100 ) : 0;
		$bp         = isset( $categories['best-practices']['score'] ) ? (int) round( $categories['best-practices']['score'] * 100 ) : 0;

		return new LabResult(
			$url,
			$lcp,
			$cls,
			$inp,
			$ttfb,
			$perf,
			$seo,
			$a11y,
			$bp,
			$device
		);
	}

	private function emptyResult( string $url, string $device ): LabResult {
		return new LabResult( $url, 0, 0, 0, 0, 0, 0, 0, 0, $device );
	}
}
