<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Model;

class CriticalCssRule {

	private ?int $id;
	private string $template;
	private string $css;
	private string $createdAt;

	public function __construct( ?int $id, string $template, string $css, string $createdAt ) {
		$this->id        = $id;
		$this->template  = $template;
		$this->css       = $css;
		$this->createdAt = $createdAt;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getTemplate(): string {
		return $this->template;
	}

	public function getCss(): string {
		return $this->css;
	}

	public function getCreatedAt(): string {
		return $this->createdAt;
	}
}
