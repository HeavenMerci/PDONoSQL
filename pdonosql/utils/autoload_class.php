<?php

spl_autoload_register( function( $classname ){
    include_once(
        dirname(__FILE__).'/../../'.str_replace('\\', '/', $classname).'.class.php');
} );
