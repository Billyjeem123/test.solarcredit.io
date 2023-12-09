<?php

class Payment extends AbstractClasses {

    private   $conn;

    public function __construct( Database $database )
 {
        $this->conn = $database->connect();
    }

    #connectToPaystackAPI:: This method  connectToPaystackAPI..

  public function connectToPaystackAPI($reference)
{
    $url = 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer sk_test_476c7d23197ff39c9d7cfd1dd3384d6e5e9f46ce', # replace this with your own test key
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        $this->outputData(false, 'Unable to process request, try again later', null);
        return; // Return or exit the function after handling the error
    }

    // Additional code...

    return $response;
}


     public function processWallet( $reference )
 {
        $transaction_reference = $reference;
        #  		$paystack_public_key = 'sk_test_476c7d23197ff39c9d7cfd1dd3384d6e5e9f46ce';

        $url = 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference);

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer sk_test_476c7d23197ff39c9d7cfd1dd3384d6e5e9f46ce', # replace this with your own test key
        ] );

        $request = curl_exec( $ch );
        $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

        return ( $request );
    }

  /*
    # recordCreditTransaction::This method records ALL Credit OR DEBIT transactions
    # UPDATE:: This method has been moved to abstarct class.

    public function recordTransaction( $reference, $usertoken, $amount, $creditOrDebit )
 {
        $time = time();

        $sql = "INSERT INTO tblwallettransaction (ref, usertoken, amount, paid_at, type)
            VALUES (:reference, :usertoken, :amount, :timePaid, :type)";

        try {
            $stmt = $this->conn->prepare( $sql );
            $stmt->bindParam( ':reference', $reference );
            $stmt->bindParam( ':usertoken', $usertoken );
            $stmt->bindParam( ':amount', $amount );
            $stmt->bindParam( ':timePaid', $time );
            $stmt->bindParam( ':type', $creditOrDebit );

            $stmt->execute();
            return true;
        } catch ( PDOException $e ) {
            #  Handle the error
            $_SESSION[ 'err' ] = 'Unable to process request:'. $e->getMessage(); 
            return false;
        }
        finally {
            $stmt = null;
        }
    }
    */

    #saveUserTokenDuringPurchase:: This method is responsible for saving the usertoken during an ongoing purchase transaction.

    public function saveUserTokenDuringPurchase( string $token, int $usertoken, string $payment_type )
 {
        try {
            $sql = "INSERT INTO tblproduct_buyers (transactionToken, usertoken, payment_type) 
        VALUES (:transactionToken, :usertoken, :payment_type)";
            $stmt = $this->conn->prepare( $sql );
            $stmt->bindParam( ':transactionToken', $token );
            $stmt->bindParam( ':usertoken', $usertoken );
            $stmt->bindParam( ':payment_type', $payment_type );
            $stmt->execute();
        } catch ( PDOException $e ) {
            $_SESSION[ 'err' ] = 'Unable to record buyers:'. $e->getMessage();
            return false;
        }
        finally {
            $stmt = null;
        }
        return true;
    }

  #saveProductBoughtTransaction::This method saves All Product purchased
  
    public function saveProductBoughtTransaction(
        string  $transactionToken,
        int $productToken,
        int  $productQuantity,
        $productPrice,
        string $productType,
        $modeOfPayment,
        string $productname,
        string $productimage,
        $commission
    ) {
        $sql = 'INSERT INTO tblpurchasedonce(transactionToken, productToken, productQuantity, price, time, productType, modeOfPayment, productname, productimage, orderid, commission) ';
        $sql .= 'VALUES(:transactionToken, :productToken, :productQuantity, :price, :time, :productType, :modeOfPayment, :productname, :productimage, :orderid, :commission)';
        $time = time();
        $orderid = uniqid();
        try {
            $stmt = $this->conn->prepare( $sql );
            $stmt->bindParam( ':transactionToken', $transactionToken );
            $stmt->bindParam( ':productToken', $productToken );
            $stmt->bindParam( ':productQuantity', $productQuantity );
            $stmt->bindParam( ':price', $productPrice );
            $stmt->bindParam( ':time', $time );
            $stmt->bindParam( ':productType', $productType );
            $stmt->bindParam( ':modeOfPayment', $modeOfPayment );
            $stmt->bindParam( ':productname', $productname );
            $stmt->bindParam( ':productimage', $productimage );
            $stmt->bindParam( ':orderid', $orderid );
            $stmt->bindParam( ':commission', $commission );
            if ( !$stmt->execute() ) {
                return false;
            }
        } catch ( PDOException $e ) {
            #  Handle the error
            $_SESSION[ 'err' ] = 'Unable to insert payonce transaction: ' . $e->getMessage();
        }
        finally {
            $stmt   = null;
        }
        return true;
    }
    #removeProductFromCart::This methid removes an item from cart after purchase

    public function removeProductFromCart( $usertoken, $productToken )
 {
        try {
            $sql = 'DELETE FROM tblcarts WHERE uToken = :usertoken AND pToken = :productToken';
            $stmt = $this->conn->prepare( $sql );
            $stmt->bindParam( ':usertoken', $usertoken );
            $stmt->bindParam( ':productToken', $productToken );
            if ( !$stmt->execute() ) {
                $_SESSION[ 'err' ] = 'Something went wrong, please try again.';
                return false;
            } else {
                return true;
            }
        } catch ( PDOException $e ) {

            $_SESSION[ 'err' ] = 'Unable to delete cart itmes: '.$e->getMessage();

        }
        finally {
            $stmt = null;
            #  $this->conn = null;
        }
    }

    #updateUserAccountBalance ::This method updateUserAccountBalance debits or credit user account depending on the transactionType.
     # UPDATE:: This method has been moved to abstarct class.
/*
    public function updateUserAccountBalance( $amount, $usertoken, $transactionType ) {
        $operator = $transactionType === 'credit' ? '+' : '-';

        $sql = "UPDATE tblwallet 
                SET amount = amount $operator :amount
                WHERE usertoken = :usertoken";

        try {
            $stmt = $this->conn->prepare( $sql );
            $stmt->bindParam( ':amount', $amount );
            $stmt->bindParam( ':usertoken', $usertoken );

            $stmt->execute();
            return true;
        } catch ( PDOException $e ) {
            #  Handle the error
            $_SESSION[ 'err' ] = 'Unable to process request: ' . $e->getMessage();
            return false;
        }
        finally {
            $stmt = null;
        }
    }
    
    */

    # fetchWalletHistory:: Thismethod fetches user WalletHistory

    public function fetchWalletHistory( int $usertoken )
 {
        $dataArray = array();
        try {
            $sql = 'SELECT amount, id, paid_at, type FROM tblwallettransaction WHERE usertoken = :usertoken ORDER BY id DESC';
            $stmt = $this->conn->prepare( $sql );
            $stmt->bindParam( ':usertoken', $usertoken );
            $stmt->execute();
            $notifications = $stmt->fetchAll( PDO::FETCH_ASSOC );
            foreach ( $notifications as $allNotification ) {
                $array = [
                   'amount' => $this->formatCurrency( $allNotification[ 'amount' ] ),
                    'paymentDate' => $this->formatDate( $allNotification[ 'paid_at' ] ),
                    'paymentType' => ( $allNotification[ 'type' ] )
                ];
                array_push( $dataArray, $array );
            }
            if ( count( $dataArray ) === 0 ) {
                $this->outputData( true, 'No transaction found', [] );
                exit;
            }
        } catch ( PDOException $e ) {
            $this->outputData( false, 'Error fetching transaction history: ' . $e->getMessage(), null );
            exit();
        }
        finally {
            $stmt = null;
            $this->conn = null;
        }
        return $dataArray;
    }

    #calculateAmountRemaining::Thismethod checks for AmountRemainning to pay  for a product installmentally

    public function calculateAmountRemaining( $initialPriceToPay ) {
        $fiftyPercent = 50;
        $amountRemaining = ( $fiftyPercent / 100 ) * $initialPriceToPay;
        return $amountRemaining;
    }

    #saveAllProductBoughtInstallmentally:: This method saves all product purchased installmentally

    public function saveAllProductBoughtInstallmentally(
        $totalAmount,
        $amountPaid,
        $amountRemainning,
        $amount_debited_so_far,
        $checkSubscribedPlan,
        $calculateMonthlyRepayment,
        $productloantoken,
        $dateExpectedToEnd,
        $usertoken,
        $agreementtoken
    ) {

        $isGuarantorSaved = false;

        #  Prepare the fields and values for the gurantor query
        $LoanProductFields = [
            'total_amount' => $totalAmount,
            'amountpaid' => $amountPaid,
            'amountRem' => $amountRemainning,
            'amount_debited_so_far' => $amount_debited_so_far,
            'duration' => $checkSubscribedPlan,
            'amontMonthly' => $calculateMonthlyRepayment,
            'token' => $productloantoken,
            'date_paid' => time(),
            'finished_date' => $dateExpectedToEnd,
            'usertoken' => $usertoken,
            'auto_debit_approval_token' => $agreementtoken
 
        ];

        # Build the SQL query
        $placeholders = implode( ', ', array_fill( 0, count( $LoanProductFields ), '?' ) );
        $columns = implode( ', ', array_keys( $LoanProductFields ) );
        $sql = "INSERT INTO tbl_installment_purchases ($columns) VALUES ($placeholders)";

        #  Execute the query and handle any errors
        try {
            #  $this->conn->beginTransaction();

            $stmt =  $this->conn->prepare( $sql );
            $i = 1;
            foreach ( $LoanProductFields as $value ) {
                $type = is_int( $value ) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue( $i, $value, $type );
                $i++;
            }
            $stmt->execute();

            $isGuarantorSaved = true;

        } catch ( PDOException $e ) {
            $_SESSION[ 'err' ] = 'Error:'. $e->getMessage();
            #  $this->conn->rollback();
            $isGuarantorSaved = false;

        }
        finally {
            $stmt = null;

        }
        return $isGuarantorSaved;
    }

    # TcalculateMonthsAhead:: This function calculates the month( s ) ahead for a given subscribed package

   public function calculateMonthsAheadAndSave( string $loantoken, int $usertoken,  int $subscribedPackage,  string $repaymentPerMonth ) {

        $startDate = new DateTime();

        for ( $i = 0; $i < $subscribedPackage; $i++ ) {
            $estimatedDate = $startDate->modify( '+1 month' );
            $monthExpectedToPay = $estimatedDate->format( 'Y-m-d' );

             # Calculate a week before due date.
		  $remindAWeekBefore = date("Y-m-d", strtotime("-7 days", strtotime($monthExpectedToPay)));

            # Calculate a day before due date.
           $remindADayBefore = date("Y-m-d", strtotime("-1 day", strtotime($monthExpectedToPay)));

           # Calculate  2days before due date.
           $remindThreeDaysBefore = date("Y-m-d", strtotime("-3 days", strtotime($monthExpectedToPay)));


            # Store month( s ) to pay And Amount To Pay.
            $saveWhenExpectedToPay = $this->storeAllInstallmentPaymentDates( $loantoken, $usertoken, 
            $monthExpectedToPay,  ceil($repaymentPerMonth), $remindADayBefore, $remindAWeekBefore, $remindThreeDaysBefore );
            if ( !$saveWhenExpectedToPay ) {
                $this->outputData( false, $_SESSION[ 'err' ], null );
                return false;
            }

        }

        return true;
    }

   # storeAllInstallmentPaymentDates::This method Store month( s ) to pay And Amount To Pay.
    public function storeAllInstallmentPaymentDates(
        string $token,
        int $usertoken,
        string $dueDate,
        string $priceToPay,
        string $a_day_before,
        string $a_week_before,
        string $_3_days_before
    ) {
        $isInstallMentSaved = true;

        #  Prepare the fields and values for the gurantor query
        $LoanProductFields = [
            'token' => $token,
            'usertoken' => $usertoken,
            'dueDate' => $dueDate,
            'priceToPay' => $priceToPay,
            'a_day_before' => $a_day_before,
            'a_week_before' => $a_week_before,
            '3_days_before'=> $_3_days_before

        ];

        # Build the SQL query
        $placeholders = implode( ', ', array_fill( 0, count( $LoanProductFields ), '?' ) );
        $columns = implode( ', ', array_keys( $LoanProductFields ) );
        $sql = "INSERT INTO loan_product_purchases ($columns) VALUES ($placeholders)";

        #  Execute the query and handle any errors
        try {
            #  $this->conn->beginTransaction();

            $stmt =  $this->conn->prepare( $sql );
            $i = 1;
            foreach ( $LoanProductFields as $value ) {
                $type = is_int( $value ) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue( $i, $value, $type );
                $i++;
            }
            $stmt->execute();

            $isInstallMentSaved = true;

        } catch ( PDOException $e ) {
            $_SESSION[ 'err' ] = 'Error:'. $e->getMessage();
            #  $this->conn->rollback();
            $isInstallMentSaved = false;

        }
        finally {
            $stmt = null;

        }
        return $isInstallMentSaved;

    }

    # saveProductInstallments::This method Store product purchased installmentally
    public function saveProductInstallments(
        string $token,
        string $productToken,
        string $productQuantity,
        string $productname,
        string $price,
        string $productType,
        string $modeOfPayment,
        string $productimage,
        $commission,
        string $agreementtoken
    ) {
        $isInstallMentSaved = true;

        #  Prepare the fields and values for the gurantor query
        $LoanProductFields = [
            'transactionToken' => $token,
            'productToken' => $productToken,
            'productQuantity' => $productQuantity,
            'productname' => $productname,
            'price' => $price,
            'time' => time(),
            'productType' => $productType,
            'modeOfPayment' => $modeOfPayment,
            'orderid' => uniqid(),
            'productimage' => $productimage,
             'commission' => $commission,
             'auto_debit_approval_token' => $agreementtoken

        ];

        # Build the SQL query
        $placeholders = implode( ', ', array_fill( 0, count( $LoanProductFields ), '?' ) );
        $columns = implode( ', ', array_keys( $LoanProductFields ) );
        $sql = "INSERT INTO tbl_store_allinstallment_product ($columns) VALUES ($placeholders)";

        #  Execute the query and handle any errors
        try {
            #  $this->conn->beginTransaction();

            $stmt =  $this->conn->prepare( $sql );
            $i = 1;
            foreach ( $LoanProductFields as $value ) {
                $type = is_int( $value ) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue( $i, $this->sanitizeInput($value), $type );
                $i++;
            }
            $stmt->execute();

            $isInstallMentSaved = true;

        } catch ( PDOException $e ) {
            $_SESSION[ 'err' ] = 'Error:'. $e->getMessage();
            #  $this->conn->rollback();
            $isInstallMentSaved = false;

        }
        finally {
            $stmt = null;

        }
        return $isInstallMentSaved;

    }


 
  #calculateProductDividends:: This method calculate Solar Dividens commission for Buy Back
    public function calculateProductDividends($amount) {
        $dividends = ($amount * $_ENV['solarDividends']) / 100;
        return $dividends;
    }
    
    
    public function creditCentralWallet($amount, $creditType, $reference)
    {
        $sql = 'INSERT INTO tbladmin_finances (amount, type, reference, time) VALUES (:amount, :creditType, :reference, :time)';
        
          $time  =  time();

        try {
            $stmt = $this->conn->prepare( $sql );
            $stmt->bindParam( ':amount', $amount );
            $stmt->bindParam( ':creditType', $creditType);
            $stmt->bindParam( ':reference', $reference);
             $stmt->bindParam( ':time', $time);

            if ( !$stmt->execute() ) {
                return false;
            }

        } catch ( \PDOException $e ) {
            $_SESSION[ 'err' ] = 'Error: ' . $e->getMessage();
            return false;
        }

        $updateAdminAccountBalance = $this->updateAdminAccountBalance($amount, $_ENV['transactionType'] = "credit");
        if(!$updateAdminAccountBalance){
            $this->outputData(false, $_SESSION['err'], null);
            exit;
             }

        return true;
    }


    #updateUserAccountBalance ::This method updateAdminAccountBalance debits or credit user account depending on the transactionType.

    public function updateAdminAccountBalance($amount,  $transactionType)
    {
        $operator = $transactionType === 'credit' ? '+' : '-';

        $sql = "UPDATE tbladmin_wallet 
                SET amount = amount $operator :amount
                WHERE usertoken = 1";

        try {
            $stmt = $this->conn->prepare( $sql );
            $stmt->bindParam(':amount', $amount);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            // Handle the error
            $_SESSION['err'] = 'Unable to process request: ' . $e->getMessage();
            return false;
        } finally {
            $stmt = null;
        }
    }
    
    public function  calculateInstallmentDividends($amount){

    return $amount/2;

  } 




    # saveProductInstallments::This method Store product purchased installmentally
    public function saveUserProductTrnsaction(
        string $usertoken,
        string $productprice,
        string $commission,
        string $producttoken,
        string $productType,
    ) {
        $isInstallMentSaved = true;

        #  Prepare the fields and values for the gurantor query
        $usertransx = [
            'usertoken' => $usertoken,
            'productprice' => $productprice,
            'commission' => $commission,
            'producttoken' => $producttoken,
            'time' => time(),
            'productType' => $productType

        ];

        # Build the SQL query
        $placeholders = implode( ', ', array_fill( 0, count( $usertransx ), '?' ) );
        $columns = implode( ', ', array_keys( $usertransx ) );
        $sql = "INSERT INTO tbluserproductranx ($columns) VALUES ($placeholders)";

        #  Execute the query and handle any errors
        try {
            #  $this->conn->beginTransaction();

            $stmt =  $this->conn->prepare( $sql );
            $i = 1;
            foreach ( $usertransx as $value ) {
                $type = is_int( $value ) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue( $i, $this->sanitizeInput($value), $type );
                $i++;
            }
            $stmt->execute();
            $isInstallMentSaved = true;

        } catch ( PDOException $e ) {
            $_SESSION[ 'err' ] = 'Error:'. $e->getMessage();
            #  $this->conn->rollback();
            $isInstallMentSaved = false;

        }
        finally {
            $stmt = null;

        }
        return $isInstallMentSaved;

    }

}
