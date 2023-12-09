<?php

class ReniKYC extends AbstractClasses {

    private $conn;

    public function __construct(Database $database) {

        $this->conn = $database->connect();
    }

    public function updateUserId($data) {

        $verifyUserId = [
            'usertoken' => $data['usertoken'],
            'type' => $data['type'],
            'number' => $data['number'],
            'photo' => base64_encode($data['photo']),
        ];

        $url = $_ENV['ReniKYC_DEV'] . '/user.kyc.UpdateID';

        $connectToReniTrust = $this->connectToReniTrust($verifyUserId, $url);

        $encode = base64_decode($connectToReniTrust);

        $encodeMailResponse = json_decode($connectToReniTrust, true);

        // var_dump($connectToReniTrust);

        if ($encodeMailResponse !== null) {

            if ($encodeMailResponse['success'] == true) {

                $this->outputData(true, $encodeMailResponse['message'], $encodeMailResponse['data']);

            } else {
                $this->outputData(false, $encodeMailResponse['message'], null);
            }
        } else {
            $this->outputData(false, "Unable to process request, Please contact support", null);
        }

    }

    public function updateUserAddress($data) {

        $verifyUserId = [
            'usertoken' => $data['usertoken'],
            'address' => $data['address'],
            'photo' => base64_encode($data['photo']),
        ];

        $url = $_ENV['ReniKYC_DEV'] . '/user.kyc.UpdateAddress';

        $connectToReniTrust = $this->connectToReniTrust($verifyUserId, $url);

        $encode = base64_decode($connectToReniTrust);

        $encodeMailResponse = json_decode($encode, true);

        if ($encodeMailResponse !== null) {

            if ($encodeMailResponse['success'] == true) {

                $this->outputData(true, $encodeMailResponse['message'], $encodeMailResponse['data']);

            } else {
                $this->outputData(false, $encodeMailResponse['message'], null);
            }
        } else {
            $this->outputData(false, "Unable to process request, Please contact support", null);
        }

    }

    public function getAccountNumber($data) {

        $getUserAccountNumber = [
            'usertoken' => $data['usertoken'],
        ];

        $url = $_ENV['ReniKYC_DEV'] . '/getUserBankAccountNumber';

        $connectToReniTrust = $this->connectToReniTrust($getUserAccountNumber, $url);

        $encode = base64_decode($connectToReniTrust);

        $encodeMailResponse = json_decode($encode, true);

        if ($encodeMailResponse !== null) {

            if ($encodeMailResponse['success'] == true) {

                $this->outputData(true, $encodeMailResponse['message'], $encodeMailResponse['data']);

            } else {
                $this->outputData(false, $encodeMailResponse['message'], null);
            }
        } else {
            $this->outputData(false, "Unable to process request, Please contact shupport", null);
        }

    }

    //This method fetches a user Account number plus balance
    public function getAccountBalance($data) {

        $getUserAccountNumber = [
            'accountNumber' => $data['accountNumber'],
        ];

        $url = $_ENV['ReniKYC_DEV'] . '/getAccountBalance';

        $connectToReniTrust = $this->connectToReniTrust($getUserAccountNumber, $url);

        $encode = base64_decode($connectToReniTrust);

        $encodeMailResponse = json_decode($encode, true);

        if ($encodeMailResponse !== null) {

            if ($encodeMailResponse['success'] == true) {

                $this->outputData(true, $encodeMailResponse['message'], $encodeMailResponse['data']);

            } else {
                $this->outputData(false, $encodeMailResponse['message'], $encodeMailResponse['data']);
            }
        } else {
            $this->outputData(false, "Unable to process request, Please contact suppojrt", null);
        }

    }

}