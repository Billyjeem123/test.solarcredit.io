<?php
require_once '../../assets/initializer.php';
$data = (array) json_decode(file_get_contents('php://input'), true);

$payment = new Payment($db);

$ReniPayment = new ReniPayment();

#  Check for rge requests method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Allow: POST');
    exit();
}

#Deduct money from wallet and coutinue transaction
$approveRequestedFund = $ReniPayment->approveRequestedFund($data['renitoken'], $data['otp']); #Deduct money from wallet and coutinue transaction

if (!$approveRequestedFund['success']) {
    return $ReniPayment->outputData(false, $approveRequestedFund['message'], $approveRequestedFund['data']);
    exit;
}

$firstLevelStatus = $approveRequestedFund['data']['status'];
//    echo  $token =  $approveRequestedFund['data']['data']['data']['data']['Reference'];
$token = $approveRequestedFund['data']['data']['data']['Reference'];
// echo json_encode($approveRequestedFund);
// exit;

# Record Ongoing Transaction After Transaction OTP has been verified;

if (!$payment->recordTransaction($token, $data['usertoken'], $data['Totalprice'], $_ENV['creditOrDebit'] = "Debit")) {
    $payment->outputData(false, $_SESSION['err'], null);
    exit;
}

# Save usertoken of buyer during purchase.
if (!$payment->saveUserTokenDuringPurchase($token, $data['usertoken'], $_ENV['PAYMENT_OPTION'] = "Paid-Once")) {
    $payment->outputData(false, $_SESSION['err'], null);
    exit;

}

$mailer = new Mailer();
$count = 0;

foreach ($data['products'] as $key => $allProducts) {

    if ($allProducts['productType'] === "Userproduct") {

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
            $errorMessage = date('[Y-m-d H:i:s] ') . 'Error sending mail for ' . __METHOD__ . '  ' . PHP_EOL . 'Error Message: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
            error_log($errorMessage, 3, 'mail.log');
        }

        $saveProductBoughtTransaction = $payment->saveProductBoughtTransaction(
            $token,
            intval($allProducts['productToken']),
            intval($allProducts['productQuantity']),
            $allProducts['productPrice'],
            $allProducts['productType'],
            $_ENV['MODE_OF_PAYMENT'] = "Account-Wallet",
            $allProducts['productname'],
            $allProducts['productimage'],
            $calculateDividends,
            $calculateDividends
        );

        if (!$saveProductBoughtTransaction) {
            $payment->outputData(false, $_SESSION['err'], null);
            exit;
        }

        $count++;
    } else {
        $count = 0;
        $saveProductBoughtTransaction = $payment->saveProductBoughtTransaction(
            $token,
            intval($allProducts['productToken']),
            intval($allProducts['productQuantity']),
            $allProducts['productPrice'],
            $allProducts['productType'],
            $_ENV['MODE_OF_PAYMENT'] = "Reni-Wallet",
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
    $payment->removeProductFromCart($data['usertoken'], $allProducts['productToken']);

}

#Send Notofication to admin of new purchase alert.
try {
    $mailer->confirmFullPaymentAndProductPurchaseToAdmin($data['products'], $payment->formatCurrency($data['Totalprice']));

} catch (Exception $e) {
    # Handle the error or log it as needed
    $errorMessage = date('[Y-m-d H:i:s] ') . 'Error sending mail for ' . __METHOD__ . '  ' . PHP_EOL . 'Error Message: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
    error_log($errorMessage, 3, 'mail.log');
}

$totalPrice = $payment->formatCurrency($data['Totalprice']);

$message = "Dear valued user, we are pleased to inform you that your wallet has been debited with an amount of $totalPrice";

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
