<?php

declare(strict_types=1);

$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists($autoload)) {
    require $autoload;
}

$mockDir = dirname(__DIR__) . '/tests/mock';

require $mockDir . '/class-wpdb.php';
require $mockDir . '/class-wp-error.php';
require $mockDir . '/functions.php';

define( 'OBJECT', 'OBJECT' );
define( 'OBJECT_K', 'OBJECT_K' );
define( 'ARRAY_A', 'ARRAY_A' );
define( 'ARRAY_N', 'ARRAY_N' );

define( 'ABSPATH', $mockDir . '/' );

if (! defined('WP_UNINSTALL_PLUGIN')) {
    define('WP_UNINSTALL_PLUGIN', true);
}
