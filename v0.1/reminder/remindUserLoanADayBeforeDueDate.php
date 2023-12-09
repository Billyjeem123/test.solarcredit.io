<?php
$db = new Database();
$LoanRepayment = new LoanRepayment($db);
$remindADayBeforeDueDate = $LoanRepayment->remindADayBeforeDueDate();
if($remindADayBeforeDueDate){
    
    $LoanRepayment->outputData(true, "CRON job successfully executed,  No errors were encountered on ->remindUserLoanADayBeforeDueDate Endpoint<-", $remindADayBeforeDueDate);
}else{
    $LoanRepayment->outputData(false, "No cron job schedule available on ->remindUserLoanADayBeforeDueDate Endpoint<-", $remindADayBeforeDueDate);
}

