<?php
$db = new Database();
$LoanRepayment = new LoanRepayment($db);
$handleUserRepaymentSchedule = $LoanRepayment->handleUserRepaymentSchedule();
if ($handleUserRepaymentSchedule) {

    $LoanRepayment->outputData(true, "CRON job successfully executed,  No errors were encountered on ->autoDebitLoan Endpoint<-", $handleUserRepaymentSchedule);
} else {
    $LoanRepayment->outputData(false, "No cron job schedule available on ->autoDebitLoan Endpoint<-", $handleUserRepaymentSchedule);
}
