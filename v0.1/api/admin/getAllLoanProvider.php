<?php

require_once( '../../assets/initializer.php' );

$Loan = new Loan( $db );

#  Check for rge requests method
if ( $_SERVER[ 'REQUEST_METHOD' ] !== 'GET' ) {
    header( 'HTTP/1.1 405 Method Not Allowed' );
    header( 'Allow: POST' );
    exit();
}
#Your method should be here
$getAllLoanProvider = $Loan->getaAllLoanProviders();

if($getAllLoanProvider){

    $Loan->outputData(true, "Fetched All Records", $getAllLoanProvider);
}


unset( $Loan );
unset( $db );

