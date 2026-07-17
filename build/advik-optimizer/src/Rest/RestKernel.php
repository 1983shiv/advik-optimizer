<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Rest;

use AdvikLabs\Optimizer\Rest\Controller\AuditController;
use AdvikLabs\Optimizer\Rest\Controller\CacheController;
use AdvikLabs\Optimizer\Rest\Controller\ScoreController;
use AdvikLabs\Optimizer\Rest\Controller\VitalsController;

class RestKernel {

	private AuditController $auditController;
	private CacheController $cacheController;
	private ScoreController $scoreController;
	private VitalsController $vitalsController;

	public function __construct(
		AuditController $auditController,
		CacheController $cacheController,
		ScoreController $scoreController,
		VitalsController $vitalsController
	) {
		$this->auditController  = $auditController;
		$this->cacheController  = $cacheController;
		$this->scoreController  = $scoreController;
		$this->vitalsController = $vitalsController;
	}

	public function register(): void {
		add_action( 'rest_api_init', [ $this, 'registerRoutes' ] );
	}

	public function registerRoutes(): void {
		register_rest_route(
			'advik-optimizer/v1',
			'/cache/purge',
			[
				'methods'             => 'POST',
				'callback'            => [ $this->cacheController, 'purge' ],
				'permission_callback' => [ $this->cacheController, 'permissionCheck' ],
				'args'                => [
					'scope' => [
						'type' => 'string',
						'enum' => [ 'all', 'url' ],
					],
					'url'   => [ 'type' => 'string' ],
				],
			]
		);

		register_rest_route(
			'advik-optimizer/v1',
			'/cache/warm',
			[
				'methods'             => 'POST',
				'callback'            => [ $this->cacheController, 'warm' ],
				'permission_callback' => [ $this->cacheController, 'permissionCheck' ],
				'args'                => [
					'sitemap' => [ 'type' => 'string' ],
					'urls'    => [ 'type' => 'array' ],
				],
			]
		);

		register_rest_route(
			'advik-optimizer/v1',
			'/cache/stats',
			[
				'methods'             => 'GET',
				'callback'            => [ $this->cacheController, 'stats' ],
				'permission_callback' => [ $this->cacheController, 'permissionCheck' ],
			]
		);

		register_rest_route(
			'advik-optimizer/v1',
			'/audits',
			[
				'methods'             => 'GET',
				'callback'            => [ $this->auditController, 'index' ],
				'permission_callback' => [ $this->auditController, 'permissionCheck' ],
				'args'                => [
					'device' => [
						'type' => 'string',
						'enum' => [ 'mobile', 'desktop' ],
					],
				],
			]
		);

		register_rest_route(
			'advik-optimizer/v1',
			'/scores',
			[
				'methods'             => 'GET',
				'callback'            => [ $this->scoreController, 'index' ],
				'permission_callback' => [ $this->scoreController, 'permissionCheck' ],
				'args'                => [
					'device' => [
						'type' => 'string',
						'enum' => [ 'mobile', 'desktop' ],
					],
				],
			]
		);

		register_rest_route(
			'advik-optimizer/v1',
			'/vitals/trend',
			[
				'methods'             => 'GET',
				'callback'            => [ $this->vitalsController, 'trend' ],
				'permission_callback' => [ $this->vitalsController, 'permissionCheck' ],
				'args'                => [
					'metric' => [
						'type' => 'string',
						'enum' => [ 'lcp', 'cls', 'inp', 'ttfb' ],
					],
					'range'  => [
						'type' => 'string',
						'enum' => [ '7d', '30d', '90d' ],
					],
					'device' => [
						'type' => 'string',
						'enum' => [ 'mobile', 'desktop' ],
					],
				],
			]
		);

		register_rest_route(
			'advik-optimizer/v1',
			'/vitals/ingest',
			[
				'methods'             => 'POST',
				'callback'            => [ $this->vitalsController, 'ingest' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'url'   => [
						'type' => 'string',
						'required' => true,
					],
					'lcp'   => [ 'type' => 'number' ],
					'cls'   => [ 'type' => 'number' ],
					'inp'   => [ 'type' => 'number' ],
					'ttfb'  => [ 'type' => 'number' ],
					'device' => [
						'type' => 'string',
						'enum' => [ 'mobile', 'desktop' ],
					],
				],
			]
		);
	}
}
