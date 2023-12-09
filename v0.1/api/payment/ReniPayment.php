<?php
    require_once( '../../assets/initializer.php' );
    $data = ( array ) json_decode( file_get_contents( 'php://input' ), true );

    $payment = new Payment( $db );

    $ReniPayment = new ReniPayment();

        #  Check for rge requests method
        if ( $_SERVER[ 'REQUEST_METHOD' ] !== 'POST' ) {
            header( 'HTTP/1.1 405 Method Not Allowed' );
            header( 'Allow: POST' );
            exit();
        }

        $auth = new Auth( $db );

        if ( !$auth->authenticateUser( $data[ 'usertoken' ] ) ) {
            return $auth->outputData( false, "User Does Not Exists", null );
            unset( $auth );
            
        }

           $getUserDetails   =  $auth->getUserdata($data['usertoken']);  // get user details plus user real-time account information
        $getAccountBalance = $getUserDetails['availableBalance'];
         
    
 
        if($getAccountBalance < $data['Totalprice']){
            $payment->outputData(false, 'Insufficient funds. Kindly fund your wallet', null);
			exit;

        }

        $RequestFund =  $ReniPayment->RequestFund($getUserDetails['renitoken'], $data[ 'Totalprice' ]);


           echo json_encode($RequestFund);
        
            unset($auth);
            unset($Payment);
            unset($ReniPayment);
       
       