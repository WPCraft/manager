<?php
/**
 * The Main loading file.
 *
 * Here we will load all neccessary libraries, functions, variables and dependencies.
 *
 * @package Manager
 * @since 1.0.0
 */

/**
 * Loading Variables
 */
# Loading Variables...
require_once( PATH . INC . "/variables.php");
# Variables Loaded

/**
 * Loading Dependencies
 */
# Loading Dependencies...
require_once( PATH . INC . "/version.php");
# Dependencies loaded

/**
 * Loading Functions
 */
# Loading Functions...
require_once( PATH . INC . "/functions.php");
# Functions Loaded

/**
 * Load the libraries
 */
# Loading Libraries...
// Database
require_once( PATH . INC . "/libs/database/class.db.php");

// Data
require_once( PATH . INC . "/libs/data/class.data-error.php");
if (version_compare(phpversion(), '5.5', '<')) { require_once( PATH . INC . "/libs/data/class.data-password.php"); }
require_once( PATH . INC . "/libs/data/class.data-hashing.php");
require_once( PATH . INC . "/libs/data/class.data-output.php");

// Route
require_once( PATH . INC ."/libs/route/class.route.php");

// Extension
require_once( PATH . INC ."/libs/extension/class.extension.php");

// user
require_once( PATH . INC ."/libs/user/class.user.php");

// Api
require_once( PATH . INC ."/libs/api/class.api.php");
# Libraries Loaded.

# Initialize the Classes
$db             = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_COLLATE, DB_CHARSET, $table_prefix);
$data_error     = new DATA_ERROR();
$data_hashing   = new DATA_HASHING();
$data_output    = new DATA_OUTPUT();
$user           = new USER();
# Classes initialized
