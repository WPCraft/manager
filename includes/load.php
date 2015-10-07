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
require_once( PATH . INC . "/libs/database/class.db-connect.php");
require_once( PATH . INC . "/libs/database/class.db-query.php");

// Data
require_once( PATH . INC . "/libs/data/class.data-error.php");
require_once( PATH . INC . "/libs/data/class.data-hashing.php");
require_once( PATH . INC . "/libs/data/class.data-output.php");

// Route
require_once( PATH . INC ."/libs/route/class.route.php");

// Extension
require_once( PATH . INC ."/libs/extension/class.extension.php");

// Authentication
require_once( PATH . INC ."/libs/authentication/class.authentication-login.php");
require_once( PATH . INC ."/libs/authentication/class.authentication-register.php");

// Api
require_once( PATH . INC ."/libs/api/class.api.php");
# Libraries Loaded.



