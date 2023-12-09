<?php
$db = new Database();
$LoanRepayment = new ProductRepayment($db);
$celebrateLoanSuccess = $LoanRepayment->celebrateProductLoanSuccess();
if($celebrateLoanSuccess){
    
    $LoanRepayment->outputData(true, "CRON job successfully executed,  No errors were encountered on ->celebrateProductLoanSuccess Endpoint<-", $celebrateLoanSuccess);
}else{
    $LoanRepayment->outputData(false, "No cron job schedule available on ->celebrateProductLoanSuccess Endpoint<-", $celebrateLoanSuccess);
}

