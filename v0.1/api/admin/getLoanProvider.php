<?php

require_once( '../../assets/initializer.php' );

$Loan = new Loan( $db );

#  Check for rge requests method
if ( $_SERVER[ 'REQUEST_METHOD' ] !== 'POST' ) {
    header( 'HTTP/1.1 405 Method Not Allowed' );
    header( 'Allow: POST' );
    exit();
}
#Your method should be here
$getAllLoanProvider = $Loan->getAllLoanProvider();
if ( $getAllLoanProvider ) {
   echo $Loan->outputData( true, 'Fetched Loan Providers', $getAllLoanProvider );
} else {

    $Loan->outputData( false,  "No record found", null );
}

unset( $Loan );
unset( $db );

