<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Vitals\Model;

class LabAudit {

	private ?int $id;
	private string $auditId;
	private string $title;
	private string $description;
	private ?float $score;
	private string $severity;
	private string $category;
	private ?int $estimatedSavingsMs;
	private string $device;
	private string $recordedAt;

	public function __construct(
		?int $id,
		string $auditId,
		string $title,
		string $description,
		?float $score,
		string $severity,
		string $category,
		?int $estimatedSavingsMs,
		string $device = 'mobile',
		?string $recordedAt = null
	) {
		$this->id                = $id;
		$this->auditId           = $auditId;
		$this->title             = $title;
		$this->description       = $description;
		$this->score             = $score;
		$this->severity          = $severity;
		$this->category          = $category;
		$this->estimatedSavingsMs = $estimatedSavingsMs;
		$this->device            = $device;
		$this->recordedAt        = $recordedAt ?? current_time( 'mysql' );
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getAuditId(): string {
		return $this->auditId;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function getScore(): ?float {
		return $this->score;
	}

	public function getSeverity(): string {
		return $this->severity;
	}

	public function getCategory(): string {
		return $this->category;
	}

	public function getEstimatedSavingsMs(): ?int {
		return $this->estimatedSavingsMs;
	}

	public function getEstimatedSavingsFormatted(): string {
		if ( null === $this->estimatedSavingsMs || $this->estimatedSavingsMs <= 0 ) {
			return '';
		}
		if ( $this->estimatedSavingsMs >= 1000 ) {
			return sprintf( '%.1f s', $this->estimatedSavingsMs / 1000 );
		}
		return sprintf( '%d ms', $this->estimatedSavingsMs );
	}

	public function getDevice(): string {
		return $this->device;
	}

	public function getRecordedAt(): string {
		return $this->recordedAt;
	}

	public function toArray(): array {
		return [
			'id'                  => $this->id,
			'audit_id'            => $this->auditId,
			'title'               => $this->title,
			'description'         => $this->description,
			'score'               => $this->score,
			'severity'            => $this->severity,
			'category'            => $this->category,
			'estimated_savings_ms' => $this->estimatedSavingsMs,
			'estimated_savings'   => $this->getEstimatedSavingsFormatted(),
			'device'              => $this->device,
			'recorded_at'         => $this->recordedAt,
		];
	}

	public static function fromArray( array $data ): self {
		return new self(
			isset( $data['id'] ) ? (int) $data['id'] : null,
			$data['audit_id'] ?? '',
			$data['title'] ?? '',
			$data['description'] ?? '',
			isset( $data['score'] ) ? (float) $data['score'] : null,
			$data['severity'] ?? 'info',
			$data['category'] ?? 'performance',
			isset( $data['estimated_savings_ms'] ) ? (int) $data['estimated_savings_ms'] : null,
			$data['device'] ?? 'mobile',
			$data['recorded_at'] ?? null
		);
	}
}
