<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Cli\Command;

abstract class AbstractCommand {

	protected function success( string $message ): void {
		if ( class_exists( 'WP_CLI' ) ) {
			\WP_CLI::success( $message );
		}
	}

	protected function error( string $message ): void {
		if ( class_exists( 'WP_CLI' ) ) {
			\WP_CLI::error( $message );
		}
	}

	protected function line( string $message ): void {
		if ( class_exists( 'WP_CLI' ) ) {
			\WP_CLI::line( $message );
		}
	}
}
