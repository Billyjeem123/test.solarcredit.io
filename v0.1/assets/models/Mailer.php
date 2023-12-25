<?php

use PHPMailer\PHPMailer\PHPMailer;

class Mailer
 {
  public function sendOTPToken($email, $fname, $otp)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = '[Solar Credit] Account Verification';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>Thank you for registering with us! We're thrilled to have you as a part of our community. To ensure the security and validity of your account, we kindly ask you to complete the email verification process.</p>
                    <p>Your OTP is: <strong>$otp</strong></p>
                    <p>To finalize your registration and gain full access to our platform, kindly enter the OTP in the designated field on our website. This verification step helps us ensure that your email address is correct and that you have control over the account.</p>

                    <p>Thank you for your cooperation in completing the email verification process. We look forward to providing you with a fantastic experience on our platform. Should you need any assistance or have any feedback, feel free to reach out to us.</p>
                    <p>Best regards,<br>Team {$_ENV['APP_NAME']}</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        echo 'Email not sent';
    } else {
        return true;
    }
}


    public function sendPasswordToUser($email, $fname, $token)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();

    // $mail->SMTPDebug = 2;
    $mail->Host = $_ENV['HOST_NAME'];

    $mail->SMTPAuth = true;

    $mail->Username = $_ENV['SMTP_USERNAME'];

    $mail->Password = $_ENV['SMTP_PWORD'];

    $mail->SMTPSecure = 'ssl';

    $mail->Port = 465;

    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);

    $mail->addAddress($email, $fname);

    $mail->isHTML(true);

    $mail->Subject = $_ENV['APP_NAME'] . ' - Reset Your Password';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }

                    @media only screen and (max-width: 768px) {
                        h1 {
                            font-size: 24px;
                            margin-bottom: 20px;
                        }
                        p {
                            font-size: 16px;
                            margin-bottom: 15px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We received a request to reset the password for your account. If you did not request this, please ignore this email.</p>
                    
                    <p>Password: [Default Password $token]</p>
                    <p>To reset your password, please follow these steps:</p>
                    <ol>
                        <li>Go to the login page on our website.</li>
                        <li>Click on the 'Forgot password' link.</li>
                        <li>Enter your email address and click 'Submit'.</li>
                        <li>Check your email for further instructions on how to reset your password.</li>
                        <li>After following this procedure, a new password will be sent to your email.</li>
                    </ol>
                    
                    <p>Please note that this is a default password token, and we strongly recommend that you change it after logging in for security purposes. You can change your password by logging in to your account and updating your account settings.</p>
                    
                    <p>If you have any questions or concerns, please do not hesitate to contact us.</p>
                    <p>Thank you again for joining us!</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        echo 'Email not sent';
    } else {
        return true;
    }
}


   public function alertAdminOfProductFromUser($productname, $productQuantity)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($_ENV['APP_MAIL'], 'Admin');
    $mail->isHTML(true);
    $mail->Subject = '[Solar-Credit] New Product Request Alert';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear Admin,</h1>
                    <p>We hope this email finds you well. This email is to inform you that a new product request has been made on the website. Please find the details of the request below:</p>
                    <p><strong>Product Name:</strong> $productname</p>
                    <p><strong>Product Quantity:</strong> $productQuantity</p>
                    <p>Please take the necessary actions to fulfill this request as soon as possible.</p>
                    <p>Best regards,<br>Team {$_ENV['APP_NAME']}</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
    } else {
        return true;
    }
}


 public function sendApprovalNotification($email, $fname)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-Credit] Your Product Request Has Been Approved';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We are pleased to inform you that your recent product request has been approved. This means that your product has met our quality standards and is now ready to be sold in our marketplace.</p>
                    <p>We believe that your product has the potential to make a significant impact in our community, and we're excited to be a part of your journey. In the meantime, please feel free to reach out to us if you have any questions or concerns. Our team is always here to help.</p>
                    <p>Thank you for choosing to work with us. We look forward to seeing the impact your product will make in our community.</p>
                    <p>Best regards,<br>Team {$_ENV['APP_NAME']}</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
    } else {
        return true;
    }
}


 public function sendDisapprovalNotification($email, $fname)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-Credit] Your Product Request Has Been Disapproved';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We regret to inform you that your recent product request has been disapproved. Our team of experts has reviewed your product thoroughly and determined that it does not meet our quality standards.</p>
                    <p>Please note that this decision was not taken lightly, and we understand how disappointing it can be to receive this news. We encourage you to keep working on your product and making improvements to meet our standards.</p>
                    <p>If you have any questions or concerns about why your product was disapproved, please feel free to reach out to us. Our team will be happy to provide you with detailed feedback and guidance on how to improve your product.</p>
                    <p>Thank you for your interest in working with us. We wish you all the best with your future endeavors.</p>
                    <p>Best regards,<br>Team {$_ENV['APP_NAME']}</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
    } else {
        return true;
    }
}


public function sendLoanNotificationToAdmin($loanApplicant)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-Credit] New Loan Request';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear Admin,</h1>
                    <p>We hope this email finds you well. We are writing to inform you that a new loan request has been submitted on the platform. The request was made by $loanApplicant.</p>
                    <p>Please review the loan request and take appropriate action.</p>
                    <p>Best regards,<br>Team {$_ENV['APP_NAME']}</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
    } else {
        return true;
    }
}


 public function notifyLoanApproval($email, $fname)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-Credit] Your Loan Has Been Approved';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We are pleased to inform you that your loan application has been approved! We appreciate your business and hope that this loan will help you achieve your financial goals.</p>
                    <p>To ensure timely payments and avoid any penalties, we kindly remind you to regularly check your dashboard and keep track of your loan details. You can easily access and monitor your loan records on your dashboard, so please make sure to do so regularly. Thank you for your cooperation.</p>
                    <p>If you have any questions or concerns, please don't hesitate to contact our customer support team.</p>
                    <p>Best regards,<br>Team {$_ENV['APP_NAME']}</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        exit;
    }
    return true;
}

public function notifyLoanDisapproval($email, $fname, $message)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-Credit] Your Loan Has Been Disapproved';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We regret to inform you that your recent loan application has been disapproved. We understand that this news may be disappointing, but we want to assure you that our decision was based on a careful evaluation of your application and our lending policies.</p>
                    <p>After a thorough review, we have determined that the reason for the disapproval of your loan application is: <b>$message</b>. This decision was made in accordance with our internal guidelines and criteria.</p>
                    <p>Please note that the disapproval of your loan application does not reflect on your creditworthiness or financial situation. We encourage you to continue to explore other options that may be available to you.</p>
                    <p>If you have any questions about the reasons for the disapproval of your application, please don't hesitate to contact our customer support team. We will do our best to provide you with a clear explanation of our decision.</p>
                    <p>We appreciate your interest in our lending service and hope that you will consider us for your future financial needs.</p>
                    <p>Best regards,<br>Team {$_ENV['APP_NAME']}</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        exit;
    }
    return true;
}

public function notifyOwnersOfSale($email, $fname, $productname)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-Credit] Sold Out';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We hope this message finds you well. We wanted to take a moment to inform you that one of your products has been purchased on our platform. Congratulations on the successful sale.</p>
                    <p>Your product, <b>$productname</b>, was recently bought by a customer who was interested in your item. We're thrilled that you're part of our platform and that your products are resonating with our users.</p>
                    <p>We're so glad that you've chosen our platform to showcase and sell your products. Your participation helps make our marketplace a great place for both sellers and buyers to connect and engage.</p>
                    <p>If you have any questions or concerns, please don't hesitate to reach out to us. We're always here to help.</p>
                    <p>Thanks again for being part of our community, and we look forward to your continued success!</p>
                    <p>Best regards,<br>Team {$_ENV['APP_NAME']}</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        exit;
    }
    return true;
}


    
    public function confirmFullPaymentAndProductPurchaseToAdmin(array $boughtBought, $totalPrice)
{
    $numberOfProducts = count($boughtBought);

    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-Credit] Product Purchase Alert';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Product Purchase Alert</h1>
                    <p>We would like to inform you that $numberOfProducts product(s) have been purchased on the website. The following items have been purchased:</p>
                    <ul>
                    ";

    foreach ($boughtBought as $product) {
        $body .= "
                        <li>
                            <strong>Product Name:</strong> {$product['productname']}<br>
                            <strong>Quantity:</strong> {$product['productQuantity']}<br>
                            <strong>Price:</strong> {$product['productPrice']}<br>
                            <strong>Product Type:</strong> {$product['productType']}<br>
                        </li>
                    ";
    }

    $body .= "
                    </ul>
                    <p>Mode of Payment: {$_ENV['MODE_OF_PAYMENT']}</p>
                    <p>Payment Option: {$_ENV['PAYMENT_OPTION']}</p>
                    <p>Total Price: N {$totalPrice}</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}

public function sendProductPaidOnceApprovalNptofication($email, $fname, $productname)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-Credit] Product Order Acknowledgment';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We hope this email finds you in good health and spirits. This is to inform you that we have received your order for <b>$productname</b>.</p>
                    <p>We would like to take this opportunity to thank you for choosing us and entrusting us with your purchase. Your order has been acknowledged and we are processing it now.</p>
                    <p>If you have any questions regarding your order, please do not hesitate to contact us. We will be more than happy to assist you.</p>
                    <p>Once again, thank you for your order, and we look forward to serving you soon.</p>
                    <p>Best regards,<br>Team {$_ENV['APP_NAME']}</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}

public function confirmPaymentInstallmentallyPurchaseToAdmin(array $boughtBought, $totalPrice, $amountPaid)
{
    $numberOfProducts = count($boughtBought);

    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-Credit] Product Purchase Alert';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Product Purchase Alert</h1>
                    <p>We would like to inform you that $numberOfProducts product(s) have been purchased on the website. The following items have been purchased:</p>
                    <ul>
                    ";

    foreach ($boughtBought as $product) {
        $body .= "
                        <li>
                            <strong>Product Name:</strong> {$product['productname']}<br>
                            <strong>Quantity:</strong> {$product['productQuantity']}<br>
                            <strong>Price:</strong> {$product['productPrice']}<br>
                            <strong>Product Type:</strong> {$product['productType']}<br>
                        </li>
                    ";
    }

    $body .= "
                    </ul>
                    <p>Mode of Payment: {$_ENV['MODE_OF_PAYMENT']}</p>
                    <p>Payment Option: {$_ENV['PAYMENT_OPTION']}</p>
                    <p>Total Price: N {$totalPrice}</p>
                    <p>Amount Paid: N {$amountPaid}</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}


    
    public function SendProductInstallmentPaymentStatusNotification($email, $fname, $productname)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = "[Solar-credit] Acknowledgement of Installment Payment for $productname";

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>I hope this email finds you well. This is to acknowledge the receipt of your installment payment for the $productname that you purchased from us.</p>
                    <p>We appreciate your prompt payment, and we are glad to inform you that the payment has been successfully processed. We would like to remind you that the remaining balance should be paid on the agreed schedule, as per our sales agreement.</p>
                    <p>Please do not hesitate to reach out to us if you have any questions or concerns regarding the installment plan or the product itself. We are always here to help.</p>
                    <p>Thank you for choosing " . $_ENV['APP_NAME'] . " for your purchase. We look forward to providing you with more excellent products and services in the future.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}


public function sendLoanPenaltyEmail($email, $fname, $amountOwed, $month)
{
    $calculatePenalty = $amountOwed * $_ENV['loan_penalty'];
    $new_amount = $amountOwed + $calculatePenalty;

    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-credit] Late Payment Penalty Notice';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We hope this email finds you in good health and high spirits. We are writing to inform you that a penalty fee of 20% has been added to your balance due to non-payment on the agreed due date.</p>
                    <p>According to our records, the initial amount to pay for $month is " . number_format($amountOwed, 2) . ". With the addition of the penalty fee, the new balance is now " . number_format($new_amount, 2) . ", of which " . number_format($calculatePenalty, 2) . " is the estimated penalty fee.</p>
                    <p>Please make the necessary arrangements to settle the balance in full, including the penalty fee, as soon as possible.</p>
                    <p>Please do not hesitate to reach out to us if you have any questions or concerns regarding the installment plan. We are always here to help.</p>
                    <p>Thank you for choosing " . $_ENV['APP_NAME'] . " for your financial needs. We look forward to hearing from you soon.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}

 
    
    public function notifyUserForDeductingLoanAmountFromWallet($email, $fname, $amount, $date)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-credit] Notification: Funds removed from wallet';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We hope this email finds you in good health and high spirits. We are writing to inform you that a recent transaction has resulted in a removal of funds from your wallet, towards the servicing of your loan.</p>
                    <p>The amount of " . number_format($amount, 2) . " has been deducted from your account on $date.</p>
                    <p>This is part of your regular loan servicing schedule and is in line with the terms and conditions of your loan agreement.</p>
                    <p>Please do not hesitate to reach out to us if you have any questions or concerns regarding the installment plan. We are always here to help.</p>
                    <p>Thank you for choosing " . $_ENV['APP_NAME'] . " for your financial needs.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}


  public function NotifyUserOfDebitAndPenaltyAmount($email, $fname, $month, $amountTopay)
{
    $calculatePenalty = $amountTopay * $_ENV['loan_penalty'];
    $new_amount = $amountTopay + $calculatePenalty;

    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-credit] Notification: Funds removed from wallet';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We hope this email finds you well. We are writing to inform you that your wallet has been debited with both an initial payment and a penalty payment.</p>
                    <p>The initial payment of " . number_format($amountTopay, 2) . " has been deducted from your account as per the agreed terms and conditions of the contract. This payment was due on $month.</p>
                    <p>Additionally, a penalty payment of " . number_format($calculatePenalty, 2) . " has been imposed as a result of a late payment. Please note that this penalty has been charged as per the terms of the agreement.</p>
                    <p>Total amount debited is " . number_format($new_amount, 2) . "</p>
                    <p>We would like to remind you that timely payment of your bills is important to ensure the smooth functioning of our services. If you have any difficulty making a payment in the future, please reach out to us for assistance.</p>
                    <p>Thank you for choosing " . $_ENV['APP_NAME'] . " for your financial needs.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}

public function NotifyUserOfLoanADayToDueDate($email, $fname)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-credit] Your Loan Payment is Due Tomorrow';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We hope this email finds you well. We would like to remind you that your loan repayment is due tomorrow. It is crucial that you ensure sufficient funds are available in your wallet to avoid any payment failures.</p>
                    <p>Failure to fund your wallet adequately by or before the due date will result in penalty charges being applied to your account. To prevent any inconvenience, please ensure that the outstanding amount is deposited into your wallet as soon as possible.</p>
                    <p>If you have already made the payment or have any concerns regarding your loan repayment, kindly disregard this reminder and accept our apologies for any inconvenience caused.</p>
                    <p>Please do not hesitate to reach out to us if you have any questions or concerns regarding your loan plan. We are always here to help.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}


 public function NotifyUserOfLoanAWeekToDueDate($email, $fname)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-credit] Your Loan Payment is Due in a week';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We hope this email finds you well. We would like to remind you that your loan repayment will be due in a week. It is crucial that you ensure sufficient funds are available in your wallet to avoid any payment failures.</p>
                    <p>Failure to fund your wallet adequately by the due date will result in penalty charges being applied to your account. To prevent any inconvenience, please make sure that the necessary amount is deposited into your wallet as soon as possible.</p>
                    <p>If you have already made the payment or have any concerns regarding your loan repayment, kindly disregard this reminder and accept our apologies for any inconvenience caused.</p>
                    <p>Please do not hesitate to reach out to us if you have any questions or concerns regarding your loan plan. We are always here to help.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}

public function NotifyUserOfLoanThreeDaysToDueDate($email, $fname)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = '[Solar-credit] Your Loan Payment Will Be Due in Three days';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We hope this email finds you well. We would like to remind you that your loan repayment will be due in three days. It is crucial that you ensure sufficient funds are available in your wallet to avoid any payment failures.</p>
                    <p>Failure to fund your wallet adequately by the due date will result in penalty charges being applied to your account. To prevent any inconvenience, please make sure that the necessary amount is deposited into your wallet as soon as possible.</p>
                    <p>If you have already made the payment or have any concerns regarding your loan repayment, kindly disregard this reminder and accept our apologies for any inconvenience caused.</p>
                    <p>Please do not hesitate to reach out to us if you have any questions or concerns regarding your loan plan. We are always here to help.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}


public function NotifyAdminOfWithdrawalRequest($fullname, $acknumber, $bankname, $amount)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($_ENV['APP_MAIL'], 'Admin');
    $mail->isHTML(true);
    $mail->Subject = '[Solar-credit] Withdrawal Request';

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear Admin,</h1>
                    <p>We are writing to notify you that a withdrawal request has been submitted by <b>$fullname</b> for the withdrawal of " . number_format($amount, 2) . " from their wallet.</p>
                    <p>According to our records, the account details are as follows:</p>
                    <p>Account number: $acknumber</p>
                    <p>Bank name: $bankname</p>
                    <p>If you need any further information from the account holder to process the request, please contact them directly.</p>
                    <p>Thank you for your prompt attention to this matter.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}

public function NotifyUserOfWithdrawalApproval($email, $fname, $amount, $bankname)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = "Withdrawal Request Approved";

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We are delighted to notify you that your withdrawal request has been authorized.</p>
                    <p>The specified amount of " . number_format($amount, 2) . " has been transferred to the following account number $bankname, and the corresponding deduction has been made from your wallet.</p>
                    <p>Thank you for using our service, and please don't hesitate to contact us if you have any questions or concerns.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}



   public function NotifyUserofWithdrawalDisapproval($email, $fname, $amount, $message)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = "Withdrawal Request Disapproved";

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We regret to inform you that your withdrawal request has been declined.</p>
                    <p>The specified amount of " . number_format($amount, 2) . " naira has been reversed and credited back to your account.</p>
                    <p>The reason for the decline is: $message.</p>
                    <p>Thank you for using our service, and please don't hesitate to contact us if you have any questions or concerns.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}

public function celebrateLoanCompletedSuccess($email, $fname)
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = "[Solar-credit] Congratulations on paying off your loan";

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We hope this email finds you well. We are delighted to inform you that you have successfully paid off your loan. Congratulations on reaching this significant milestone!</p>
                    <p>Thank you again for choosing " . $_ENV['APP_NAME'] . " as your lending partner. We are grateful for your trust and confidence in our services, and we wish you all the best in your future endeavors.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}

    
    
 public function notifyUserOfUnPaidOutstanding($email, $fname)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = "[Solar-credit] Notification of Empty Wallet Balance for Outstanding Purchase";

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We hope this email finds you well.</p>
                    <p>We would like to inform you that your wallet is to be debited for an outstanding purchased product as per the terms of our agreement. However, it appears that your account balance is currently empty and there are no available funds to cover the outstanding balance.</p>
                    <p>We kindly request that you deposit the necessary funds as soon as possible to cover the outstanding balance.</p>
                    <p>To deposit funds, please log in to your account and fund your outstanding wallet.</p>
                    <p>We will handle the rest. If you have any questions or concerns regarding this matter, please do not hesitate to contact us. We are always here to assist you in any way we can.</p>
                    <p>Thank you for choosing " . $_ENV['APP_NAME'] . ". We look forward to hearing from you soon.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}

public function NotifyUserOfProductLoanADayToDueDate($email, $fname, $productname)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = "[Solar-credit] Upcoming Product Loan Payment Reminder: Due Tomorrow";

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We hope this email finds you well. This is a friendly reminder regarding your product loan payment.</p>
                    <p>As per our records, the deadline for your loan payment for the following product(s): $productname is tomorrow, as you have opted for a monthly installment plan.</p>
                    <p>Please ensure that your account wallet is funded before or by tomorrow. The payment will be automatically debited from your wallet.</p>
                    <p>If you have any questions or concerns regarding your loan, please don't hesitate to reach out to us. Our team is always available to assist you.</p>
                    <p>Thank you for choosing " . $_ENV['APP_NAME'] . " for your financial needs. We look forward to hearing from you soon.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}



 public function NotifyUserOfProductLoanAWeekToDueDate($email, $fname, $boughtBought)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = "[Solar-credit] Upcoming Product Loan Payment Reminder: Due In A Week Time";

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We hope this email finds you well. This is a friendly reminder regarding your product loan payment.</p>
                    <p>As per our records, the scheduled payment deadline for the following product(s) will be due in a week's time, as you have opted for a monthly installment plan:</p>
                    <ul>";
                    foreach ($boughtBought as $product) {
                        $body .= "<li><b>Product Name:</b> " . $product['productname'] . "</li>";
                        $body .= "<li><b>Price:</b> " . $product['price'] . "</li><br>";
                    }
                    $body .= "
                    </ul>
                    <p>Please ensure that your account wallet is funded before then. The payment will be automatically debited from your wallet.</p>
                    <p>If you have any questions or concerns regarding your loan, please don't hesitate to reach out to us. Our team is always available to assist you.</p>
                    <p>Thank you for choosing " . $_ENV['APP_NAME'] . " for your financial needs. We look forward to hearing from you soon.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}

public function NotifyUserOfProductLoanThreeDaysToDueDate($email, $fname, $boughtBought)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = "[Solar-credit] Upcoming Product Loan Payment Reminder: Due In Three Days Time";

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>We hope this email finds you well. This is a friendly reminder regarding your product loan payment.</p>
                    <p>As per our records, the scheduled payment deadline for the following product(s) will be due in three days' time, as you have opted for a monthly installment plan:</p>
                    <ul>";
                    foreach ($boughtBought as $product) {
                        $body .= "<li><b>Product Name:</b> " . $product['productname'] . "</li>";
                        $body .= "<li><b>Price:</b> " . $product['price'] . "</li><br>";
                    }
                    $body .= "
                    </ul>
                    <p>Please ensure that your account wallet is funded before or by then. The payment will be automatically debited from your wallet.</p>
                    <p>If you have any questions or concerns regarding your loan, please don't hesitate to reach out to us. Our team is always available to assist you.</p>
                    <p>Thank you for choosing " . $_ENV['APP_NAME'] . " for your financial needs. We look forward to hearing from you soon.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}


  public function celebrateProductLoanCompletedSuccesss($email, $fname, $boughtBought)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_NAME'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PWORD'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom($_ENV['APP_MAIL'], $_ENV['APP_NAME']);
    $mail->addAddress($email, $fname);
    $mail->isHTML(true);
    $mail->Subject = "[Solar-credit] Your Installment Payments are Complete!";

    $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 16px;
                        padding: 30px;
                        line-height: 1.6;
                        color: #333;
                        box-shadow: 0px 0px 10px #ccc;
                    }
                    @media only screen and (max-width: 768px) {
                        body {
                            font-size: 14px;
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div style='padding: 20px;'>
                    <h1>Dear $fname,</h1>
                    <p>It's time to celebrate! We are thrilled to inform you that you have successfully completed all your installment payments for the following product(s):</p>
                    <ul>";
                    foreach ($boughtBought as $product) {
                        $body .= "<li><b>Product Name:</b> " . $product['productname'] . "</li><br>";
                    }
                    $body .= "
                    </ul>
                    <p>We want to express our sincerest congratulations on reaching this significant milestone. Your commitment and dedication in fulfilling your financial obligations have paid off.</p>
                    <p>We extend our heartfelt appreciation for choosing us as your preferred provider for installment purchases. Your satisfaction is our utmost priority, and we are committed to delivering exceptional service at every step.</p>
                    <p>If you have any questions or need further assistance regarding your installment plan or any other matter, our dedicated support team is here to help. We want to ensure that your experience with us continues to be smooth and enjoyable.</p>
                    <p>Thank you for choosing " . $_ENV['APP_NAME'] . " for your financial needs.</p>
                    <p>Best regards,<br>Team " . $_ENV['APP_NAME'] . "</p>
                </div>
            </body>
        </html>
    ";

    $mail->Body = $body;

    if (!$mail->send()) {
        
        return false;
    } else {
        return true;
    }
}


}
