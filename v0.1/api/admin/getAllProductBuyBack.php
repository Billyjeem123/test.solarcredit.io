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
$getAllBuyBackProduct = $product->getBuyBacks();

if ( $getAllBuyBackProduct ) {
        http_response_code(200);
    $product->outputData( true, 'Fetched Prodcuts', $getAllBuyBackProduct );
} else {

    $product->outputData( false,  "No record found.", null );
}

unset( $category );
unset( $db );

