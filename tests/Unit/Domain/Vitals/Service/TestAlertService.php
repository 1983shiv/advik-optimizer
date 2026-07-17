<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Vitals\Service;

use AdvikLabs\Optimizer\Domain\Vitals\Service\AlertService;
use PHPUnit\Framework\TestCase;

class TestAlertService extends TestCase {

	public function testDoesNotAlertWhenAllMetricsBelowThreshold(): void {
		$settings = [
			'vitals_alert_lcp' => 2.5,
			'vitals_alert_cls' => 0.1,
			'vitals_alert_inp' => 200,
		];

		$service = $this->getMockBuilder( AlertService::class )
			->setConstructorArgs( [ $settings ] )
			->onlyMethods( [ 'sendAlert' ] )
			->getMock();

		$service->expects( $this->never() )->method( 'sendAlert' );

		$service->checkAndAlert(
			[
				'lcp'  => 1.5,
				'cls'  => 0.05,
				'inp'  => 100,
				'ttfb' => 300,
			],
			'mobile'
		);
	}

	public function testAlertsWhenLcpExceedsThreshold(): void {
		$settings = [
			'vitals_alert_lcp' => 2.5,
			'vitals_alert_cls' => 0.1,
			'vitals_alert_inp' => 200,
		];

		$service = $this->getMockBuilder( AlertService::class )
			->setConstructorArgs( [ $settings ] )
			->onlyMethods( [ 'sendAlert' ] )
			->getMock();

		$service->expects( $this->once() )->method( 'sendAlert' );

		$service->checkAndAlert(
			[
				'lcp'  => 4.2,
				'cls'  => 0.05,
				'inp'  => 100,
			],
			'mobile'
		);
	}

	public function testAlertsWhenClsExceedsThreshold(): void {
		$settings = [
			'vitals_alert_lcp' => 2.5,
			'vitals_alert_cls' => 0.1,
			'vitals_alert_inp' => 200,
		];

		$service = $this->getMockBuilder( AlertService::class )
			->setConstructorArgs( [ $settings ] )
			->onlyMethods( [ 'sendAlert' ] )
			->getMock();

		$service->expects( $this->once() )->method( 'sendAlert' );

		$service->checkAndAlert(
			[
				'lcp'  => 2.0,
				'cls'  => 0.35,
				'inp'  => 150,
			],
			'desktop'
		);
	}

	public function testAlertsOnMultipleViolations(): void {
		$settings = [
			'vitals_alert_lcp' => 2.5,
			'vitals_alert_cls' => 0.1,
			'vitals_alert_inp' => 200,
		];

		$service = $this->getMockBuilder( AlertService::class )
			->setConstructorArgs( [ $settings ] )
			->onlyMethods( [ 'sendAlert' ] )
			->getMock();

		$service->expects( $this->once() )->method( 'sendAlert' );

		$service->checkAndAlert(
			[
				'lcp'  => 5.0,
				'cls'  => 0.5,
				'inp'  => 400,
			],
			'mobile'
		);
	}

	public function testDoesNotAlertWhenNoThresholdConfigured(): void {
		$service = $this->getMockBuilder( AlertService::class )
			->setConstructorArgs( [ [] ] )
			->onlyMethods( [ 'sendAlert' ] )
			->getMock();

		$service->expects( $this->exactly( 3 ) )->method( 'sendAlert' );

		$service->checkAndAlert( [ 'lcp' => 5.0 ], 'mobile' );
		$service->checkAndAlert( [ 'cls' => 0.5 ], 'mobile' );
		$service->checkAndAlert( [ 'inp' => 500 ], 'mobile' );
	}

	public function testIgnoresUnknownMetricTypes(): void {
		$settings = [
			'vitals_alert_lcp' => 2.5,
		];

		$service = $this->getMockBuilder( AlertService::class )
			->setConstructorArgs( [ $settings ] )
			->onlyMethods( [ 'sendAlert' ] )
			->getMock();

		$service->expects( $this->never() )->method( 'sendAlert' );

		$service->checkAndAlert(
			[
				'unknown_metric' => 999,
				'ttfb'           => 999,
			],
			'mobile'
		);
	}
}
