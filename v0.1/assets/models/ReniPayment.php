<?php

class ReniPayment extends AbstractClasses {

    public function getAccountBalance($data) {

        $getUserAccountNumber = [
            'usertoken' => $data['usertoken'] ?? $data,
        ];

        $url = $_ENV['RENI_SANDBOX'] . '/getAccountBalance';

        $connectToReniTrust = $this->connectToReniTrust($getUserAccountNumber, $url);

        $encodeMailResponse = json_decode($connectToReniTrust, true);

        // var_dump($encodeMailResponse);

        if ($encodeMailResponse !== null) {

            if ($encodeMailResponse['success'] == true) {

                $this->outputData(true, $encodeMailResponse['message'], $encodeMailResponse['data']);

            } else {
                $this->outputData(false, $encodeMailResponse['message'], $encodeMailResponse['data']);
            }
        } else {
            $this->outputData(false, "User doees not have an account number", 0);
        }

    }

// -?9?X1X[3yqg

// This method gets user financial details

    public function getUserFinacialDetails($solar_reni_token) {

        $getUserAccountNumber = [
            'usertoken' => $solar_reni_token,
        ];

        $url = $_ENV['RENI_SANDBOX'] . '/getAccountBalance';

        $connectToReniTrust = $this->connectToReniTrust($getUserAccountNumber, $url);

        $encodeMailResponse = json_decode($connectToReniTrust, true);

        if ($encodeMailResponse !== null) {

            return $encodeMailResponse;

        } else {

            return false;
        }

    }

    // This endpoint communicates with Reni to request authorization for deducting funds from the user's wallet.
    public function RequestFund($solar_reni_token, $amount) {

        $getUserAccountNumber = [
            'r_usertoken' => $solar_reni_token,
            'amount' => $amount,
        ];

        $url = $_ENV['RENI_SANDBOX'] . '/requestFund-User';

        $connectToReniTrust = $this->connectToReniTrust($getUserAccountNumber, $url);

        $encodeMailResponse = json_decode($connectToReniTrust, true);

        if ($encodeMailResponse !== null) {

            return $encodeMailResponse;

        } else {
            $this->outputData(false, "Unable to process request, Please contact support", null);
        }

    }

    // This endpoint communicates with Reni to request authorization for deducting funds from the user's wallet.
    public function approveRequestedFund($solar_reni_token, $otp) {

        $getUserAccountNumber = [
            'usertoken' => $solar_reni_token,
            'otp' => $otp,
        ];

        $url = $_ENV['RENI_SANDBOX'] . '/approveOtp';

        $connectToReniTrust = $this->connectToReniTrust($getUserAccountNumber, $url);

        $encodeMailResponse = json_decode($connectToReniTrust, true);

        if ($encodeMailResponse !== null) {
            return $encodeMailResponse;
        } else {
            return false;
        }
    }

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

        // Set a timeout for the entire cURL request (including connection and transfer)
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // Set a timeout for the connection phase only
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            $this->outputData(false, 'Unable to process request, try again later', null);
            return;
        }

        curl_close($ch);

        return $response;
    }

//       public function connectToReniTrust( array $payload, $url )
//  {

//         $ch = curl_init();
//         curl_setopt( $ch, CURLOPT_URL, $url );
//         curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
//         curl_setopt( $ch, CURLOPT_POST, 1 );
//         curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $payload ) );

//         $headers = [
//             'Authorization: Bearer ' . $_ENV['Solar_Access_Bearer']

//         ];

//         curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

//         $response = curl_exec( $ch );

//         if ( $response === false ) {
//             $error = curl_error( $ch );
//             $this->outputData( false, 'Unable to process request, try again later', null );
//             return;
//         }

//         curl_close( $ch );

//         return $response;
//     }

}