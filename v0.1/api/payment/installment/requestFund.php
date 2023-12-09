<?php
require_once '../../../assets/initializer.php';
$data = (array) json_decode(file_get_contents('php://input'), true);

$payment = new Payment($db);

$ReniPayment = new ReniPayment($db);

#  Check for rge requests method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Allow: POST');
    exit();
}

$auth = new Auth($db);

if (!$auth->authenticateUser($data['usertoken'])) {
    return $auth->outputData(false, "User Does Not Exists", null);
    unset($auth);

}

$getUserDetails = $auth->getUserdata($data['usertoken']); // get user details plus user real-time account information
$getAccountBalance = $getUserDetails['availableBalance'];

//   $renitoken = "Vxwcik8lUV76FoH7XA4XSrRdnWD3JffHyZRyCOwgRuY=";
//   $getAccountBalance = 20;

# Check if isInstallmentOngoing.. Check if uscer has  pending istallmentall product
// $isInstallmentOngoing = $payment->isInstallmentOngoing($data[ 'usertoken' ]);
// if($isInstallmentOngoing){
//     $payment->outputData(false, "Sorry, but we are unable to process your request. You currently have an active product with ongoing installment payments", null);
//      exit;
// }

if ($getAccountBalance < $data['halfpayment']) {
    $payment->outputData(false, 'Insufficient funds. Kindly fund your wallet', null);
    exit;

}

# Prepare to check for Installmentally-Plan value
$checkSubscribedPlan = $payment->checkSubscribedPlan(intval($data['package']));
if (!$checkSubscribedPlan) {
    $payment->outputData(false, $_SESSION['err'], null);
    exit;
}

# Calculates the estimated total days for paying off a loan based on the subscribed months.
$calculateDateDifferenceInDays = $payment->calculateDateDifferenceInDays($checkSubscribedPlan['planid']);

# Get estimated monthin count
$estimateTotalMonthsForLoanRepayment = $ReniPayment->estimateTotalMonthsForLoanRepayment($checkSubscribedPlan['planid']);

#This method saves user agreement for whatever auto_debit_type.
$requestRecurrentApproval = $ReniPayment->requestRecurrentDebitApproval($getUserDetails['renitoken'], $data['halfpayment'],
    $calculateDateDifferenceInDays, $estimateTotalMonthsForLoanRepayment, $data['usertoken'], "PRODUCT_INSTALLMENTAL");

if ($requestRecurrentApproval['success'] != false) {
    $RequestFund = $ReniPayment->RequestFund($getUserDetails['renitoken'], $data['halfpayment']);
    $array = [
        "status" => true,
        "message" => "Authorization sucessful",
        "data" => $RequestFund,
        "agreementtoken" => $requestRecurrentApproval['agreementtoken'],
    ];
    echo json_encode($array);
    exit;

} else {

    return $payment->outputData(false, "Unable to process request, Please try again later");
}
