<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Vitals\Service;

use AdvikLabs\Optimizer\Domain\Vitals\Contract\LighthouseClientInterface;
use AdvikLabs\Optimizer\Domain\Vitals\Model\LabResult;

class VitalsScanService {

	private LighthouseClientInterface $client;
	private VitalsIngestService $ingestService;

	public function __construct(
		LighthouseClientInterface $client,
		VitalsIngestService $ingestService
	) {
		$this->client        = $client;
		$this->ingestService = $ingestService;
	}

	public function scanUrl( string $url, string $device = 'mobile' ): LabResult {
		$result = $this->client->scan( $url, $device );
		$this->ingestService->ingestLabData( $result );
		return $result;
	}

	public function scanHomepage( string $device = 'mobile' ): LabResult {
		$homeUrl = home_url();
		return $this->scanUrl( $homeUrl, $device );
	}

	public function scanBoth( string $url ): array {
		$mobile  = $this->scanUrl( $url, 'mobile' );
		$desktop = $this->scanUrl( $url, 'desktop' );

		return [
			'mobile' => $mobile,
			'desktop' => $desktop,
		];
	}
}
