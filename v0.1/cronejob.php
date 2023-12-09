<?php
require __DIR__ . '/../vendor/autoload.php';
header('Content-Type: application/json;charset=utf-8');
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(dirname(__DIR__, 1), '/.env.dev');
$dotenv->load();

#Loan crone jobs codes.
// require_once 'reminder/autoDebitLoan.php';
// require_once 'reminder/autoDebitLoanAfterDueDate.php';
// require_once 'reminder/remindUserLoanADayBeforeDueDate.php';
// require_once 'reminder/remindUserLoanAWeekBeforeDueDate.php';
// require_once 'reminder/celebrateLoanSuccess.php';

// #ProductRepayment crone jobs codes.

// require_once 'reminder/autoDebitProductLoan.php';
// require_once 'reminder/autoDebitProductLoanAfterDueDate.php';
// require_once 'reminder/remindUserProductLoanADayBeforeDueDate.php';
// require_once 'reminder/remindUserProductLoanAWeekBeforeDueDate.php';
// require_once 'reminder/remindUserProductLoanAWeekBeforeDueDate.php';
// require_once 'reminder/remindUserProductLoanThreeDaysBeforeDueDate.php';
// require_once 'reminder/celebrateProductLoanSuccess.php';
require_once 'reminder/verify-recurent-status.php';
