<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Vitals\Model;

class LabResult {

	private string $url;
	private float $lcp;
	private float $cls;
	private float $inp;
	private float $ttfb;
	private int $performanceScore;
	private int $seoScore;
	private int $accessibilityScore;
	private int $bestPracticesScore;
	private string $device;
	private string $recordedAt;

	public function __construct(
		string $url,
		float $lcp,
		float $cls,
		float $inp,
		float $ttfb,
		int $performanceScore,
		int $seoScore,
		int $accessibilityScore,
		int $bestPracticesScore,
		string $device = 'mobile',
		?string $recordedAt = null
	) {
		$this->url                 = $url;
		$this->lcp                 = $lcp;
		$this->cls                 = $cls;
		$this->inp                 = $inp;
		$this->ttfb                = $ttfb;
		$this->performanceScore    = $performanceScore;
		$this->seoScore            = $seoScore;
		$this->accessibilityScore  = $accessibilityScore;
		$this->bestPracticesScore  = $bestPracticesScore;
		$this->device              = $device;
		$this->recordedAt          = $recordedAt ?? current_time( 'mysql' );
	}

	public function getUrl(): string {
		return $this->url;
	}

	public function getLcp(): float {
		return $this->lcp;
	}

	public function getCls(): float {
		return $this->cls;
	}

	public function getInp(): float {
		return $this->inp;
	}

	public function getTtfb(): float {
		return $this->ttfb;
	}

	public function getPerformanceScore(): int {
		return $this->performanceScore;
	}

	public function getSeoScore(): int {
		return $this->seoScore;
	}

	public function getAccessibilityScore(): int {
		return $this->accessibilityScore;
	}

	public function getBestPracticesScore(): int {
		return $this->bestPracticesScore;
	}

	public function getDevice(): string {
		return $this->device;
	}

	public function getRecordedAt(): string {
		return $this->recordedAt;
	}

	public function isEmpty(): bool {
		return 0.0 === $this->lcp
			&& 0.0 === $this->cls
			&& 0.0 === $this->inp
			&& 0.0 === $this->ttfb
			&& 0 === $this->performanceScore
			&& 0 === $this->seoScore
			&& 0 === $this->accessibilityScore
			&& 0 === $this->bestPracticesScore;
	}

	public function toMetrics(): array {
		$hash = md5( $this->url );

		return [
			new VitalMetric( null, $hash, $this->url, 'lcp', $this->lcp, $this->device, 'lab', $this->recordedAt ),
			new VitalMetric( null, $hash, $this->url, 'cls', $this->cls, $this->device, 'lab', $this->recordedAt ),
			new VitalMetric( null, $hash, $this->url, 'inp', $this->inp, $this->device, 'lab', $this->recordedAt ),
			new VitalMetric( null, $hash, $this->url, 'ttfb', $this->ttfb, $this->device, 'lab', $this->recordedAt ),
			new VitalMetric( null, $hash, $this->url, 'performance', (float) $this->performanceScore, $this->device, 'lab', $this->recordedAt ),
			new VitalMetric( null, $hash, $this->url, 'seo', (float) $this->seoScore, $this->device, 'lab', $this->recordedAt ),
			new VitalMetric( null, $hash, $this->url, 'accessibility', (float) $this->accessibilityScore, $this->device, 'lab', $this->recordedAt ),
			new VitalMetric( null, $hash, $this->url, 'best_practices', (float) $this->bestPracticesScore, $this->device, 'lab', $this->recordedAt ),
		];
	}

	public static function fromArray( array $data ): self {
		return new self(
			$data['url'] ?? '',
			(float) ( $data['lcp'] ?? 0 ),
			(float) ( $data['cls'] ?? 0 ),
			(float) ( $data['inp'] ?? 0 ),
			(float) ( $data['ttfb'] ?? 0 ),
			(int) ( $data['performance_score'] ?? 0 ),
			(int) ( $data['seo_score'] ?? 0 ),
			(int) ( $data['accessibility_score'] ?? 0 ),
			(int) ( $data['best_practices_score'] ?? 0 ),
			$data['device'] ?? 'mobile',
			$data['recorded_at'] ?? null
		);
	}
}
