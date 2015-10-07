<?php
/**
 * Manager Content Management System
 *
 * The main configuration file.
 *
 * @package Manager
 * @version 1.0.0
 */

/**
 * Database Credentials
 */
define('DB_NAME', 'pagifyDBsudxd');
define('DB_USER', 'pagifyDBsudxd');
define('DB_PASSWORD', 'czGXgXoxjr');
define('DB_HOST', '127.0.0.1');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');


/**
 * Uniqe Secret Keys & Salt
 */
define('SALT',       '>BWp[9S[8[C2m+;LixKdr,3n^2Mf2|v}Fz}FcrP#9O9S#5SIby<Au<Av>cv>y{EV');

/**
 * Database table prefix
 *
 * This is needed when running multiple MANAGER in a same database.
 */
$table_prefix  = 'mngr_';

/**
 * Define the PATH
 */
if ( !defined('PATH') )
    define('PATH', dirname(__FILE__) . '/');

/**
 * Load the settings
 */
require_once(PATH . 'settings.php');
