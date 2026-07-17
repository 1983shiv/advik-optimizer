<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Hook\Listener;

use AdvikLabs\Optimizer\Domain\Cache\Service\CachePurgeService;

class ContentChangeListener {

	private CachePurgeService $purgeService;

	public function __construct( CachePurgeService $purgeService ) {
		$this->purgeService = $purgeService;
	}

	public function onPostChange( int $postId ): void {
		$post = get_post( $postId );

		if ( null === $post || 'publish' !== $post->post_status ) {
			return;
		}

		$this->purgeService->purgeForObject( $postId );
	}

	public function onCommentChange(): void {
		$this->purgeService->purgeAll();
	}

	public function onThemeOrPluginChange(): void {
		$this->purgeService->purgeAll();
	}

	public function onMenuChange(): void {
		$this->purgeService->purgeAll();
	}
}
