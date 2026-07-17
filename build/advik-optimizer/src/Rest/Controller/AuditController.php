<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Rest\Controller;

use AdvikLabs\Optimizer\Domain\Vitals\Repository\AuditRepository;

class AuditController extends AbstractRestController {

	private AuditRepository $repository;

	public function __construct( AuditRepository $repository ) {
		$this->repository = $repository;
	}

	public function index( \WP_REST_Request $request ): \WP_REST_Response {
		$device = sanitize_key( $request->get_param( 'device' ) ?? 'mobile' );
		$audits = $this->repository->getByDevice( $device );

		return $this->success(
			[
				'device' => $device,
				'audits' => array_map(
					fn( $a ) => $a->toArray(),
					$audits
				),
			]
		);
	}
}
