<?php
$db = new Database();
$LoanRepayment = new LoanRepayment($db);
$autoDebitLoanAfterDueDate = $LoanRepayment->autoDebitLoanAfterDueDate();
if($autoDebitLoanAfterDueDate){
    
    $LoanRepayment->outputData(true, "CRON job successfully executed,  No errors were encountered on ->autoDebitLoanAfterDueDate Endpoint<-", $autoDebitLoanAfterDueDate);
}else{
    $LoanRepayment->outputData(false, "No cron job schedule available on ->autoDebitLoanAfterDueDate Endpoint<-", $autoDebitLoanAfterDueDate);
}

