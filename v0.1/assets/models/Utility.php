<?php
// Start with PHPMailer class
use PHPMailer\PHPMailer\PHPMailer;

$timestamp = time();

# DECODE THE HTMLSPECIAL STRING IN TO STRING
# -----------------------------------------------------------------------*/

class Utility
{
  public function escape_data($data)
  {
    $mysqli = mysqli_connect($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PWORD'], $_ENV['DB_NAME']);
    if (function_exists('mysql_real_escape_string')) {
      $data = mysqli_real_escape_string($mysqli, $data);
      $data = strip_tags($data);
    } else {
      $data = trim($data);
      $data = mysqli_escape_string($mysqli, $data);
      $data = strip_tags($data);
    }
    return $data;
  }
  # ---------------------------------------------------------------------

  function get_ip_info()
  {
    $indicesServer = array(
      'PHP_SELF',
      'argv',
      'argc',
      'GATEWAY_INTERFACE',
      'SERVER_ADDR',
      'SERVER_NAME',
      'SERVER_SOFTWARE',
      'SERVER_PROTOCOL',
      'REQUEST_METHOD',
      'REQUEST_TIME',
      'REQUEST_TIME_FLOAT',
      'QUERY_STRING',
      'DOCUMENT_ROOT',
      'HTTP_ACCEPT',
      'HTTP_ACCEPT_CHARSET',
      'HTTP_ACCEPT_ENCODING',
      'HTTP_ACCEPT_LANGUAGE',
      'HTTP_CONNECTION',
      'HTTP_HOST',
      'HTTP_REFERER',
      'HTTP_USER_AGENT',
      'HTTPS',
      'REMOTE_ADDR',
      'REMOTE_HOST',
      'REMOTE_PORT',
      'REMOTE_USER',
      'REDIRECT_REMOTE_USER',
      'SCRIPT_FILENAME',
      'SERVER_ADMIN',
      'SERVER_PORT',
      'SERVER_SIGNATURE',
      'PATH_TRANSLATED',
      'SCRIPT_NAME',
      'REQUEST_URI',
      'PHP_AUTH_DIGEST',
      'PHP_AUTH_USER',
      'PHP_AUTH_PW',
      'AUTH_TYPE',
      'PATH_INFO',
      'ORIG_PATH_INFO'
    );
    $result = "";
    $result = $result . '<table cellpadding="10">';
    foreach ($indicesServer as $arg) {
      if (isset($_SERVER[$arg])) {
        $result = $result . '<tr><td>' . $arg . '</td><td> __ ' . $_SERVER[$arg] . ' </td> </tr>';
      } else {
        $result = $result . '<tr><td>' . $arg . '</td><td>__</td> </tr>';
      }
    }
    $result = $result . '</table>';
    return $result;
  }
  // 0069948573




  public function get_client_ip()
  {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
      $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
      $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
      $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
      $ipaddress = getenv('REMOTE_ADDR');
    else
      $ipaddress = 'UNKNOWN';

    $ipaddress = $_SERVER['SERVER_ADDR'];
    return $ipaddress;
  }


  // Function to generate OTP 
  public function generateNumericOTP($n)
  {

    // Take a generator string which consist of 
    // all numeric digits 
    $generator = "1357902468";

    // Iterate for n-times and pick a single character 
    // from generator and append it to $result 

    // Login for generating a random character from generator 
    //     ---generate a random number 
    //     ---take modulus of same with length of generator (say i) 
    //     ---append the character at place (i) from generator to result 

    $result = "";

    for ($i = 1; $i <= $n; $i++) {
      $result .= substr($generator, (rand() % (strlen($generator))), 1);
    }

    // Return result 
    return $result;
  }

  public function generateAlphaNumericOTP($n)
  {

    // Take a generator string which consist of 
    // all numeric digits 
    $generator = "1357902468ABCDEFGHIJKLMNOPQRSTUVWXYZ";

    // Iterate for n-times and pick a single character 
    // from generator and append it to $result 

    // Login for generating a random character from generator 
    //     ---generate a random number 
    //     ---take modulus of same with length of generator (say i) 
    //     ---append the character at place (i) from generator to result 

    $result = "";

    for ($i = 1; $i <= $n; $i++) {
      $result .= substr($generator, (rand() % (strlen($generator))), 1);
    }

    // Return result 
    return $result;
  }

  public function generateAlphaNumericOTP_case($n)
  {

    // Take a generator string which consist of 
    // all numeric digits 
    $generator = "1357902468ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

    // Iterate for n-times and pick a single character 
    // from generator and append it to $result 

    // Login for generating a random character from generator 
    //     ---generate a random number 
    //     ---take modulus of same with length of generator (say i) 
    //     ---append the character at place (i) from generator to result 

    $result = "";

    for ($i = 1; $i <= $n; $i++) {
      $result .= substr($generator, (rand() % (strlen($generator))), 1);
    }

    // Return result 
    return $result;
  }

  public function generateAlphaNumericOTP_symbol($n)
  {

    // Take a generator string which consist of 
    // all numeric digits 
    $generator = "1357902468ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_@!";

    // Iterate for n-times and pick a single character 
    // from generator and append it to $result 

    // Login for generating a random character from generator 
    //     ---generate a random number 
    //     ---take modulus of same with length of generator (say i) 
    //     ---append the character at place (i) from generator to result 

    $result = "";

    for ($i = 1; $i <= $n; $i++) {
      $result .= substr($generator, (rand() % (strlen($generator))), 1);
    }

    // Return result 
    return $result;
  }

  public function generateAlphaOTP($n)
  {

    // Take a generator string which consist of 
    // all numeric digits 
    $generator = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

    // Iterate for n-times and pick a single character 
    // from generator and append it to $result 

    // Login for generating a random character from generator 
    //     ---generate a random number 
    //     ---take modulus of same with length of generator (say i) 
    //     ---append the character at place (i) from generator to result 

    $result = "";

    for ($i = 1; $i <= $n; $i++) {
      $result .= substr($generator, (rand() % (strlen($generator))), 1);
    }

    // Return result 
    return $result;
  }

  public function validateParams()
  {

    http_response_code(400);
    $this->outputData(false, 'Invalid parameter', null);
    exit;
  }

  function redirect($location)
  {

    header("Location: $location");
  }


  // validate email
  public function validateEmail($value = '')
  {
    // code...
    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
      return true;
    } else {
      return false;
    }
  }

  // validate phone number
  public function validatePhone($value = '')
  {
    // code...
    if (preg_match('/^[0-9]{13}+$/', $value)) {
      return true;
    } else {
      return false;
    }
  }
  public function validatePayload($data)
  {

    if (!isset($data)) {

      $array = ['success' => false, 'message' => "Payload not found.", 'data' => null];
      $return = json_encode($array);
      echo "$return";
      http_response_code(400);
    }
  }

  // output data
  public function outputData($success = null, $message = null, $data = null)
  {
    $arr_output = array(
      'success' => $success,
      'message' => $message,
      'data' => $data,
    );
    echo json_encode($arr_output);
  }

  public function token()
  {

    $defaultPassword = mt_rand(100000, 999999);
    return $defaultPassword;
  }

  public function validateCard($number)
  {

    global $type;

    $cardtype = array(
      "visa" => "/^4[0-9]{12}(?:[0-9]{3})?$/",
      "mastercard" => "/^5[1-5][0-9]{14}$/",
      "amex" => "/^3[47][0-9]{13}$/",
      "discover" => "/^6(?:011|5[0-9]{2})[0-9]{12}$/",
    );

    if (preg_match($cardtype['visa'], $number)) {
      $type = "visa";
      return 'visa';
    } else if (preg_match($cardtype['mastercard'], $number)) {
      $type = "mastercard";
      return 'mastercard';
    } else if (preg_match($cardtype['amex'], $number)) {
      $type = "amex";
      return 'amex';
    } else if (preg_match($cardtype['discover'], $number)) {
      $type = "discover";
      return 'discover';
    } else {
      return false;
    }
  }

  public function validateBvn($UserBvn)
  {

    $bvn = "  /^[0-9]{11}$/";

    if (preg_match($bvn, $UserBvn)) {
      return true;
    } else {

      return false;
    }
  }

  public function validateNin($UserNin)
  {

    $nin = "/^[0-9]{11}$/";

    if (preg_match($nin, $UserNin)) {
      return true;
    } else {

      return false;
    }
  }

  public function validateDate($date)
  {

    $dateType = "/^(0[1-9]|1[0-2])\/?([0-9]{4}|[0-9]{2})$/";

    if (preg_match($dateType, $date)) {
      return true;
    } else {

      return false;
    }
  }

  public function validateCvv($cvv)
  {

    $Cvv = "/^[0-9]{3,4}$/";

    if (preg_match($Cvv, $cvv)) {
      return true;
    } else {

      return false;
    }
  }

  public function calculateDateAhead($package)
  {

    $current_date = date("Y-m-d");
    $three_months_ahead = date("Y-m-d", strtotime($current_date . $package));
    return $three_months_ahead;
  }

  public function getAllMonthsToPay($package)
  {
    $count = 0;
    $startDate = new DateTime();

    for ($i = 0; $i < $package; $i++) {
      $date = $startDate->modify('+1 month');
      $check = $date->format('Y-m-d');
      if ($check) {
        $count = $count++;
      }
    }
  }

  public function convertDateToTimeStamp($prefterredDate)
  {
    $timestamp = strtotime($prefterredDate);
    return $timestamp;
  }

  public function convertTimeStampToRealDate($prefterredDate)
  {

    $timestamp = $prefterredDate; // This is a Unix timestamp for September 1, 2021
    $date = date('Y-m-d', $timestamp);
    return $date; // Outputs: 2021-09-01
  }



  public function diffForHumans($timestamp)
  {

    $current_time = time();

    $difference_in_seconds = $current_time - $timestamp;

    if ($difference_in_seconds < 60) {
      return "Just now";
    } elseif ($difference_in_seconds < 3600) {
      return floor($difference_in_seconds / 60) . " minutes ago";
    } elseif ($difference_in_seconds < 86400) {
      return floor($difference_in_seconds / 3600) . " hours ago";
    } elseif ($difference_in_seconds < 604800) {
      return floor($difference_in_seconds / 86400) . " days ago";
    } elseif ($difference_in_seconds < 2592000) {
      $weeks = floor($difference_in_seconds / 604800);
      return $weeks . " " . ($weeks === 1 ? "week" : "weeks") . " ago";
    } elseif ($difference_in_seconds < 31104000) {
      return floor($difference_in_seconds / 2592000) . " months ago";
    } else {
      return floor($difference_in_seconds / 31104000) . " years ago";
    }
  }
}
