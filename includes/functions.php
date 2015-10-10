<?php
/**
 * Functions File
 *
 * @package Manager
 * @since 1.0.0
 */

/**
 * Escape Html Chars
 */
function _esc($string){
    return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
}

/**
 * Sanitize The Data for injection preventation
 */
function _e($string){
    return mysql_real_escape_string($string);
}

/**
 * Gettext
 */
function __($string){
    return $string;
}