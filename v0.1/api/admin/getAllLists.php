<?php

require_once( '../../assets/initializer.php' );

$NewsLetter = new NewsLetter( $db );

#  Check for rge requests method
if ( $_SERVER[ 'REQUEST_METHOD' ] !== 'GET' ) {
    header( 'HTTP/1.1 405 Method Not Allowed' );
    header( 'Allow: POST' );
    exit();
}
#Your method should be here
$AllLists = $NewsLetter->getAllLists();


unset( $NewsLetter );
unset( $db );

