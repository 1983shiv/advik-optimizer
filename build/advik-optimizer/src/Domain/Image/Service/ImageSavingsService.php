<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Image\Service;

use AdvikLabs\Optimizer\Domain\Image\Repository\ImageOptimizationRepository;

class ImageSavingsService {

	private ImageOptimizationRepository $repository;

	public function __construct( ImageOptimizationRepository $repository ) {
		$this->repository = $repository;
	}

	public function totalSavingsBytes(): int {
		return $this->repository->getTotalSavings();
	}

	public function totalSavingsFormatted(): string {
		$bytes = $this->totalSavingsBytes();

		if ( $bytes >= 1048576 ) {
			return number_format( $bytes / 1048576, 1 ) . ' MB';
		}

		if ( $bytes >= 1024 ) {
			return number_format( $bytes / 1024, 1 ) . ' KB';
		}

		return $bytes . ' B';
	}

	public function optimizedCount(): int {
		return $this->repository->getDoneCount();
	}

	public function summary(): array {
		return [
			'savings_bytes'  => $this->totalSavingsBytes(),
			'savings'        => $this->totalSavingsFormatted(),
			'count'          => $this->optimizedCount(),
		];
	}
}
