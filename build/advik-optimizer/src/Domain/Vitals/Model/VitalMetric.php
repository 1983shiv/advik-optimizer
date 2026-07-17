<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Vitals\Model;

class VitalMetric {

	private ?int $id;
	private string $urlHash;
	private string $url;
	private string $metricType;
	private float $value;
	private string $device;
	private string $source;
	private string $recordedAt;

	public function __construct(
		?int $id,
		string $urlHash,
		string $url,
		string $metricType,
		float $value,
		string $device,
		string $source,
		string $recordedAt
	) {
		$this->id          = $id;
		$this->urlHash     = $urlHash;
		$this->url         = $url;
		$this->metricType  = $metricType;
		$this->value       = $value;
		$this->device      = $device;
		$this->source      = $source;
		$this->recordedAt  = $recordedAt;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getUrlHash(): string {
		return $this->urlHash;
	}

	public function getUrl(): string {
		return $this->url;
	}

	public function getMetricType(): string {
		return $this->metricType;
	}

	public function getValue(): float {
		return $this->value;
	}

	public function getDevice(): string {
		return $this->device;
	}

	public function getSource(): string {
		return $this->source;
	}

	public function getRecordedAt(): string {
		return $this->recordedAt;
	}

	public function toArray(): array {
		return [
			'id'          => $this->id,
			'url_hash'    => $this->urlHash,
			'url'         => $this->url,
			'metric_type' => $this->metricType,
			'value'       => $this->value,
			'device'      => $this->device,
			'source'      => $this->source,
			'recorded_at' => $this->recordedAt,
		];
	}

	public static function fromArray( array $data ): self {
		return new self(
			$data['id'] ?? null,
			$data['url_hash'] ?? '',
			$data['url'] ?? '',
			$data['metric_type'] ?? '',
			(float) ( $data['value'] ?? 0 ),
			$data['device'] ?? 'desktop',
			$data['source'] ?? 'lab',
			$data['recorded_at'] ?? current_time( 'mysql' )
		);
	}
}
