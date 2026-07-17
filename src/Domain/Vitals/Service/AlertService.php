<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Vitals\Service;

class AlertService {

	private array $settings;

	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	public function checkAndAlert( array $metrics, string $device ): void {
		$thresholds = [
			'lcp'  => isset( $this->settings['vitals_alert_lcp'] ) ? (float) $this->settings['vitals_alert_lcp'] : 2.5,
			'cls'  => isset( $this->settings['vitals_alert_cls'] ) ? (float) $this->settings['vitals_alert_cls'] : 0.1,
			'inp'  => isset( $this->settings['vitals_alert_inp'] ) ? (int) $this->settings['vitals_alert_inp'] : 200,
		];

		$violations = [];
		foreach ( $thresholds as $metric => $threshold ) {
			if ( isset( $metrics[ $metric ] ) && $metrics[ $metric ] > $threshold ) {
				$violations[] = [
					'metric'    => strtoupper( $metric ),
					'value'     => $metrics[ $metric ],
					'threshold' => $threshold,
					'unit'      => 'cls' === $metric ? '' : ( 'inp' === $metric ? 'ms' : 's' ),
				];
			}
		}

		if ( empty( $violations ) ) {
			return;
		}

		$this->sendAlert( $violations, $device );
	}

	protected function sendAlert( array $violations, string $device ): void {
		$subject = sprintf(
			/* translators: %s: site name */
			esc_html__( '[Advik Optimizer] Performance alert for %s', 'advik-optimizer' ),
			get_bloginfo( 'name' )
		);

		$lines   = [];
		$lines[] = __( 'The following Core Web Vitals metrics have exceeded their configured thresholds:', 'advik-optimizer' );
		/* translators: %s: device type (mobile/desktop) */
		$lines[] = sprintf( __( 'Device: %s', 'advik-optimizer' ), $device );
		$lines[] = '';

		foreach ( $violations as $v ) {
			$value   = 'cls' === strtolower( $v['metric'] ) ? number_format( $v['value'], 3 ) : ( 'inp' === strtolower( $v['metric'] ) ? round( $v['value'] ) : round( $v['value'], 1 ) );
			$unit    = $v['unit'] ? ' ' . $v['unit'] : '';
			$lines[] = sprintf(
				'%s: %s%s (threshold: %s%s)',
				$v['metric'],
				$value,
				$unit,
				$v['threshold'],
				$unit
			);
		}

		$lines[] = '';
		$lines[] = __( 'Please check your site performance on the Advik Optimizer dashboard.', 'advik-optimizer' );
		$message = implode( "\n", $lines );

		$emailEnabled = ! empty( $this->settings['vitals_alert_email'] );
		$webhookEnabled = ! empty( $this->settings['vitals_alert_webhook'] );

		if ( $emailEnabled ) {
			$to = ! empty( $this->settings['vitals_alert_email_address'] )
				? $this->settings['vitals_alert_email_address']
				: get_option( 'admin_email' );
			wp_mail( $to, $subject, $message );
		}

		if ( $webhookEnabled && ! empty( $this->settings['vitals_webhook_url'] ) ) {
			$body = wp_json_encode(
				[
					'subject'    => $subject,
					'message'    => $message,
					'violations' => $violations,
					'device'     => $device,
					'site_url'   => get_site_url(),
					'timestamp'  => current_time( 'mysql' ),
				]
			);
			wp_remote_post(
				$this->settings['vitals_webhook_url'],
				[
					'body'    => $body,
					'headers' => [ 'Content-Type' => 'application/json' ],
					'timeout' => 15,
				]
			);
		}
	}
}
