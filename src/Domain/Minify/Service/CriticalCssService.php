<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Minify\Service;

use AdvikLabs\Optimizer\Domain\Minify\Contract\RendererInterface;
use AdvikLabs\Optimizer\Domain\Minify\Model\CriticalCssRule;
use AdvikLabs\Optimizer\Domain\Minify\Repository\CriticalCssRepository;

class CriticalCssService {

	private CriticalCssRepository $repository;
	private ?RendererInterface $renderer;

	public function __construct( CriticalCssRepository $repository, ?RendererInterface $renderer = null ) {
		$this->repository = $repository;
		$this->renderer   = $renderer;
	}

	public function scan( array $urls ): int {
		if ( null === $this->renderer ) {
			return 0;
		}

		$count = 0;

		foreach ( $urls as $template => $url ) {
			$html = $this->renderer->render( $url );

			if ( '' === $html ) {
				continue;
			}

			$css = $this->extractCriticalCss( $html );

			if ( '' === $css ) {
				continue;
			}

			$rule = new CriticalCssRule( null, $template, $css, current_time( 'mysql' ) );
			$this->repository->save( $rule );
			++$count;
		}

		return $count;
	}

	public function getRule( string $template ): ?CriticalCssRule {
		return $this->repository->findByTemplate( $template );
	}

	public function getLastScanTime(): ?string {
		return $this->repository->getLastScanTime();
	}

	public function getRuleCount(): int {
		return $this->repository->getRuleCount();
	}

	public function getTotalCssLength(): int {
		return $this->repository->getTotalCssLength();
	}

	private function extractCriticalCss( string $html ): string {
		$css = '';

		if ( preg_match( '/<style[^>]*>([\s\S]*?)<\/style>/i', $html, $matches ) ) {
			$css = $matches[1];
		}

		$links = [];
		if ( preg_match_all( '/<link[^>]*rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+)["\'][^>]*\/?>/i', $html, $linkMatches ) ) {
			foreach ( $linkMatches[1] as $href ) {
				$links[] = $href;
			}
		}

		return trim( $css );
	}
}
