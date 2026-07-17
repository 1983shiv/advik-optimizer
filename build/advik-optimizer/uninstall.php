<?php
/**
 * Uninstall Advik Optimizer.
 *
 * @package AdvikLabs\Optimizer
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$autoload = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $autoload ) ) {
	require $autoload;
}

if ( class_exists( 'AdvikLabs\\Optimizer\\Install\\Uninstaller' ) ) {
	AdvikLabs\Optimizer\Install\Uninstaller::uninstall();
}
