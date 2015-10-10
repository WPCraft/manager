<?php
/**
 * Class: DATA_OUTPUT
 *
 * This class will handle the output's
 *
 * @package Manager
 * @sub-package Manager-Library
 * @since 1.0.0
 */
if(!class_exists("DATA_OUTPUT")):
    class DATA_OUTPUT{

        /**
         * Initializer
         */
        function __construct(){

        }

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
    }
endif;

