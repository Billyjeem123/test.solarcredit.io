<?php
require_once '../../../assets/initializer.php';
$data = (array) json_decode(file_get_contents('php://input'), true);

$payment = new Payment($db);

$ReniPayment = new ReniPayment();

#  Check for rge requests method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Allow: POST');
    exit();
}

$getUserDetails = $payment->getUserdata($data['usertoken']);
// get user details plus user real-time account information
$getAccountBalance = $getUserDetails['availableBalance'] ?? 20;
//   echo json_encode( $getAccountBalance );
//   exit;

// $renitoken = 'Vxwcik8lUV76FoH7XA4XSrRdnWD3JffHyZRyCOwgRuY=';
// $getAccountBalance = 20;

$approveRequestedFund = $ReniPayment->approveRequestedFund($getUserDetails['renitoken'], $data['otp']);
#Deduct money from wallet and coutinue transaction
if (!$approveRequestedFund['success']) {
    return $ReniPayment->outputData(false, $approveRequestedFund['message'], $approveRequestedFund['data']);
    exit;
}

$firstLevelStatus = $approveRequestedFund['data']['status'];
$token = $approveRequestedFund['data']['data']['data']['Reference'];

# Prepare to check for Installmentally-Plan value
$checkSubscribedPlan = $payment->checkSubscribedPlan(intval($data['package']));

if (!$checkSubscribedPlan) {
    $payment->outputData(false, $_SESSION['err'], null);
    exit;
}

#calculateAmountRemaining ::Check  and calculate  half of the payment.
$calculateAmountRemaining = $payment->calculateAmountRemaining($data['Totalprice']);
if ($data['amountPaid'] < $calculateAmountRemaining) {
    $payment->outputData(false, "Unable to process transaction.You must pay at least {$payment->formatCurrency($calculateAmountRemaining)} to process transaction", null);
    exit;
}

# Record Ongoing Transaction After Transaction OTP has been verified;
if (!$payment->recordTransaction($token, $data['usertoken'], $data['amountPaid'], $_ENV['creditOrDebit'] = 'Debit')) {
    $payment->outputData(false, $_SESSION['err'], null);
    exit;
}

# Save usertoken of buyer during purchase.
if (!$payment->saveUserTokenDuringPurchase($token, $data['usertoken'], $_ENV['PAYMENT_OPTION'] = 'Installment')) {
    $payment->outputData(false, $_SESSION['err'], null);
    exit;

}

#Calculate monthly payment
$calculateMonthlyRepayment = $payment->calculateMonthlyRepayment($calculateAmountRemaining, $checkSubscribedPlan['planid']);

#calculate date
$calculateDateAhead = $payment->calculateDateAhead(+$checkSubscribedPlan['planid'] . '' . 'month');

#This method stores info about the product loan.
$saveAllProductBoughtInstallmentally = $payment->saveAllProductBoughtInstallmentally(
    $data['Totalprice'],
    $data['amountPaid'],
    $calculateAmountRemaining,
    $data['amountPaid'],
    $checkSubscribedPlan['planid'],
    $calculateMonthlyRepayment,
    $token,
    $calculateDateAhead,
    $data['usertoken'],
    $data['agreementtoken']

);

if (!$saveAllProductBoughtInstallmentally) {
    $payment->outputData(false, $_SESSION['err'], null);
    exit;
}

#calculateMonthsAheadAndSave:: Calulation month duration
$calculateMonthsAheadAndSave = $payment->calculateMonthsAheadAndSave(
    $token,
    $data['usertoken'],
    $checkSubscribedPlan['planid'],
    $calculateMonthlyRepayment

);
if (!$calculateMonthsAheadAndSave) {
    $payment->outputData(false, $_SESSION['err'], null);
    exit;
}

$mailer = new Mailer();
$count = 0;

foreach ($data['productBought'] as $key => $allProducts) {

    if ($allProducts['productType'] === 'Userproduct') {

        # To future dev reading this code, In the later on,
        # we might need to alert product owners for the code below, but right now i think we shouldn't.
        # Just in case the project ownsers asked for this,worry no more. just uncomment this code, if need be.
        # It should definely do the work for you.
        # I hope i helped you .

        $ownerRecord = $payment->getUserdata($allProducts['ownertoken']);

        $calculateDividends = $payment->calculateProductDividends($allProducts['productPrice']);

        #Save user transaction information... save user, price, commison
        #After saving user transaction information use crone job to send real time money
        #to those who owns the product
        $saveUserProductTrnsaction = $payment->saveUserProductTrnsaction($ownerRecord['usertoken'],
            $allProducts['productPrice'], $calculateDividends, $allProducts['productToken'], $allProducts['productType']);

        if (!$saveUserProductTrnsaction) {
            $payment->outputData(false, $_SESSION['err'], null);
            exit;
        }

        //   $creditCentralWallet = $payment->creditCentralWallet($calculateDividends,
        // $_ENV['creditOrDebit']="Credit", " Received  calculateDividends naira from  $ownerRecord[fname] for product buyback commission.");
        // if (!$creditCentralWallet) {
        // $payment->outputData(false, $_SESSION['err'], null);
        // exit;
        // }

        try {
            $mailer->notifyOwnersOfSale($ownerRecord['mail'], $ownerRecord['fname'], $allProducts['productname']);

        } catch (Exception $e) {
            # Handle the error or log it as needed
            $errorMessage = date('[ Y-m-d H:i:s ] ') . 'Error sending mail for ' . __METHOD__ . '  ' . PHP_EOL . 'Error Message: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
            error_log($errorMessage, 3, 'mail.log');
        }

        $saveProductInstallments = $payment->saveProductInstallments(
            $token,
            $allProducts['productToken'],
            $allProducts['productQuantity'],
            $allProducts['productname'],
            $allProducts['productPrice'],
            $allProducts['productType'],
            $_ENV['MODE_OF_PAYMENT'] = "Reni-Wallet",
            $allProducts['productimage'],
            $calculateDividends,
            $data['agreementtoken']

        );

        if (!$saveProductInstallments) {
            $payment->outputData(false, $_SESSION['err'], null);
            exit;
        }

        $count++;
    } else {

        $count = 0;

        $saveProductInstallments = $payment->saveProductInstallments(
            $token,
            $allProducts['productToken'],
            $allProducts['productQuantity'],
            $allProducts['productname'],
            $allProducts['productPrice'],
            $allProducts['productType'],
            $_ENV['MODE_OF_PAYMENT'] = "Account-Wallet",
            $allProducts['productimage'],
            0.00,
            $data['agreementtoken']

        );

        if (!$saveProductInstallments) {
            $payment->outputData(false, $_SESSION['err'], null);
            exit;
        }
        $count++;

    }
    $payment->removeProductFromCart($data['usertoken'], $allProducts['productToken']);

}

#Send Notofication to admin of new purchase alert.
try {
    $mailer->confirmPaymentInstallmentallyPurchaseToAdmin($data['productBought'], $payment->formatCurrency($data['Totalprice']), $data['amountPaid']);

} catch (Exception $e) {
    # Handle the error or log it as needed
    $errorMessage = date('[ Y-m-d H:i:s ] ') . 'Error sending mail for ' . __METHOD__ . '  ' . PHP_EOL . 'Error Message: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
    error_log($errorMessage, 3, 'mail.log');
}

$amountPaid = $payment->formatCurrency($data['amountPaid']);

$message = "Dear valued user, we are pleased to inform you that your wallet has been debited with an amount of $amountPaid naira ";

$notification = new Notification($db);
$isNotificationSent = $notification->notifyUser($message, $data['usertoken']);

if ($isNotificationSent) {
    unset($notification);
    #        Notify the user of a successful transaction
    http_response_code(200);
    $payment->outputData(true, 'Transaction successful', null);
    exit;
} else {
    #  Notify the user of a transaction failure and provide error details
    $payment->outputData(false, 'Transaction failed', $_SESSION['err']);
}

#   Release the resources used by the Payment instance
unset($payment);
unset($mailer);
