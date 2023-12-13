<?php

class Loan extends AbstractClasses {

    private $conn;

    public function __construct(Database $database) {

        $this->conn = $database->connect();
    }

    #saveGuarantorInfo:: This method creates Loan providers information

    public function createLoanProvider(array $data) {

        #  Prepare the fields and values for the gurantor query
        $guarantorfields = [
            'name' => $data['name'],
            'image' => $data['image'],
            'time' => time(),
            'interest_rate' => $data['interest_rate'],

        ];

        if (!$this->isProviderExists($data['name'])) {
            return $this->outputData(false, 'Provider Already Exists', null);
        }

        # Build the SQL query
        $placeholders = implode(', ', array_fill(0, count($guarantorfields), '?'));
        $columns = implode(', ', array_keys($guarantorfields));
        $sql = "INSERT INTO tblloanprovider ($columns) VALUES ($placeholders)";

        #  Execute the query and handle any errors
        try {
            // $this->conn->beginTransaction();

            $stmt = $this->conn->prepare($sql);
            $i = 1;
            foreach ($guarantorfields as $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($i, $value, $type);
                $i++;
            }
            $stmt->execute();

            return $this->outputData(true, 'Provider Added!', null);

        } catch (PDOException $e) {
            $_SESSION['err'] = 'Error:' . $e->getMessage();
            // $this->conn->rollback();

        } finally {
            $stmt = null;
            $this->conn = null;

        }

    }

    #hasVerifiedNextOfKin::This method checks if user has verified NextOfKin

    public function isProviderExists(string $loan_name) {
        try {
            $sql = 'SELECT name FROM tblloanprovider WHERE
             name = :name  ';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $loan_name, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                throw new Exception('Failed to execute query');
            }
            if ($stmt->rowCount() > 0) {
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            $this->respondWithInternalError('Error: ' . $e->getMessage());
        } finally {
            $stmt = null;

        }

    }

    # getAllLoanProviders: This method fetches all loan providers related to loan requests
    public function getaAllLoanProviders() {
        $dataArray = array();
        $sql = 'SELECT * FROM tblloanprovider ORDER BY id DESC';

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $count = $stmt->rowCount();

            if ($count > 0) {
                $loanProviders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($loanProviders as $provider) {
                    $array = [
                        'providerid' => $provider['id'],
                        'providername' => $provider['name'],
                        'provider_interest_rate' => $provider['interest_rate'],
                    ];
                    array_push($dataArray, $array);
                }
            } else {
                $this->outputData(false, 'No records found', null);
            }
        } catch (PDOException $e) {
            return $this->outputData(false, 'Error getting provider data: ' . $e->getMessage(), null);
        } finally {
            $stmt = null;
        }

        return $dataArray;
    }

/**
 * getProviderInfoByID: This method fetches information about a specific loan provider by their ID.
 *
 * @param int $providerid The ID of the loan provider to retrieve information for.
 * @return array An array containing the provider's information if found; otherwise, an error message.
 */
    public function getProviderInfoByID(int $providerid) {
        $dataArray = array();
        $sql = 'SELECT id, name FROM tblloanprovider WHERE id = :id';

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $providerid, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->rowCount();

            if ($count > 0) {
                $loanProviders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($loanProviders as $provider) {
                    $array = [
                        'providerid' => $provider['id'],
                        'providername' => $provider['name'],
                    ];
                    array_push($dataArray, $array);
                }
            } else {
                return $this->outputData(false, 'No records found', null);
            }
        } catch (PDOException $e) {
            return $this->outputData(false, 'Error getting provider data: ' . $e->getMessage(), null);
        } finally {
            $stmt = null;
        }

        return $dataArray;
    }

    # checkEligibilityStatus: This method checks the eligibility status of a user.
    public function checkEligibilityStatus($usertoken) {
        $sql = 'SELECT usertoken, isCompletedStatus FROM loan_records WHERE usertoken = :usertoken
    AND  isCompletedStatus = 0';

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usertoken', $usertoken);
            $stmt->execute();
            $count = $stmt->rowCount();

            if ($count > 0) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return $this->outputData(false, 'Error getting eligibility data: ' . $e->getMessage(), null);
        } finally {
            $stmt = null;
        }
    }

    public function CreateUserNextOfKiN(array $data) {

        #checkVeridicationMeans.::This checks if userhas already done KYC.
        if (!$this->hasVerifiedNextOfKin($data['usertoken'])) {
            $this->outputData(false, 'Account has already been verified', null);
            exit;
        }

        $reniPayLoad = [
            'usertoken' => $data['renitoken'],
            'bvn' => $data['identity_num'],
        ];

        if ($data['means_of_iden'] === "bvn") {
            $sendReniKYC = $this->updateUserBVN($reniPayLoad);

            if (!$sendReniKYC['success']) {
                return $this->outputData(false, $sendReniKYC['message'], $sendReniKYC['data']);
                exit();
            }

        } else {
            // Handle NIN or other cases
            $sendReniKYC = $this->updateUserBVN($reniPayLoad);

            if (!$sendReniKYC['success']) {
                return $this->outputData(false, $sendReniKYC['message'], $sendReniKYC['data']);
                exit();
            }
        }

        # Prepare the fields and values for the insert query

        $imageUrl = $_ENV['IMAGE_PATH'] . "/$data[photo]";

        $fields = [
            'fname' => $data['fname'],
            'usertoken' => $data['usertoken'],
            'occupation' => $data['occupation'],
            'means_of_iden' => $data['means_of_iden'],
            'identity_num' => $data['identity_num'],
            'address' => $data['address'],
            'photo' => ($data['photo']),
            'imageUrl' => $imageUrl,

        ];
        # Build the SQL query
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $columns = implode(', ', array_keys($fields));
        $sql = "INSERT INTO tblkin ($columns) VALUES ($placeholders)";

        #  Execute the query and handle any errors
        try {
            $stmt = $this->conn->prepare($sql);
            $i = 1;
            foreach ($fields as $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($i, $value, $type);
                $i++;
            }
            $stmt->execute();

            http_response_code(201);
            $output = $this->outputData(true, 'Registration successful!', null);
            exit;
        } catch (PDOException $e) {

            $output = $this->respondWithInternalError('Error: ' . $e->getMessage());
            exit;
        } finally {
            $stmt = null;
            $this->conn = null;

        }

        return $output;

    }

#checkAndHandleBVN:: This method validates a user

    public function checkAndHandleBVN($data) {
        $reniPayLoad = [
            'usertoken' => $data['usertoken'],
            'bvn' => $data['identity_num'],
        ];

        $sendReniKYC = $this->updateUserBVN($reniPayLoad);

        if (!$sendReniKYC['success']) {
            return $this->outputData(false, $sendReniKYC['message'], $sendReniKYC['data']);
            exit("die here");
        }

        // You can return additional information or success status if needed.
        //coutinue
    }

    #checkAndHandleBVN:: This method validates a user
    public function checkAndHandleNIN($data) {
        $reniPayLoad = [
            'usertoken' => $data['usertoken'],
            'nin' => $data['identity_num'],
        ];

        $sendReniKYC = $this->updateUserBVN($reniPayLoad);

        if (!$sendReniKYC['success']) {
            return $this->outputData(false, $sendReniKYC['message'], $sendReniKYC['data']);
            exit;
        }

        // You can return additional information or success status if needed.
        //coutinue
    }

    #hasVerifiedNextOfKin::This method checks if user has verified NextOfKin

    public function hasVerifiedNextOfKin(string $usertoken) {
        try {
            $sql = 'SELECT usertoken FROM tblkin WHERE
             usertoken = :usertoken  ';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usertoken', $usertoken, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                throw new Exception('Failed to execute query');
            }
            if ($stmt->rowCount() > 0) {
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            $this->respondWithInternalError('Error: ' . $e->getMessage());
        } finally {
            $stmt = null;

        }

    }

    #initiateLoanRequest:: This method handles Loan requests On the platform...

    public function initiateLoanRequest(array $data) {
        $token = intval($this->token());

        // $checkEligibilityStatus = $this->checkEligibilityStatus($data['usertoken']);
        // if ($checkEligibilityStatus) {
        //     return $this->outputData(false, "Cannot process request due to ongoing Transaction", null);
        // }
        # Prepare to check for loan-Plan value
        $checkSubscribedPlan = $this->checkSubscribedPlan(intval($data['plan']));

        if (!$checkSubscribedPlan) {
            $this->outputData(false, $_SESSION['err'], null);
            exit;
        }

        # Prepare to check for provider intrest rate per
        $getInterestRate = $this->getInterestRate(intval($data['providerid']));

        if (!$getInterestRate) {
            $this->outputData(false, $_SESSION['err'], null);
            exit;
        }

        #Get the applicant info
        $getUserData = $this->getUserData($data['usertoken']);

        #calculateLoanInterest :: Calculate provider interest rate
        $calculateLoanInterest = $this->calculateLoanInterest($data['amountToBorrow'], $getInterestRate['provider_rate']);

        $totalLoanAmount = $calculateLoanInterest + $data['amountToBorrow'];

        # Calculates the estimated total days for paying off a loan based on the subscribed months.
        $calculateDateDifferenceInDays = $this->calculateDateDifferenceInDays($checkSubscribedPlan['planid']);

        # Get estimated month for paying back loan
        $estimateTotalMonthsForLoanRepayment = $this->estimateTotalMonthsForLoanRepayment($checkSubscribedPlan['planid']);

        #Reuest for autodebit:: In case the loan application is successful  auto debit a user account
        $requestRecurrentApproval = $this->requestRecurrentDebitApproval($getUserData['renitoken'], $totalLoanAmount,
            $calculateDateDifferenceInDays, $estimateTotalMonthsForLoanRepayment, $data['usertoken'], "LOAN_INSTALLMENT");

        if (!$requestRecurrentApproval['success']) {
            return $this->outputData(false, "Unable to process request, Please try again later", null);
        }

        # Prepare the fields and values for the Loan query

        $imageUrl = $_ENV['IMAGE_PATH'] . "/$data[passport]";

        $fields = [
            'plan' => intval($checkSubscribedPlan['planid']),
            'fname' => $getUserData['fname'],
            'amountToBorrow' => $data['amountToBorrow'],
            'total_amount' => $totalLoanAmount,
            'usertoken' => $data['usertoken'],
            'means_of_identity' => $data['means_of_identity'] ?? "Null",
            'identity_number' => $data['identity_number'] ?? "Null",
            'occupation' => ($getUserData['occupation']),
            'passport_photo' => $data['passport'],
            'purpose_of_loan' => $data['purpose_of_loan'],
            'token' => $token,
            'imageUrl' => $imageUrl,
            'interest_amount' => $calculateLoanInterest,
            'time' => time(),
            'providerid' => intval($data['providerid']),
            'auto_debit_approval_token' => ($requestRecurrentApproval['agreementtoken']),
        ];

        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $columns = implode(', ', array_keys($fields));
        $loanQuery = "INSERT INTO loan_records ($columns) VALUES ($placeholders)";

        # Execute the query and handle any errors
        try {
            $stmt = $this->conn->prepare($loanQuery);
            $i = 1;
            foreach ($fields as $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($i, $value, $type);
                $i++;
            }
            $stmt->execute();

            # Prepare the process  for the collateral query
            $collateral = $data['collecteral'][0];
            $saveColleteralInfo = $this->saveColleteralInfo($collateral, $token, $data['usertoken']);
            if (!$saveColleteralInfo) {
                $this->outputData(false, $_SESSION['err'], null);
                exit;
            }

            # Prepare the process  for the Guarantor query
            $saveGuarantorInfo = $this->saveGuarantorInfo($data['guarantor'], $token);
            if (!$saveGuarantorInfo) {
                $this->outputData(false, $_SESSION['err'], null);
                exit;
            }

            #Prepare the  process for calculating MonthlyRepayment
            $monthlyRepayment = $this->calculateMonthlyRepayment($totalLoanAmount, $checkSubscribedPlan['planid']);

            #calculateMonthsAheadAndSave::This method Calculate When User is expected to pay back..
            $calculateMonthsAhead = $this->calculateMonthsAheadAndSave($checkSubscribedPlan['planid'], $data['usertoken'], $token, $monthlyRepayment);
            if (!$calculateMonthsAhead) {
                $this->outputData(false, $_SESSION['err'], null);
                exit;
            }

            $mailer = new Mailer();

            $mailer->sendLoanNotificationToAdmin($getUserData['fname']);

            http_response_code(201);
            $output = $this->outputData(true, 'Loan application received. We will notify you upon approval', null);
            exit;
        } catch (PDOException $e) {
            $output = $this->respondWithInternalError('Error: ' . $e->getMessage());
            exit;
        } finally {
            $stmt = null;
            $this->conn = null;
            unset($mailer);

        }

        return $output;

    }

    #saveGuarantorInfo:: This method saves guarantor info during loan Process

    public function saveGuarantorInfo(array $guarantor, int $loantoken) {

        $isGuarantorSaved = false;

        foreach ($guarantor as $guarantorInfo) {

            #  Prepare the fields and values for the gurantor query
            $guarantorfields = [
                'name' => $guarantorInfo['name'],
                'phone' => $guarantorInfo['phone'],
                'email' => $guarantorInfo['email'],
                'phone' => $guarantorInfo['phone'],
                'token' => $loantoken,
                'relationship' => $guarantorInfo['relationship'],

            ];

            # Build the SQL query
            $placeholders = implode(', ', array_fill(0, count($guarantorfields), '?'));
            $columns = implode(', ', array_keys($guarantorfields));
            $sql = "INSERT INTO tblguarantor ($columns) VALUES ($placeholders)";

            #  Execute the query and handle any errors
            try {
                // $this->conn->beginTransaction();

                $stmt = $this->conn->prepare($sql);
                $i = 1;
                foreach ($guarantorfields as $value) {
                    $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                    $stmt->bindValue($i, $value, $type);
                    $i++;
                }
                $stmt->execute();

                $isGuarantorSaved = true;

            } catch (PDOException $e) {
                $_SESSION['err'] = 'Error:' . $e->getMessage();
                // $this->conn->rollback();
                $isGuarantorSaved = false;

            } finally {
                $stmt = null;

            }

        }
        return $isGuarantorSaved;
    }

    #saveColleteralInfo:: This method saves colleteral  info during loan Process

    public function saveColleteralInfo(array $collateral, int $loantoken, int $usertoken) {

        $isColleteralSaved = false;

        #  Prepare the fields and values for the collecteral query

        $imageUrl = $_ENV['IMAGE_PATH'] . "/$collateral[proof_of_ownership]";

        $collateralFields = [
            'category' => intval($collateral['category']) ?? "null",
            'usertoken' => $usertoken,
            'years_of_usage' => $collateral['years_of_usage'],
            'proof_of_ownership' => $collateral['proof_of_ownership'],
            'watt' => $collateral['watts'],
            'price_bought' => $collateral['price_bought'],
            'token' => $loantoken,
            'imageUrl' => $imageUrl,
        ];
        # Build the SQL query
        $collateralPlaceholders = implode(', ', array_fill(0, count($collateralFields), '?'));
        $collateralColumns = implode(', ', array_keys($collateralFields));
        $collateralQuery = "INSERT INTO tblcollecteral ($collateralColumns) VALUES ($collateralPlaceholders)";

        #  Execute the query and handle any errors
        try {

            $stmt = $this->conn->prepare($collateralQuery);
            $i = 1;
            foreach ($collateralFields as $collateralValue) {
                $type = is_int($collateralValue) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($i, $collateralValue, $type);
                $i++;
            }
            $stmt->execute();

            $isColleteralSaved = true;

        } catch (PDOException $e) {
            $_SESSION['err'] = 'Error:' . $e->getMessage();
            $isColleteralSaved = false;

        } finally {
            $stmt = null;

        }
        return $isColleteralSaved;

    }

    #calculateLoanInterest ::CalculateLoanInterest
    public function calculateLoanInterest($loanAmount, $provider_intrest) {

        $removePercentage = str_replace('%', '', $provider_intrest);
        $interestAmount = $loanAmount * ($removePercentage / 100);
        return $interestAmount;

    }

    # calculateLoanDueDate::This method is responsible for storing the start and end dates of a loan.

    public function calculateLoanDueDate(array $data, int $loantoken, int $usertoken) {
        $isCalculatedDate = false;
        #  Prepare the fields and values for the insert query
        $fields = [
            'tbloan_token' => $loantoken,
            'usertoken' => $usertoken,
            'month' => $data['month'],
            'priceToPay' => $data['priceToPay'],
            'remind_a_week_before' => $data['remind_a_week_before'],

        ];

        # Build the SQL query
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $columns = implode(', ', array_keys($fields));
        $sql = "INSERT INTO loan_repayments ($columns) VALUES ($placeholders)";

        #  Execute the query and handle any errors
        try {
            $stmt = $this->conn->prepare($sql);
            $i = 1;
            foreach ($fields as $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($i, $value, $type);
                $i++;
            }
            $stmt->execute();

            $isCalculatedDate = true;

            exit;
        } catch (PDOException $e) {

            $this->respondWithInternalError('Error: ' . $e->getMessage());
            $isCalculatedDate = false;
            exit;
        } finally {
            $stmt = null;
            // $this->conn = null;

        }

        return $isCalculatedDate;
    }

    # TcalculateMonthsAhead:: This function calculates the month(s) ahead for a given subscribed package
    public function calculateMonthsAheadAndSave(int $subscribedPackage, int $usertoken, int $loantoken, string $repaymentPerMonth) {

        $startDate = new DateTime();

        for ($i = 0; $i < $subscribedPackage; $i++) {
            $estimatedDate = $startDate->modify('+1 month');
            $monthExpectedToPay = $estimatedDate->format('Y-m-d');

            # Calculate a week before due date.
            $remindAWeekBefore = date("Y-m-d", strtotime("-7 days", strtotime($monthExpectedToPay)));

            # Calculate a day before due date.
            $remindADayBefore = date("Y-m-d", strtotime("-1 day", strtotime($monthExpectedToPay)));

            # Calculate 3 day before due date.
            $remindThreeDaysBefore = date("Y-m-d", strtotime("-3 days", strtotime($monthExpectedToPay)));

            # Store month( s ) to pay And Amount To Pay.
            $saveWhenExpectedToPay = $this->saveWhenExpectedToPay($monthExpectedToPay, $usertoken,
                $loantoken, ceil($repaymentPerMonth), $remindADayBefore, $remindAWeekBefore, $remindThreeDaysBefore);
            if (!$saveWhenExpectedToPay) {
                $this->outputData(false, $_SESSION['err'], null);
                return false;
            }

        }

        return true;
    }

    #saveWhenExpectedToPay:: This method saves month(s) Expected to pay back
    public function saveWhenExpectedToPay($monthExpectedToPay, int $usertoken,
        int $loantoken, string $repaymentPerMonth, $remindADayBefore, $remindAWeekBefore, $remindThreeDaysBefore
    ) {

        $sql = "INSERT INTO loan_repayments (tbloan_token, usertoken, month, a_day_before, a_week_before, 3_days_before, priceToPay)
        VALUES (:tbloan_token, :usertoken, :month, :a_day_before, :a_week_before, :3_days_before, :priceToPay)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':tbloan_token', $loantoken);
        $stmt->bindParam(':usertoken', $usertoken);
        $stmt->bindParam(':month', $monthExpectedToPay);
        $stmt->bindParam(':a_day_before', $remindADayBefore);
        $stmt->bindParam(':a_week_before', $remindAWeekBefore);
        $stmt->bindParam(':3_days_before', $remindThreeDaysBefore);
        $stmt->bindParam(':priceToPay', $repaymentPerMonth);

        #  Execute statement and return result
        try {
            if (!$stmt->execute()) {
                $_SESSION['err'] = "Unable to save repayment plan";
                return false;
            }

            return true;
        } catch (Exception $e) {
            $_SESSION['err'] = $e->getMessage();
            $this->respondWithInternalError(false, $_SESSION['err'], null);
            return false;
        } finally {
            $stmt = null;
        }

    }

#getProviderInfo ::This method fetches are

    public function getAllLoanProvider() {
        $dataArray = array();

        $sql = 'SELECT * FROM tblloanprovider ORDER BY id DESC';

        try {
            $stmt = $this->conn->query($sql);
            $stmt->execute();
            $count = $stmt->rowCount();

            if ($count === 0) {
                return false;
                exit;
            }

            $getAllProvider = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($getAllProvider as $allprovider) {

                $array = [
                    'id' => $allprovider['id'],
                    'providername' => $allprovider['name'],
                    'providerimage' => $allprovider['image'],
                    'interest_rate' => $allprovider['interest_rate'],
                ];

                array_push($dataArray, $array);

            }

        } catch (PDOException $e) {
            return $this->outputData(false, "Error while retrieving loan provider:" . $e->getMessage(), null);
            exit;
        } finally {
            $stmt = null;
            $this->conn = null;
        }

        return $dataArray;

    }

    #getSingleLoanRecord::This method fetches a single loan by token
    public function getSingleLoanRecord($loantoken) {
        $dataArray = array();

        $sql = 'SELECT * FROM loan_records WHERE token = :loantoken';

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':loantoken', $loantoken);
            $stmt->execute();

            $loanRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($loanRecords)) {
                return false;
            }

            foreach ($loanRecords as $allLoanRecords) {
                // Extract additional data
                $verifyNextOfKin = $this->verifyNextOfKin($allLoanRecords['usertoken']);
                $getAllLoanGuarantors = $this->getAllLoanGuarantors($allLoanRecords['token']);
                $getAllLoanColleterals = $this->getAllLoanColleterals($allLoanRecords['token']);
                 $getAllRecordsOfMonthExpectedToPay = $this->getAllRecordsOfMonthExpectedToPay($allLoanRecords['token']);
                $getProviderDetails = $this->getProviderInfoByID($allLoanRecords['providerid']);
                $getUserData = $this->getUserdata($allLoanRecords['usertoken']);
                $getPaymentHistory = $this->getPaymentHistory($getUserData['renitoken'], $allLoanRecords['auto_debit_approval_token']);
                 $mergePaymentHistoryIntoMonths = $this->mergePaymentHistoryIntoMonths($getAllRecordsOfMonthExpectedToPay, $getPaymentHistory['data']['history']);

                // Determine loan status and completion status
                $loanStatus = ($allLoanRecords['status'] == 1) ? 'Approved' : (($allLoanRecords['status'] == 2) ? 'Declined' : 'Pending');
                $isCompletedStatus = ($loanStatus === "Pending") ? 'Waiting for approval' : ($allLoanRecords['isCompletedStatus'] == 1 ? 'Payment completed.' : 'Payment-ongoing.');

                // Prepare the data array
                $array = [
                    'packagePlan' => ($allLoanRecords['plan'] > 1) ? ($allLoanRecords['plan'] . ' months') : ($allLoanRecords['plan'] . ' month'),
                    'fullname' => $allLoanRecords['fname'],
                    'amountToBorrow' => $this->formatCurrency($allLoanRecords['amountToBorrow']),
                    'loanStatus' => $loanStatus,
                    'isCompletedStatus' => $isCompletedStatus,
                    'usertoken' => $allLoanRecords['usertoken'],
                    'amount_debited_so_far' => $this->formatCurrency($allLoanRecords['amount_debited_so_far']),
                    'means_of_identity' => $allLoanRecords['means_of_identity'],
                    'identity_number' => $allLoanRecords['identity_number'],
                    'occupation' => $allLoanRecords['occupation'],
                    'passport_photo' => $allLoanRecords['imageUrl'],
                    'purpose_of_loan' => $allLoanRecords['purpose_of_loan'],
                    'loantoken' => $allLoanRecords['token'],
                    'nextOfKin' => $verifyNextOfKin,
                    'RequestedOn' => $this->formatDate($allLoanRecords['time']),
                    'getAllLoanGuarantors' => $getAllLoanGuarantors,
                    'getAllLoanColleterals' => $getAllLoanColleterals,
                    'getAllRecordsOfMonthExpectedToPay' => $mergePaymentHistoryIntoMonths,
                    'preferredProvider' => $getProviderDetails,
                ];

                array_push($dataArray, $array);
            }
        } catch (PDOException $e) {
            return $this->outputData(false, "Error while retrieving loan:" . $e->getMessage(), null);
            exit;
        } finally {
            $stmt = null;
            $this->conn = null;
        }

        return $dataArray;
    }

#getAllLoanRecord::This method fetches all Loan records inthe databse

    public function getAllLoanRecord() {
        $dataArray = array();

        $sql = 'SELECT * FROM loan_records ORDER BY status = 0 DESC';

        try {
            $stmt = $this->conn->query($sql);
            $stmt->execute();
            $count = $stmt->rowCount();

            $loanRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($loanRecords as $allLoanRecords) {
                $loanStatus = ($allLoanRecords['status'] == 1) ? 'Approved' : (($allLoanRecords['status'] == 2) ? 'Declined' : 'Pending');
                $isCompletedStatus = ($loanStatus === "Pending") ? 'Waiting for approval' : ($allLoanRecords['isCompletedStatus'] == 1 ? 'Payment completed.' : 'Payment-ongoing.');
                $array = [
                    'packagePlan' => ($allLoanRecords['plan'] > 1) ? ($allLoanRecords['plan'] . ' months') : ($allLoanRecords['plan'] . ' month'),
                    'fullname' => $allLoanRecords['fname'],
                    'amountToBorrow' => $this->formatCurrency($allLoanRecords['amountToBorrow']),
                    'amount_plus_intrest' => $this->formatCurrency($allLoanRecords['total_amount']),
                    'loanStatus' => $loanStatus,
                    'isCompletedStatus' => $isCompletedStatus,
                    'usertoken' => $allLoanRecords['usertoken'],
                    'occupation' => $allLoanRecords['occupation'],
                    'passport_photo' => $allLoanRecords['imageUrl'],
                    'loantoken' => $allLoanRecords['token'],
                    'purpose_of_loan' => $allLoanRecords['purpose_of_loan'],
                    'RequestedOn' => $this->formatDate($allLoanRecords['time']),

                ];
                array_push($dataArray, $array);
            }
        } catch (PDOException $e) {
            return $this->outputData(false, "Error while retrieving loan:" . $e->getMessage(), null);
            exit;
        } finally {
            $stmt = null;
            $this->conn = null;
        }

        return $dataArray;
    }

#getAllUserLoanRecord::This method fetches all Loan records related to  user
    public function getAllUserLoanRecord(int $usertoken) {
        $dataArray = array();
        $sql = "SELECT * FROM loan_records WHERE usertoken = :usertoken ORDER BY status = 0";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usertoken', $usertoken);
            $stmt->execute();
            $count = $stmt->rowCount();
            if ($count === 0) {
                $_SESSION['err'] = "No record found";
                return false;
            }
            $loanRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($loanRecords as $allLoanRecords) {
                $loanStatus = ($allLoanRecords['status'] == 1) ? 'Approved' : (($allLoanRecords['status'] == 2) ? 'Declined' : 'Pending');
                $isCompletedStatus = ($loanStatus === "Pending") ? 'Waiting for approval' : ($allLoanRecords['isCompletedStatus'] == 1 ? 'Payment completed.' : 'Payment-ongoing.');
                $array = [
                    'packagePlan' => ($allLoanRecords['plan'] > 1) ? ($allLoanRecords['plan'] . ' months') : ($allLoanRecords['plan'] . ' month'),
                    'fullname' => $allLoanRecords['fname'],
                    'amountToBorrow' => $this->formatCurrency($allLoanRecords['amountToBorrow']),
                    'amount_plus_intrest' => $this->formatCurrency($allLoanRecords['total_amount']),
                    'loanStatus' => $loanStatus,
                    'isCompletedStatus' => $isCompletedStatus,
                    'usertoken' => $allLoanRecords['usertoken'],
                    'occupation' => $allLoanRecords['occupation'],
                    'passport_photo' => $allLoanRecords['imageUrl'],
                    'loantoken' => $allLoanRecords['token'],
                    'purpose_of_loan' => $allLoanRecords['purpose_of_loan'],
                    'RequestedOn' => $this->formatDate($allLoanRecords['time']),

                ];

                array_push($dataArray, $array);
            }

        } catch (PDOException $e) {
            $_SESSION['err'] = "Error while retreiving loan:" . $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            $this->conn = null;
        }
        return $dataArray;
    }

#getAllLoanGuarantors ::This method fetches All guarantors related in a loan requests
    public function getAllLoanGuarantors(int $loantoken) {
        $dataArray = array();
        $sql = 'SELECT * FROM tblguarantor WHERE token = :loantoken ORDER BY id DESC';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':loantoken', $loantoken);
            $stmt->execute();
            $count = $stmt->rowCount();
            if ($count > 0) {
                $guarantors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($guarantors as $allGuarantors) {
                    $array = [
                        'guarantorName' => $allGuarantors['name'],
                        'guarantorEmail' => $allGuarantors['email'],
                        'guarantorPhone' => $allGuarantors['phone'],
                        'relationship' => $allGuarantors['relationship'],
                    ];
                    array_push($dataArray, $array);
                }
            } else {
                $this->outputData(false, 'No record found', null);
            }
        } catch (PDOException $e) {
            $this->respondWithInternalError(false, 'Error getting loan guarantor: ' . $e->getMessage(), null);
            return false;
        } finally {
            $stmt = null;

        }
        return $dataArray;
    }

# getAllRecordsOfMonthExpectedToPay ::This method fetches All months expected to repay LOAN
    public function getAllRecordsOfMonthExpectedToPay(int $loantoken) {
        $dataArray = array();
        try {
            $sql = 'SELECT month, priceToPay, tbloan_token,
        payyment_status,  penalty, remind_a_day_before_status, remind_3_days_before_status ,
        remind_a_week_before_status FROM loan_repayments WHERE tbloan_token = :token';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':token', $loantoken);
            $stmt->execute();
            $expectedMonths = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($expectedMonths as $listExpectedMonths) {
                $array = [
                    'dueDate' => $this->formateLoanDate($listExpectedMonths['month']),
                    'priceExpectedToPay' => $this->formatCurrency($listExpectedMonths['priceToPay']),
                    'penaltyFee' => $listExpectedMonths['penalty'],
                    'paymentStatus' => ($listExpectedMonths['payyment_status'] == 1) ? 'Paid' : (($listExpectedMonths['payyment_status'] == 2) ? 'Not-paid' : 'Payment-pending'),
                    'remind_a_day_before_status' => $listExpectedMonths['remind_a_day_before_status'] === 1 ? "Sent" : "Pending",
                    'remind_a_week_before_status' => $listExpectedMonths['remind_a_week_before_status'] === 1 ? "Sent" : "Pending",
                    'remind_2days_before_status' => $listExpectedMonths['remind_3_days_before_status'] === 1 ? "Sent" : "Pending",
                ];

                array_push($dataArray, $array);
            }

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            // $this->respondWithInternalError(false, "Error: " . $e->getMessage(), null);
            exit;
            return false;
        } finally {
            $stmt = null;
        }
        return $dataArray;
    }

    public function formateLoanDate($dateString) {
        $dateTime = DateTime::createFromFormat('Y-d-m', $dateString);

        if ($dateTime) {
            return $dateTime->format('d F Y');
        } else {
            return 'Invalid date format';
        }
    }

#getAllLoanColleterals ::This method fetches all Colletcteral related to a loan request.
    public function getAllLoanColleterals(int $loantoken) {
        try {
            $sql = 'SELECT * FROM tblcollecteral WHERE token = :token';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':token', $loantoken);
            $stmt->execute();
            $collaterals = $stmt->fetch(PDO::FETCH_ASSOC);
            $getCategory = $this->getProductCategory($collaterals['category']);

            $array = [
                'catname' => $getCategory['catname'] ?? null,
                'years_of_usage' => $collaterals['years_of_usage'],
                # 'means_of_proof' => $collaterals['means_of_proof'],
                'proof_of_ownership' => ($collaterals['imageUrl']),
                'watt' => $collaterals['watt'],
                'price_bought' => $this->formatCurrency($collaterals['price_bought']),
            ];

        } catch (PDOException $e) {
            $this->respondWithInternalError(false, "Error: " . $e->getMessage(), null);
            exit;
            return false;
        } finally {
            $stmt = null;
        }
        return $array;
    }

#approveLoanRequest::This method approves loan requests.
    public function approveLoanRequest($usertoken, $loanToken) {
        $status = 1;

        $getUserData = $this->getUserData($usertoken);

        try {
            if (!$this->updateLoanRecordStatus($usertoken, $loanToken, $status)) {
                $this->outputData(false, $_SESSION['err'], null);
                exit;

            }

            $mailer = new Mailer();
            $mailer->notifyLoanApproval($getUserData['mail'], $getUserData['fname']);
            $_SESSION['err'] = "Approved";

        } catch (Exception $e) {
            $_SESSION['err'] = "Unable to process Loan requests:" . $e->getMessage();
            return false;
            exit;
        } finally {
            #Free nessacry resources.
            unset($mailer);
            unset($status);
        }
        return true;
    }

#disapproveLoanRequest::This method disapproveLoanRequest a  loan requests.
    public function disapproveLoanRequest($usertoken, $loanToken, $message) {
        $status = 2;

        $getUserData = $this->getUserData($usertoken);

        try {
            if (!$this->updateLoanRecordStatus($usertoken, $loanToken, $status)) {
                $this->outputData(false, $_SESSION['err'], null);
                exit;

            }

            $mailer = new Mailer();
            $mailer->notifyLoanDisapproval($getUserData['mail'], $getUserData['fname'], $message);
            $_SESSION['err'] = "Declined";

        } catch (Exception $e) {
            $_SESSION['err'] = "Unable to process Loan requests:" . $e->getMessage();
            return false;
            exit;
        } finally {
            #Free nessacry resources.
            unset($mailer);
            unset($status);
        }
        return true;
    }

    #updateLoanRecordStatus::This method updates the status of  aloan request.
    public function updateLoanRecordStatus(int $userToken, int $loanToken, int $status) {
        try {
            $sql = 'UPDATE loan_records SET status = :status WHERE usertoken = :userToken AND token = :loanToken';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':userToken', $userToken);
            $stmt->bindParam(':loanToken', $loanToken);
            $stmt->execute();
        } catch (PDOException $e) {
            $_SESSION['err'] = 'Error While updating status: ' . $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            $this->conn = null;
        }
        return true;
    }
// getAllRecordsOfMonthExpectedToPay

    #getPendingAutoDebits::This method fetches pending loans auto debit request
    public function getPendingAutoDebits() {
        $dataArray = array();
        $sql = 'SELECT * FROM current_autodebit_approval WHERE  is_verified = 0 LIMIT 10';
        $stmt = $this->conn->query($sql);

        try {
            $stmt->execute();
            $getPendingAutoDebits = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($getPendingAutoDebits as $pendindDebit) {
                $array = [
                    'usertoken' => $pendindDebit['usertoken'],
                    'reni_debit_token' => $pendindDebit['reni_debit_token'],
                    'recurrent_type' => $pendindDebit['recurrent_type'],
                    'is_verified' => $pendindDebit['is_verified'],
                ];

                array_push($dataArray, $array);
            }

        } catch (PDOException $e) {
            $_SESSION['err'] = "Unable to retrieve pending loan data" . $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            $this->conn = null;
        }
        return $dataArray;
    }

#checkRecurrentPayment::This method disapproveLoanRequest a  loan requests.
    public function checkRecurrentPayment() {
        $getPendingAutoDebits = $this->getPendingAutoDebits();

        if (empty($getPendingAutoDebits)) {
            return null;
        }

        $requestData = [];

        foreach ($getPendingAutoDebits as $debit) {
            // Validate input data
            $getUserData = $this->getUserData($debit['usertoken']);

            // echo json_encode($getUserData);
            // exit;

            $requestData = [
                'usertoken' => $getUserData['renitoken'],
                'autoDebitToken' => $debit['reni_debit_token'],
            ];

            $url = $_ENV['RENI_SANDBOX'] . '/checkAutoDebit';

            // Call the connectToReniTrust function with the collected user IDs
            $connectToReniTrust = $this->connectToReniTrust($requestData, $url);

            $decode = json_decode($connectToReniTrust);

            // $token = $decode['data']['token'];
            // $status = $decode['data']['status'];

            echo json_encode($decode);

        }

        // Process $connectToReniTrust further as needed
    }

}
