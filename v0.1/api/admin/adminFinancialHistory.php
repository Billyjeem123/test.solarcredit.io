<?php

require_once( '../../assets/initializer.php' );

$product = new Product( $db );

#  Check for rge requests method
if ( $_SERVER[ 'REQUEST_METHOD' ] !== 'POST' ) {
    header( 'HTTP/1.1 405 Method Not Allowed' );
    header( 'Allow: POST' );
    exit();
}
#Your method should be here
$getAdminFinancialHistory = $product->getAdminFinancialHistory();
if ( $getAdminFinancialHistory ) {
   echo $product->outputData( true, 'Fetched History', $getAdminFinancialHistory );
} else {

    $product->outputData( false,  "No record found", null );
}

unset( $product );
unset( $db );

