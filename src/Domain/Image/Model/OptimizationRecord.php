<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Image\Model;

class OptimizationRecord {

	private ?int $id;
	private int $attachmentId;
	private int $originalSize;
	private ?int $optimizedSize;
	private ?string $format;
	private string $status;
	private string $updatedAt;

	public function __construct(
		?int $id,
		int $attachmentId,
		int $originalSize,
		?int $optimizedSize,
		?string $format,
		string $status,
		string $updatedAt
	) {
		$this->id            = $id;
		$this->attachmentId  = $attachmentId;
		$this->originalSize  = $originalSize;
		$this->optimizedSize = $optimizedSize;
		$this->format        = $format;
		$this->status        = $status;
		$this->updatedAt     = $updatedAt;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getAttachmentId(): int {
		return $this->attachmentId;
	}

	public function getOriginalSize(): int {
		return $this->originalSize;
	}

	public function getOptimizedSize(): ?int {
		return $this->optimizedSize;
	}

	public function getFormat(): ?string {
		return $this->format;
	}

	public function getStatus(): string {
		return $this->status;
	}

	public function getUpdatedAt(): string {
		return $this->updatedAt;
	}
}
