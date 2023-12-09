<?php
$db = new Database();
$LoanRepayment = new Loan($db);
$handleUserRepaymentSchedule = $LoanRepayment->checkRecurrentPayment();
var_dump($handleUserRepaymentSchedule);