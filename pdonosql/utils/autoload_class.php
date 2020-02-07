<?php

/**
 * includes automatically the right file for the class used \
 * the pattern for the class filename is as 'ClassName.class.php' \
 * the file path must be the same as its namespace ('/' replacing '\')
 *
 * NOTE: only include this file once for it to work. NOTHING MORE TO DO
 */
spl_autoload_register( function( $classname ){
    include_once(
        dirname(__FILE__).'/../../'.str_replace('\\', '/', $classname).'.class.php');
} );
