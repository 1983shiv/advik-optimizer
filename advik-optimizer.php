<?php
/**
 * Plugin Name:  Advik Optimizer
 * Plugin URI:   https://advik-labs.com/advik-optimizer
 * Description:  WordPress performance and SEO plugin — cache, image optimization, minification, CWV monitoring, SEO, and database cleanup.
 * Version:      0.1.0
 * Requires PHP: 8.0
 * Requires at least: 6.0
 * Author:       AdvikLabs
 * Author URI:   https://advik-labs.com
 * License:      GPL v2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  advik-optimizer
 * Domain Path:  /languages
 *
 * @package AdvikLabs\Optimizer
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ADVIK_OPTIMIZER_VERSION', '0.1.0' );
define( 'ADVIK_OPTIMIZER_FILE', __FILE__ );
define( 'ADVIK_OPTIMIZER_DIR', plugin_dir_path( __FILE__ ) );
define( 'ADVIK_OPTIMIZER_URL', plugin_dir_url( __FILE__ ) );

$autoload = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $autoload ) ) {
	require $autoload;
}

register_activation_hook( __FILE__, array( 'AdvikLabs\Optimizer\Install\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'AdvikLabs\Optimizer\Install\Deactivator', 'deactivate' ) );

if ( ! class_exists( 'AdvikLabs\\Optimizer\\Plugin' ) ) {
	return;
}

add_action(
	'plugins_loaded',
	function () {
		$plugin = new AdvikLabs\Optimizer\Plugin();
		$plugin->boot();
	}
);
