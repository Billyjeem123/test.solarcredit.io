<?php
require_once( '../../assets/initializer.php' );

$Load = new Load( $db );

# Check for the request method
if ( $_SERVER[ 'REQUEST_METHOD' ] !== 'POST' ) {
    header( 'HTTP/1.1 405 Method Not Allowed' );
    header( 'Allow: POST' );
    exit();
}

# Check for required parameters
$validKeys = [ 'applianace' ];
$invalidKeys = array_diff( array_keys( $_FILES ), $validKeys );
if ( !empty( $invalidKeys ) ) {
    foreach ( $invalidKeys as $key ) {
        $errors[] = "$key is not a valid input field";
    }

    if ( !empty( $errors ) ) {
        $Load->respondUnprocessableEntity( $errors );
        return;
    }
}

# Check for required fields
if ( empty( $_FILES['applianace']['name'] ) ) {
    $errors[] = 'file is required';
}
if ( !empty( $errors ) ) {
    $Load->respondUnprocessableEntity( $errors );
    return;
}

# Your method should be here
$uploadImageToServer = $Load->saveLoadItems( $_FILES[ 'applianace' ] );

unset( $Load );
unset( $db );
