<?php

class Users extends AbstractClasses {

    private $conn;

    public function test() {

        echo "testing";
    }

    public function __construct(Database $database) {

        $this->conn = $database->connect();
    }

    public function registerUser(array $data) {

        $checkIfMailExists = $this->checkIfMailExists($data['mail']);
        if ($checkIfMailExists) {
            $output = $this->outputData(false, 'Email already exists', null);
            exit;
        }

        $reniPayload = [
            'mail' => $data['mail'],
            'firstname' => $data['fname'],
            'lastname' => $data['fname'],
            'phone' => $data['phone'],

        ];

        $newsLetter = new NewsLetter();

        try {
            $renitoken = $newsLetter->linkUserToReniTrust($reniPayload);

        } catch (\Throwable $e) {
            $errorMessage = date('[Y-m-d H:i:s] ') . 'An error occurred while processing feedback in ' . __METHOD__ . PHP_EOL . 'Error Message: ' . $e->getMessage() . ' at ' . $e->getFile() . ' on line ' . $e->getLine();
            error_log($errorMessage, 3, 'reni.log');
        }

        $token = (int) $this->token();
        $passHash = password_hash($data['pword'], PASSWORD_DEFAULT);
        #  Prepare the fields and values for the insert query
        $fields = [
            'fname' => $data['fname'],
            'mail' => $data['mail'],
            'phone' => $data['phone'],
            'pword' => $passHash,
            'abtUs' => $data['abtUs'],
            'occupation' => $data['occupation'],
            'usertoken' => $token,
            'otp' => $token,
            'userType' => 'Users',
            'time' => time(),
            'renitoken' => $renitoken,

        ];

        # Build the SQL query
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $columns = implode(', ', array_keys($fields));
        $sql = "INSERT INTO tblusers ($columns) VALUES ($placeholders)";

        #  Execute the query and handle any errors
        try {
            $stmt = $this->conn->prepare($sql);
            $i = 1;
            foreach ($fields as $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($i, $this->sanitizeInput($value), $type);
                $i++;
            }
            $stmt->execute();

            #Create wallet after registration #Feture depricated
            // $this->createWallet( $token );

            $mailer = new Mailer();

            if ($mailer->sendOTPToken($data['mail'], $data['fname'], $token)) {
                unset($mailer);
            }

            $userInfo = [
                'availableBalance_thousand' => 0.00,
            ];

            $dataArray = [
                'firstname' => $data['fname'],
                'lastname' => $data['fname'],
                'mail' => $data['mail'],
            ];

            try {
                $newsLetter->addMailToNewsLetter($dataArray);

            } catch (\Throwable $e) {
                $errorMessage = date('[Y-m-d H:i:s] ') . 'An error occurred while processing feedback in ' . __METHOD__ . PHP_EOL . 'Error Message: ' . $e->getMessage() . ' at ' . $e->getFile() . ' on line ' . $e->getLine();
                error_log($errorMessage, 3, 'reni.log');
            }

            $output = $this->outputData(true, 'Account created', $userInfo);
        } catch (PDOException $e) {

            $output = $this->respondWithInternalError('Error: ' . $e->getMessage());
        } finally {
            $this->conn = null;
            unset($mailer);
            unset($newsLetter);

        }

        return $output;
    }

    #Create wallet::Tgis method create wallet on the platform
    #This feature has been deprecated

    public function createWallet(int $usertoken) {
        $amount = '0.00';
        try {
            $sql = 'INSERT INTO tblwallet (usertoken, amount) VALUES (:usertoken, :amount)';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usertoken', $usertoken);
            $stmt->bindParam(':amount', $amount);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            $this->respondWithInternalError('Error: ' . $e->getMessage());
            return false;
        } finally {
            $stmt = null;
            $this->conn = null;
        }
    }

    # updateUserData function updates user biodata

    public function updateUserData(int $usertoken, array $data) {
        try {
            $updateQuery = 'UPDATE tblusers SET ';
            $params = array();
            foreach ($data as $key => $value) {
                if ($key !== 'apptoken') {

                    $updateQuery .= $key . ' = ?, ';
                    $params[] = $value;
                }
            }
            $updateQuery = rtrim($updateQuery, ', ') . ' WHERE usertoken = ?';
            $params[] = $usertoken;

            $stmt = $this->conn->prepare($updateQuery);
            $stmt->execute($params);

            $_SESSION['err'] = 'Record updated';
            return true;
        } catch (PDOException $e) {
            $_SESSION['err'] = $e->getMessage();
            $this->respondWithInternalError($_SESSION['err']);
            return null;
        } finally {
            $stmt = null;
            $this->conn = null;
        }
    }

    public function tryLogin($data) {

        try {
            $sql = 'SELECT * FROM tblusers WHERE mail = :mail';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':mail', $data['mail'], PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                $this->outputData(false, "Invalid credential.", null);
                exit;
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user['status'] != 1) {

                $unverifiedData = array(
                    'kycStatus' => $this->getkycStatus($user['usertoken']),
                    'regStatus' => ($user['status'] == 1) ? true : false,

                );
                $this->outputData(false, 'Account not verified.verify account by entering  OTP sent to yout mail', $unverifiedData);
                exit;
            }

            if (!password_verify($data['pword'], $user['pword'])) {
                $this->outputData(false, "Incorrect password for $data[mail]", null);
                exit;
            }
            $reni = new ReniPayment();

            if ($user) {

                $getStatus = $this->getkycStatus($user['usertoken']);
                $verifyNextOfKin = $this->verifyNextOfKin($user['usertoken']);
                $getAccountBalance = $reni->getUserFinacialDetails($user['renitoken']); // This method fetches user Account number and balance from renitrust
                $userData = [
                    'fname' => $user['fname'],
                    'mail' => $user['mail'],
                    'usertoken' => $user['usertoken'],
                    'phone' => $user['phone'],
                    'regStatus' => ($user['status'] == 1) ? true : false,
                    'userType' => $user['userType'],
                    'occupation' => $user['occupation'],
                    'availableBalance_thousand' => $getAccountBalance['data']['WithdrawableBalance_th'] ?? false,
                    'availableBalance' => $getAccountBalance['data']['WithdrawableBalance'] ?? false,
                    'nextOfKin' => $verifyNextOfKin,
                    'kycStatus' => $getStatus,
                    'created' => $this->formatDate($user['time']),
                ];
            }

        } catch (PDOException $e) {
            $_SESSION['err'] = $e->getMessage();
            $this->respondWithInternalError($_SESSION['err']);
        } finally {
            $stmt = null;
            $this->conn = null;
            $reni = null;
        }
        return $userData;
    }

    private function checkIfMailExists(string $mail): bool {

        try {
            $sql = 'SELECT mail FROM tblusers WHERE mail = :mail';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':mail', $mail, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            $_SESSION['err'] = $e->getMessage();
            $this->respondWithInternalError($_SESSION['err']);
        } finally {
            $stmt = null;
        }
    }

    #VerifyOtp::This method verifies  a user OTP during verification

    public function verifyOtp(array $data) {

        try {
            $sql = 'SELECT * FROM tblusers WHERE  mail = :mail';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':mail', $data['mail'], PDO::PARAM_STR);
            if (!$stmt->execute()) {
                return false;
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user['otp'] != $data['usertoken']) {
                $this->outputData(false, 'Please enter your correct OTP..', null);
                exit();
            }
            if (!$this->activateAccount($data['mail'])) {

                $this->outputData(false, $_SESSION['err'], null);
                exit();
            }
            $getStatus = $this->getkycStatus($user['usertoken']);
            $userData = [
                'fname' => $user['fname'],
                'mail' => $user['mail'],
                'usertoken' => $user['usertoken'],
                'phone' => $user['phone'],
                'regStatus' => ($user['status'] == 1) ? true : false,
                'userType' => $user['userType'],
                'kycStatus' => $getStatus,
                'created' => $this->formatDate($user['time']),

            ];

        } catch (PDOException $e) {
            $_SESSION['err'] = $e->getMessage();
            $this->respondWithInternalError($_SESSION['err']);
        } finally {
            $stmt = null;
            $this->conn = null;
        }

        return $userData;

    }

    #This methid activates User account if during registration verification

    public function activateAccount($mail) {
        try {
            $status = 1;
            $sql = ' UPDATE tblusers SET status = :status WHERE  mail = :mail';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':mail', $mail);
            if (!$stmt->execute()) {
                return false;
            } else {
                return true;
            }
        } catch (PDOException $e) {
            $this->outputData(false, $_SESSION['err'] = $e->getMessage(), null);
            return false;
        } finally {
            $stmt = null;
            $this->conn = null;
        }
    }

    #Update Password:: This function updates a user Password

    public function updatePassword(array $data): void {

        try {
            $sql = 'SELECT pword FROM tblusers WHERE usertoken = :usertoken';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usertoken', $data['usertoken'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                $dbPwd = $stmt->fetchColumn();
                $passHash = password_hash($data['npword'], PASSWORD_DEFAULT, [12]);

                if (password_verify($data['fpword'], $dbPwd)) {

                    if (!$this->updatePasswordInDB($passHash, $data['usertoken'])) {
                        $this->outputData(false, $_SESSION['err'], null);
                        return;
                    }
                    $this->outputData(true, 'Password Updated', null);
                    return;

                } else {

                    $this->outputData(false, 'Current password specified is not correct', null);
                    return;
                }
            }
        } catch (PDOException $e) {
            $_SESSION['err'] = $e->getMessage();
            $this->respondWithInternalError($_SESSION['err']);
        } finally {
            $stmt = null;
            #   Terminate the database connection
            $this->conn = null;

        }
    }

    # updatePasswordInDB::This function Updates users ppassword....

    private function updatePasswordInDB(string $pword, int $usertoken): bool {
        try {
            $sql = 'UPDATE tblusers SET pword = :pword WHERE usertoken = :usertoken';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':pword', $pword, PDO::PARAM_STR);
            $stmt->bindParam(':usertoken', $usertoken, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            $_SESSION['err'] = $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            $this->conn = null;
        }
    }

    public function forgetPword(array $data): bool {

        $checkIfMailExists = $this->checkIfMailExists($data['mail']);
        if (!$checkIfMailExists) {
            $this->outputData(false, 'Email does not exists', null);
            return false;

        }
        $token = $this->token();
        $passHash = password_hash($token, PASSWORD_DEFAULT);

        if (!$this->resetPasswordInDB($passHash, $data['mail'])) {
            $this->respondWithInternalError($_SESSION['err']);

            return false;
        }

        $mailer = new Mailer;
        $userData = $this->getUserdataViaMail($data['mail']);

        try {
            if ($mailer->sendPasswordToUser($data['mail'], $userData['fname'], $token)) {
                $this->outputData(true, 'Password sent to mail', null);
                return true;

            }
        } catch (PDOException $e) {
            $_SESSION['err'] = $e->getMessage();
        } finally {
            unset($mailer);
        }

    }

    private function resetPasswordInDB(string $pword, string $mail): bool {
        try {
            $sql = 'UPDATE tblusers SET pword = :pword WHERE mail = :mail';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':pword', $pword, PDO::PARAM_STR);
            $stmt->bindParam(':mail', $mail, PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            $_SESSION['err'] = $e->getMessage();
            return false;
        } finally {
            $stmt = null;
        }
    }

    #getAllUsers::This method fetches AllUsers

    public function getAllUsers() {
        $dataArray = array();
        $sql = 'SELECT fname, mail, usertoken, phone,
        status, userType, image, time FROM tblusers ORDER BY id DESC';
        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($users as $user) {
                $getStatus = $this->getkycStatus($user['usertoken']) ?? 'null';
                $getKYCData = $this->getKYCData($user['usertoken']);
                // $getAllHistoryLogs = $this->getAllHistoryLogs($user['usertoken']);
                $array = [
                    'fname' => $user['fname'],
                    'mail' => $user['mail'],
                    'usertoken' => $user['usertoken'],
                    'phone' => $user['phone'],
                    'regStatus' => ($user['status'] == 1) ? true : false,
                    'userType' => $user['userType'],
                    'profileImage' => json_decode($user['image'] ?? ''),
                    'kycStatus' => $getStatus,
                    'created' => $this->formatDate($user['time']),
                    'kycData' => $getKYCData,
                    // 'getUserHistoryLogs' => $getAllHistoryLogs
                ];

                array_push($dataArray, $array);
            }

        } catch (PDOException $e) {
            $_SESSION['err'] = "Unable to retrieve user data" . $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            $this->conn = null;
        }
        return $dataArray;
    }

    #updateUserStatus ::This methos changes users Status
    public function updateUserStatus($mail, $userType) {
        try {
            $sql = "UPDATE tblusers SET userType = :userType WHERE mail = :mail";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':userType', $userType);
            $stmt->bindParam(':mail', $mail);
            $stmt->execute();

            $_SESSION['err'] = " Status updated successfully";
            return true;

        } catch (PDOException $e) {
            $_SESSION['err'] = "Unable to update status: " . $e->getMessage();
            return false;

        } finally {
            $stmt = null;
            $this->conn = null;
        }

    }

}
