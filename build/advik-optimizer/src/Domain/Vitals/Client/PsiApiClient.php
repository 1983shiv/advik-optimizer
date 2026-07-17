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
		$catParams = [ 'PERFORMANCE', 'SEO', 'ACCESSIBILITY', 'BEST_PRACTICES' ];

		$requestUrl = add_query_arg(
			[
				'url'      => $url,
				'key'      => $this->apiKey,
				'strategy' => $strategy,
			],
			$this->endpoint
		);

		$requestUrl .= '&' . implode( '&', array_map( fn( $c ) => 'category=' . $c, $catParams ) );

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
		$inpRaw = $audits['interaction-to-next-paint']['numericValue'] ?? $audits['inp']['numericValue'] ?? null;
		$inp  = null !== $inpRaw ? (float) $inpRaw : 0;
		$ttfb = isset( $audits['server-response-time']['numericValue'] ) ? (float) $audits['server-response-time']['numericValue'] / 1000 : 0;

		$lhc = $lighthouse['categories'] ?? [];
		$perf = isset( $lhc['performance']['score'] ) ? (int) round( $lhc['performance']['score'] * 100 ) : 0;
		$seo  = isset( $lhc['seo']['score'] ) ? (int) round( $lhc['seo']['score'] * 100 ) : 0;
		$a11y = isset( $lhc['accessibility']['score'] ) ? (int) round( $lhc['accessibility']['score'] * 100 ) : 0;
		$bp   = isset( $lhc['best-practices']['score'] ) ? (int) round( $lhc['best-practices']['score'] * 100 ) : 0;

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
