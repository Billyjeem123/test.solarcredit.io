<?php
    require_once( '../../assets/initializer.php' );
    $data = ( array ) json_decode( file_get_contents( 'php://input' ), true );

    $payment = new Payment( $db );

    if ( empty( $data[ 'reference' ] ) ) {
        $payment->outputData( false, 'Reference is missing', null );
        exit;
    }
    $mastercardPaymentVerificationMethod = $payment->connectToPaystackAPI( $data[ 'reference' ] );
    $decodeMastercardVerificationMethod = json_decode( $mastercardPaymentVerificationMethod, true );
    
    if ($decodeMastercardVerificationMethod === null) {
      # Handle the case when the decoding failed
      $payment->outputData(false, 'Failed to decode verification method', null);
      exit;
      }

    if ( $decodeMastercardVerificationMethod[ 'status' ] !== true) {
        $payment->outputData( false, 'Cannot process transaction,try again later', null );
        exit;
    }

    $auth = new Auth( $db );

    if ( !$auth->authenticateUser( $data[ 'usertoken' ] ) ) {
        $auth->outputData( false, $_SESSION[ 'err' ], null );
        unset( $auth );
        exit;
    }
    

    $token  = $payment->token();

    # Save usertoken of buyer during purchase.
    if(!$payment->saveUserTokenDuringPurchase($token,  $data[ 'usertoken' ], $_ENV['PAYMENT_OPTION'] = "Paid-Once")){
    $payment->outputData(false, $_SESSION['err'], null);
    exit;
    }

     $mailer = new Mailer();
     $count = 0;
     
     foreach ($data['productBought'] as $key => $allProducts) {

         if ($allProducts['productType'] === "Userproduct") {

            # To future dev reading this code, In the later on,  
            # we might need to alert product owners for the code below, but right now i think we shouldn't.
            # Just in case the project ownsers asked for this,worry no more. just uncomment this code, if needs be.
            # It should definely do the work for you.
            # I hope i helped you . 

            $ownerRecord = $payment->getUserdata($allProducts['ownertoken']);
            
             $calculateDividends = $payment->calculateProductDividends($allProducts['productPrice']);
             
              $creditCentralWallet = $payment->creditCentralWallet($calculateDividends, 
            $_ENV['creditOrDebit']="Credit", " Received $calculateDividends naira from  $ownerRecord[fname] for product buyback commission.");
            if (!$creditCentralWallet) {
            $payment->outputData(false, $_SESSION['err'], null);
            exit;
            }

            try {
                $mailer->notifyOwnersOfSale($ownerRecord['mail'], $ownerRecord['fname'], $allProducts['productname']);
            } catch (Exception $e) {
                # Handle the error or log it as needed
                $errorMessage = date('[Y-m-d H:i:s] ') . 'Error sending mail for ' . __METHOD__ . '  ' . PHP_EOL . 'Error Message: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
                error_log($errorMessage, 3, 'mail.log');
            }
     
             $saveProductBoughtTransaction = $payment->saveProductBoughtTransaction(
                 $token, 
                 ($allProducts['productToken']),
                 intval($allProducts['productQuantity']),   
                 $allProducts['productPrice'],  
                 $allProducts['productType'],  
                 $_ENV['MODE_OF_PAYMENT'] = "Mastercard",
                 $allProducts['productname'],
                 $allProducts['productimage'],
                 $calculateDividends
             );
     
             if (!$saveProductBoughtTransaction) {
                 $payment->outputData(false, $_SESSION['err'], null);
                 exit;
             }
     
             $count++;
         }else{
            $count = 0;
            $saveProductBoughtTransaction = $payment->saveProductBoughtTransaction(
                $token, 
                intval($allProducts['productToken']),
                intval($allProducts['productQuantity']),   
                $allProducts['productPrice'],  
                $allProducts['productType'],  
                $_ENV['MODE_OF_PAYMENT'] = "Mastercard",
                $allProducts['productname'],
                $allProducts['productimage'],
                0.00
            );

            if (!$saveProductBoughtTransaction) {
                $payment->outputData(false, $_SESSION['err'], null);
                exit;
            }

            $count++;

         }
         $removeProductFromCart = $payment->removeProductFromCart($data['usertoken'],  $allProducts['productToken']);
         if (!$removeProductFromCart) {
            $payment->outputData(false, $_SESSION['err'], null);
            exit;
        }
         
     }


        #Send Notofication to admin of new purchase alert.
    
         try {
       $sendMailToAdmin = $mailer->confirmFullPaymentAndProductPurchaseToAdmin($data['productBought'], $payment->formatCurrency($data[ 'Totalprice' ]));
    } catch (Exception $e) {
        # Handle the error or log it as needed
        $errorMessage = date('[Y-m-d H:i:s] ') . 'Error sending mail for ' . __METHOD__ . '  ' . PHP_EOL . 'Error Message: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
        error_log($errorMessage, 3, 'mail.log');
    }
   
     $payment->outputData(true, 'Transaction sucessfull', null);
     unset($payment);
     unset( $auth );
     

