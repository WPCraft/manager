<?php
/**
 * Define the inlcludes directory
 */


if(!defined('PATH')){
    define('PATH', dirname(__FILE__) . '/');
}
if(!defined('INC')){
    define( 'INC', 'includes' );
}

# Loading MANAGER...
require( PATH . INC . '/load.php' );
# MANAGER Loaded
