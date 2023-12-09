<?php
$db = new Database();
$ProductRepayment = new ProductRepayment($db);
$remindAWeekBeforeDueDate = $ProductRepayment->remindAWeekBeforeDueDate();
if($remindAWeekBeforeDueDate){
    
    $ProductRepayment->outputData(true, "CRON job successfully executed,  No errors were encountered on ->remindUserProductLoanAWeekBeforeDueDate Endpoint<-", $remindAWeekBeforeDueDate);
}else{
    $ProductRepayment->outputData(false, "No cron job schedule available on ->remindUserProductLoanAWeekBeforeDueDate Endpoint<-", $remindAWeekBeforeDueDate);
}

