<?php

abstract class AbstractClasses {

    public function getMemoryUsage() {
        $mem_usage = memory_get_usage(true);
        if ($mem_usage < 1024) {
            return $mem_usage . ' bytes';
        } elseif ($mem_usage < 1048576) {
            return round($mem_usage / 1024, 2) . ' KB';
        } else {
            return round($mem_usage / 1048576, 2) . ' MB';
        }

    }

    public function checkSize() {
        $memory_usage = $this->getMemoryUsage();
        echo 'Memory usage: ' . $memory_usage;
    }

    #  validate email

    public function validateEmail($value) {
        #  code...
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * sanitizeInput Parameters
     *
     * @param [ type ] $input
     * @return string
     */
    public function sanitizeInput($input) {
        # Remove white space from beginning and end of string
        $input = trim($input);
        # Remove slashes
        $input = stripslashes($input);
        # Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $input;
    }

    #  resourceNotFound::Check for id if exists

    private function resourceNotFound(int $id): void {

        echo json_encode(['message' => "Resource with id $id not found"]);
    }

    /**
     * respondUnprocessableEntity alert of errors deteced
     *
     * @param array $errors
     * @return void
     */

    public function respondUnprocessableEntity(array $errors): void {

        $this->outputData(false, 'Kindly review your request parameters to ensure they comply with our requirements.', $errors);
    }

    public function connectToThirdPartyAPI(array $payload, string $url) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            $this->outputData(false, 'Unable to process request, try again later', null);
        }

        curl_close($ch);

        return $response;
    }

    public function respondWithInternalError($errors): void {

        $this->outputData(false, 'Unable to process request, try again later', $errors);
    }

    public function token() {

        $defaultPassword = mt_rand(100000, 999999);
        return $defaultPassword;
    }

    #This method checks for KYC staus of  a user

    public function getkycStatus(string $usertoken) {
        try {
            $status = 1;
            $db = new Database();
            $sql = 'SELECT usertoken, status FROM tblkyc WHERE usertoken = :userToken  AND  status = :status';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':userToken', $usertoken, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                throw new Exception('Failed to execute query');
            }
            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        } finally {
            $stmt = null;
            unset($db);

        }
    }

    #This method verifies a user verifyNextOfKin

    public function verifyNextOfKin($usertoken) {
        try {
            $db = new Database();
            $sql = 'SELECT usertoken  FROM tblkin WHERE usertoken = :userToken';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':userToken', $usertoken, PDO::PARAM_INT);
            $stmt->execute([$usertoken]);

            if ($stmt->rowCount() == 0) {

                return false;
            }

            return true;

        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        } finally {
            $stmt = null;
            unset($db);
        }
    }

    #getCategoryName:: This method accept token to get the category a product belongs to

    public function getProductCategory(int $productCatgoryId) {
        try {
            $db = new Database();
            $sql = 'SELECT catname FROM tblcategory WHERE id = :productCatgoryId';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam('productCatgoryId', $productCatgoryId);
            $stmt->execute();
            $result_set = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result_set === false) {

                $this->outputData(false, "Category with  $result_set[id] not found", null);
                exit;
            }

            $dataArray = ['catname' => $result_set['catname']];
        } catch (PDOException $e) {
            $this->outputData(false, 'Error fetching category name: ' . $e->getMessage(), null);
            return;
        } catch (Exception $e) {
            $this->outputData(false, $e->getMessage(), null);
            return;
        } finally {
            $stmt = null;
            unset($db);
        }
        return $dataArray;
    }

    #getUserdata::This method fetches All info related to a user

    public function getUserdata(int $usertoken) {
        try {
            $db = new Database();
            $reni = new ReniPayment($db);
            $sql = 'SELECT * FROM tblusers WHERE usertoken = :usertoken';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam('usertoken', $usertoken);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() === 0) {

                $this->outputData(false, 'No user found', null);
                exit;
            }

            $getkycStatus = $this->getkycStatus($usertoken);
            $verifyNextOfKin = $this->verifyNextOfKin($usertoken);
            $getAllOutstandingFee = $this->getAllOutstandingFee($usertoken);
            $isInstallmentOngoing = $this->isInstallmentOngoing($usertoken);
            $getAccountBalance = $reni->getUserFinacialDetails($user['renitoken']); // This method fetches user Account number and balance from renitrust
            //  echo json_encode($getAccountBalance['data']);
            //  exit;
            $array = [
                'fname' => $user['fname'],
                'mail' => $user['mail'],
                'usertoken' => intval($user['usertoken']),
                'phone' => $user['phone'],
                'regStatus' => ($user['status'] === 1) ? true : false,
                'userType' => $user['userType'],
                'occupation' => $user['occupation'],
                'renitoken' => $user['renitoken'] ?? 0,
                'kycStatus' => $getkycStatus,
                'availableBalance_thousand' => $getAccountBalance['data']['WithdrawableBalance_th'] ?? false,
                'availableBalance' => $getAccountBalance['data']['WithdrawableBalance'] ?? false,
                'nextOfKin' => $verifyNextOfKin,
                'isInstallmentOngoing' => $isInstallmentOngoing,
                'getAllOutstandingFee' => $getAllOutstandingFee,
                "accountNumber" => $getAccountBalance['data']['accountNumber'] ?? 0,
            ];

        } catch (PDOException $e) {
            $_SESSION['err'] = 'Error retrieving user details: ' . $e->getMessage();
            exit;
            #  $this->respondWithInternalError( false, 'Unable to retrieve user details: ' . $e->getMessage(), null );
            return false;
        } finally {
            $stmt = null;
            unset($db);
        }
        return $array;
    }

    # Get user data via mail
    public function getUserdataViaMail(string $mail) {
        try {
            $db = new Database();
            $sql = 'SELECT * FROM tblusers WHERE mail = :mail';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam('mail', $mail);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (count($user) == 0) {

                $this->outputData(false, 'No user found', null);
                exit;
            }

            $array = [
                'fname' => $user['fname'],
                'mail' => $user['mail'],
                'usertoken' => intval($user['usertoken']),
                'phone' => $user['phone'],

            ];

        } catch (PDOException $e) {
            $_SESSION['err'] = 'Error retrieving user details: ' . $e->getMessage();
            exit;
            #  $this->respondWithInternalError( false, 'Unable to retrieve user details: ' . $e->getMessage(), null );
            return false;
        } finally {
            $stmt = null;
            unset($db);
        }
        return $array;
    }

    #getKYCData::This method fetches   verifies  a user bvn..

    public function updateUserBVN($data) {
        $verifyBvnPost = [
            'usertoken' => $data['usertoken'],
            'bvn' => $data['bvn'],
        ];

        $url = $_ENV['RENI_SANDBOX'] . '/updateUserBVN';

        $connectToReniTrust = $this->connectToReniTrust($verifyBvnPost, $url);

        $encodeMailResponse = json_decode($connectToReniTrust, true);
        if (isset($encodeMailResponse['success'])) {
            return $encodeMailResponse;
        }

        return $encodeMailResponse;
    }

#This method  links  the application to renitrust
    public function connectToReniTrust(array $payload, $url) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $headers = [
            'Authorization: Bearer ' . $_ENV['Solar_Access_Bearer'],
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            $this->outputData(false, 'Unable to process request, try again later', null);
            exit;
        }

        curl_close($ch);

        return $response;
    }

    public function getKYCData($usertoken) {

        try {
            $db = new Database();
            $sql = 'SELECT * FROM tblkyc WHERE usertoken = :usertoken AND status = 1';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':usertoken', $usertoken);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return null;

            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $array = [
                'fname' => $user['fullname'],
                'profession' => $user['occupation'],
                // 'photo' => $user[ 'imageUrl' ],
                'kycStatus' => ($user['status'] === 1) ? true : false,
            ];

        } catch (PDOException $e) {
            $this->outputData(false, 'Error retrieving KYC data: ' . $e->getMessage(), null);
            return false;
        } finally {
            $stmt = null;
            unset($db);
        }

        return $array;
    }

    #updateUserAccountBalance ::This method updateUserAccountBalance debits or credit user account depending on the transactionType.

    public function updateUserAccountBalance($amount, $usertoken, $transactionType) {
        $operator = $transactionType === 'credit' ? '+' : '-';

        $db = new Database();

        $sql = "UPDATE tblwallet
                SET amount = amount $operator :amount
                WHERE usertoken = :usertoken";

        try {
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':usertoken', $usertoken);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            #  Handle the error
            $_SESSION['err'] = 'Unable to process request: ' . $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            unset($db);
        }
    }

    # recordCreditTransaction::This method records ALL Credit OR DEBIT transactions

    public function recordTransaction($reference, $usertoken, $amount, $creditOrDebit) {
        $time = time();
        $db = new Database();

        $sql = "INSERT INTO tblwallettransaction (ref, usertoken, amount, paid_at, type)
                VALUES (:reference, :usertoken, :amount, :timePaid, :type)";

        try {
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':reference', $reference);
            $stmt->bindParam(':usertoken', $usertoken);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':timePaid', $time);
            $stmt->bindParam(':type', $creditOrDebit);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            #  Handle the error
            $_SESSION['err'] = 'Unable to process request:' . $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            unset($db);
        }
    }

    #authenticateUser:: This method authencticates User data

    public function authenticateUser($usertoken) {
        $db = new Database();
        try {
            $sql = 'SELECT usertoken FROM tblusers WHERE usertoken = :usertoken';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':usertoken', $usertoken);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {

                $_SESSION['err'] = 'User does not exists';
                return false;
                exit;
            }

            return true;
        } catch (PDOException $e) {
            $_SESSION['err'] = $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            unset($db);
        }
    }

    #formatDate::This method format date to humna readable format
    public function formatDate($time) {

        // return date( 'D d M, Y: H', $time );
        return date('F d, Y', $time);

    }

    public function convertToReadableDate($dateString) {
        if (!is_string($dateString)) {
            return "Invalid date format";
        }

        $timestamp = strtotime($dateString);
        if ($timestamp === false) {
            return "Invalid date";
        }

        $readableDate = date("F j, Y", $timestamp);
        return $readableDate;
    }

    public static function formatDateReadable1($time) {

        $timestamp = strtotime($time);
        $formattedDate = date('F d, Y', $timestamp);

        return $formattedDate;

    }

    #v::This method format date to amount readable fomat
    public function formatCurrency($amonut) {

        return number_format($amonut, 2);

    }

    #checkSubscribedPlan ::This methos checks for Subscription plan.It returns the subscription integer value

    public function checkSubscribedPlan(int $planid) {

        try {
            $db = new Database();
            $sql = 'SELECT id, plan_value FROM tblplan WHERE id = :plan_id';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':plan_id', $planid);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {

                $_SESSION['err'] = 'Plan is not valid';
                return false;
            }

            $planArray = $stmt->fetch(PDO::FETCH_ASSOC);

            $array = [
                'planid' => $planArray['plan_value'],
            ];

        } catch (PDOException $e) {
            $_SESSION['err'] = 'Error: ' . $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            unset($db);
        }
        return $array;
    }

    #Check Intereest Rate of a prvider
    public function getInterestRate(int $providerid) {

        try {
            $db = new Database();
            $sql = 'SELECT id, interest_rate  FROM tblloanprovider WHERE id = :providerid';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':providerid', $providerid);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {

                $_SESSION['err'] = 'provider id is not valid';
                return false;
            }

            $planArray = $stmt->fetch(PDO::FETCH_ASSOC);

            $array = [
                'providerid' => $planArray['id'],
                'provider_rate' => $planArray['interest_rate'],
            ];

        } catch (PDOException $e) {
            $_SESSION['err'] = 'Error: ' . $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            unset($db);
        }
        return $array;
    }

    # Calculates the monthly repayment amount for a loan based on the loan amount and loan term.
    public function calculateMonthlyRepayment(float $loan_amount, int $loan_term): float {
        return $loan_amount / $loan_term;
    }

    public function calculateDateAhead($package) {

        $current_date = date("Y-m-d");
        $three_months_ahead = date("Y-m-d", strtotime($current_date . $package));
        return $three_months_ahead;
    }

    public function currentDate() {

        $timezone = new DateTimeZone('Africa/Lagos');
        $currentDate = new DateTime('now', $timezone);
        $currentDateString = $currentDate->format('Y-m-d');
        return $currentDateString;
    }

/*
|--------------------------------------------------------------------------
|ALL HISTORY METHODS
|--------------------------------------------------------------------------
 */

    #This method is specifically meaant for? your guess is as good as mine.. wait did you know it? Yes, to track user history logs..

    public function logUserActivity(int $usertoken, $logs, $longtitude, $latitude) {
        try {

            $db = new Database();
            $time = time();
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            $sql = 'INSERT INTO tblhistory_log (usertoken, logs, longtitude, latitude, ip,time)
            VALUES (:usertoken, :logs, :longtitude,:latitude, :ip,:time )';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':usertoken', $usertoken);
            $stmt->bindParam(':logs', $logs);
            $stmt->bindParam(':longtitude', $longtitude);
            $stmt->bindParam(':latitude', $latitude);
            $stmt->bindParam(':ip', $ipAddress);
            $stmt->bindParam(':time', $latitude);
            $stmt->bindParam(':latitude', $time);
            if (!$stmt->execute()) {
                $this->outputData(false, 'Unable to process query', null);
                return false;
            }
        } catch (Exception $e) {
            $_SESSION['err'] = "Error" . $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            unset($db);
        }
        return true;
    }

    # getAllHistoryLogs::This method fetches all History logs Belonging to a user

    public function getAllHistoryLogs(int $usertoken) {

        $dataArray = array();

        $db = new Database();
        $sql = 'SELECT * FROM tblhistory_log  WHERE usertoken = :usertoken';
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':usertoken', $usertoken);
        try {
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return null;
                exit;
            }
            $historyLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($historyLogs as $allLogs) {
                $array = [
                    'Logs' => $allLogs['logs'],
                    'longtitude' => $allLogs['longtitude'],
                    'ipAddress' => $allLogs['ip'],
                    'latitude' => $allLogs['latitude'],
                    'Date' => $this->formatDate($allLogs['time']),
                ];

                array_push($dataArray, $array);
            }

        } catch (PDOException $e) {
            $_SESSION['err'] = 'Unable to retrieve user history' . $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            unset($db);
        }
        return $dataArray;
    }

    public function notifyUserMessage($context, $userToken) {
        $currentTime = time();
        $db = new Database();

        $sql = "INSERT INTO tblnotify (context, usertoken, time)
               VALUES (:context, :userToken, :time)";

        try {
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':context', $context);
            $stmt->bindParam(':userToken', $userToken);
            $stmt->bindParam(':time', $currentTime);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            #  Handle the error
            $_SESSION['err'] = $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            unset($db);
        }
    }

    public function getAllOutstandingFee($usertoken) {
        $checkLoanForUserDebt = $this->checkLoanForUserDebt($usertoken);
        $checkProductLoanForUserDebt = $this->checkProductLoanForUserDebt($usertoken);

        $getOutstandingTotal = $checkLoanForUserDebt + $checkProductLoanForUserDebt;

        return $this->formatCurrency($getOutstandingTotal);
    }

    #checkLoanForUserDebt:: This method checks for user loan of amount owned
    public function checkLoanForUserDebt($usertoken) {
        try {
            $db = new Database();
            $sql = "SELECT usertoken, amountToBorrow, amount_debited_so_far, status FROM loan_records WHERE usertoken = :usertoken AND  status  =1";
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':usertoken', $usertoken);

            if (!$stmt->execute()) {
                return false;
            }

            if ($stmt->rowCount() == 0) {
                return 0.00;
            }
            $totalAmountOwing = 0; #  Variable to hold the total amount owed

            $loanRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($loanRecords as $record) {
                $amountToBorrow = $record['amountToBorrow'];
                $amountDebitedSoFar = $record['amount_debited_so_far'];

                if ($amountDebitedSoFar === $amountToBorrow) {
                    return 0.00;
                } else {
                    $amountOwing = $amountToBorrow - $amountDebitedSoFar;
                    $totalAmountOwing += $amountOwing; #  Add the amount owing to the total
                }
            }

            if ($totalAmountOwing > 0) {
                return $totalAmountOwing;
            }
        } catch (PDOException $e) {
            #  Handle the exception here
            return false;
        } finally {
            $stmt = null;
            unset($db);
        }
    }

    #checkProductLoanForUserDebt:: This method checks for product user loan of amount owned
    public function checkProductLoanForUserDebt($usertoken) {
        try {
            $db = new Database();
            $sql = "SELECT total_amount, usertoken, amount_debited_so_far
                 FROM tbl_installment_purchases WHERE usertoken = :usertoken";
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':usertoken', $usertoken);

            if ($stmt->execute()) {
                $loanRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $totalAmountOwing = 0; #  Variable to hold the total amount owed

                foreach ($loanRecords as $record) {
                    $total_amount = $record['total_amount'];
                    $amountDebitedSoFar = $record['amount_debited_so_far'];

                    if ($amountDebitedSoFar === $total_amount) {
                        return 0.00;
                    } else {
                        $amountOwing = $total_amount - $amountDebitedSoFar;
                        $totalAmountOwing += $amountOwing; #  Add the amount owing to the total
                    }
                }

                if ($totalAmountOwing > 0) {
                    return $totalAmountOwing;
                }
            }

            return 0;
        } catch (PDOException $e) {
            #  Handle the exception here
            return false;
        } finally {
            $stmt = null;
            unset($db);
        }
    }

#isInstallmentOngoing:: This checks if  a user has an active installation plan

    public function isInstallmentOngoing($usertoken) {

        $db = new Database();

        $sql = "SELECT COUNT(*) as count FROM tbl_installment_purchases
            WHERE usertoken = :usertoken";

        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':usertoken', $usertoken);

        if (!$stmt->execute()) {
            return false;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $row['count'];

        if ($count > 0) {
            $sql = "SELECT COUNT(*) as ongoingCount FROM tbl_installment_purchases
                WHERE usertoken = :usertoken AND isCompletedStatus = 0";

            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':usertoken', $usertoken);

            if (!$stmt->execute()) {
                return false;
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $ongoingCount = $row['ongoingCount'];

            if ($ongoingCount > 0) {
                return true; #  Installment is ongoing
            }
        }
        #No user found"
        unset($db);
        return false; #  User has no record of installment or all installments are completed
    }

    public function calculateDateDifferenceInDays($package) {

        $days_in_month = 30; // Assuming an average of 30 days per month
        $days_ahead = $package * $days_in_month;

        return $days_ahead;
    }

/**
 * Calculates the estimated total months for repaying a loan.
 *
 * @param int $subscribedPackage The number of subscribed months for the loan.
 *
 * @return int The count of estimated months for loan repayment.
 */
    public function estimateTotalMonthsForLoanRepayment(int $subscribedPackage) {
        // Initialize the start date as the current date and time
        $startDate = new DateTime();

        // Counter to keep track of the number of months
        $count = 0;

        // Loop through the subscribed months
        for ($i = 0; $i < $subscribedPackage; $i++) {
            // Modify the start date by adding one month
            $startDate->modify('+1 month');

            // Increment the counter for each month
            $count++;
        }

        // Return the count of estimated months for loan repayment
        return $count;
    }

    // This endpoint sends recurrent mail confiormaton to user for auto debit
    public function requestRecurrentDebitApproval($solar_reni_token, $amount, $frequency, $length, $usertoken, $recurrent_type) {

        $getUserAccountNumber = [
            'usertoken' => $solar_reni_token,
            'amount' => $amount,
            'frequency' => $frequency,
            'length' => $length,
        ];

        $url = $_ENV['RENI_SANDBOX'] . '/createAutoDebit';

        $connectToReniTrust = $this->connectToReniTrust($getUserAccountNumber, $url);

        $encodedMailResponse = json_decode($connectToReniTrust, true);

        if ($encodedMailResponse !== null) {
            # Save user agreement recurrent to database
            $agreementToken = $encodedMailResponse['data']['data']['token'];

            $this->saveRecurrentDebitApproval($usertoken, $agreementToken, $recurrent_type);

            // Return true and the token
            return ['success' => true, 'agreementtoken' => $agreementToken];
        } else {
            // Return false
            return ['success' => false];
        }
    }

    #saveRecurrentDebitApproval: :This nmethods saves recurrent debit data
    public function saveRecurrentDebitApproval($usertoken, $reni_debit_token, $recurrent_type) {

        $db = new Database();
        $time = time();
        try {
            $sql = 'INSERT INTO current_autodebit_approval (usertoken, reni_debit_token, recurrent_type,time) VALUES (:usertoken, :reni_debit_token, :recurrent_type, :time)';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':usertoken', $usertoken);
            $stmt->bindParam(':reni_debit_token', $reni_debit_token);
            $stmt->bindParam(':recurrent_type', $recurrent_type);
            $stmt->bindParam(':time', $time);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            $this->respondWithInternalError('Error: ' . $e->getMessage());
            return false;
        } finally {
            $stmt = null;
            $db = null;
        }

    }

##mergePaymentHistory:: This method merges payment history of reni transaction record and the platform payment
    public function mergePaymentHistoryIntoMonths($months, $history) {

        // Create a copy of $months to avoid modifying the original array
        $modifiedMonths = $months;

        foreach ($modifiedMonths as &$month) {
            $dueDateParts = explode(" ", $month['dueDate']);
            $monthNumber = date('m', strtotime($dueDateParts[1]));

            $historyFound = false; // Flag to check if history is found for the month

            foreach ($history as $historyItem) {
                if (
                    (int) $historyItem['day'] === (int) $dueDateParts[0] &&
                    $historyItem['month'] === $dueDateParts[1] &&
                    (int) $historyItem['year'] === (int) $dueDateParts[2]
                ) {
                    // Merge data if the dates match
                    $month['historyData'] = [
                        "id" => $historyItem['id'],
                        "usertoken" => $historyItem['usertoken'],
                        "token" => $historyItem['token'],
                        "status" => $historyItem['status'],
                        "amount" => $historyItem['amount'],
                        "timestamp" => $historyItem['timestamp'],
                        "deleted" => $historyItem['deleted'],
                    ];

                    $historyFound = true; // Set the flag to true
                }
            }

            // If no history is found, add a status to the month data
            if (!$historyFound) {
                $month['historyData'] = "No payment history available";
            }
        }

        // Return the modified copy of $months
        return $modifiedMonths;

    }

    #getPaymentHistory:: This method fetches a user payment history from renitrust
    public function getPaymentHistory($renitoken, $reni_debit_token) {

        $requestData = [
            'usertoken' => $renitoken,
            'autoDebitToken' => $reni_debit_token,
        ];

        $url = $_ENV['RENI_SANDBOX'] . '/checkAutoDebit';

        // Call the connectToReniTrust function with the collected user IDs
        $connectToReniTrust = $this->connectToReniTrust($requestData, $url);

        $decode = json_decode($connectToReniTrust, true);

        return $decode;

    }

    public function outputData($success = null, $message = null, $data = null) {

        $arr_output = array(
            'success' => $success,
            'message' => $message,
            'data' => $data,
        );
        echo json_encode($arr_output);
    }

}