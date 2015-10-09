<?php
/**
 * Manager Content Management System
 *
 * The blog header file. Here we are loading the manager cms.
 *
 * @package Manager
 * @version 1.0.0
 */

if ( !isset($header_sent) ) {

    $header_sent = true;

    require_once( dirname(__FILE__) . '/load.php' );

    // template loaded function will be called here.
    require_once( dirname(__FILE__) . '/includes/template-loader.php');

}
