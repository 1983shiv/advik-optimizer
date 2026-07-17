<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Image\Model;

class EncodedImage {

	private string $path;
	private int $width;
	private int $height;
	private int $originalSize;
	private int $optimizedSize;
	private string $format;

	public function __construct(
		string $path,
		int $width,
		int $height,
		int $originalSize,
		int $optimizedSize,
		string $format
	) {
		$this->path          = $path;
		$this->width         = $width;
		$this->height        = $height;
		$this->originalSize  = $originalSize;
		$this->optimizedSize = $optimizedSize;
		$this->format        = $format;
	}

	public function getPath(): string {
		return $this->path;
	}

	public function getWidth(): int {
		return $this->width;
	}

	public function getHeight(): int {
		return $this->height;
	}

	public function getOriginalSize(): int {
		return $this->originalSize;
	}

	public function getOptimizedSize(): int {
		return $this->optimizedSize;
	}

	public function getFormat(): string {
		return $this->format;
	}

	public function getSavings(): int {
		return $this->originalSize - $this->optimizedSize;
	}

	public function getUrl(): string {
		$uploadDir = wp_upload_dir();
		$relPath   = str_replace( wp_normalize_path( $uploadDir['basedir'] ), '', wp_normalize_path( $this->path ) );
		return $uploadDir['baseurl'] . $relPath;
	}
}
