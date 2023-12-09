<?php
$db = new Database();
$ProductRepayment = new ProductRepayment($db);
$remindAWeekBeforeDueDate = $ProductRepayment->remindThreeddDaysBeforeDueDate();
if($remindAWeekBeforeDueDate){
    
    $ProductRepayment->outputData(true, "CRON job successfully executed,  No errors were encountered on ->remindUserProductLoanThreeDaysBeforeDueDate Endpoint<-", $remindAWeekBeforeDueDate);
}else{
    $ProductRepayment->outputData(false, "No cron job schedule available on ->remindUserProductLoanThreeDaysBeforeDueDate Endpoint<-", $remindAWeekBeforeDueDate);
}

