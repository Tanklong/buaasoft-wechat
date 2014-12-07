<?php
/**
 * The base configurations of the system.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

// MySQL database name
define('DB_NAME', 'weixin');

// MySQL database username
define('DB_USER', 'root');

// MySQL database password
define('DB_PASSWORD', 'root');

// MySQL hostname
define('DB_HOST', 'localhost');

// MySQL database handle charset
define('DB_CHARSET', 'utf8');

// Authentication unique salts
define('LOGIN_SALT', 'unique string here');
define('MESSAGE_SALT', 'unique string here');
define('AJAX_SALT', 'unique string here');

// Website root URL
define('ROOT_URL', 'http://localhost/');

// Define ABSPATH as this file's directory

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Enable debug on test environments
define('WX_DEBUG', 'WX_DEBUG');

// Sets up vars and included files
require_once ABSPATH . 'includes/load.php';