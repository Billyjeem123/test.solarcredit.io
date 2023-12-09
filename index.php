
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="script.js"></script>
</head>
<body>
    <h2>Login</h2>
    <form id="loginForm">
        <label for="username">Username:</label>
        <input type="text" id="username" name="fname" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="lname" required>
        <br>
        <button type="button" onclick="login()">Login</button>
    </form>
    <p id="responseMessage"></p>



    <script>

        function goodMorning(){

            return "Good morning";


        }

        goodMorning();
        function login() {
            var userData = {
                mail: "billyhadiattaofeeq+123@gmail.com",
                pword: "1234",
                "apptoken": "vjh35vj3hv5jhv56jh5v6jhv56jh3v6j3hv6jhvj3hvuu3yg5uygu3y5guyg5uyuhb5uh"
            };

            // var authToken = "$enicom.com.ng"; // Replace with your actual bearer token

            // AJAX request
            $.ajax({
                type: "POST",
                url: "https://dev.system.fulazo.io/v0.1/api/login",
                data: JSON.stringify(userData),
                contentType: "application/json", // Set the content type to JSON
                // headers: {
                //     "Authorization": "Bearer " + authToken
                // },
                success: function(response) {
                    alert(response);
                },
                error: function(xhr, status, error) {
                    alert("Error: " + xhr.responseText);
                }
            });
        }
    </script>
</body>
</html>

<?php

exit;

$months = [
    [
        "dateToPayId" => "1",
        "dueDate" => "31 November 2023",
        "priceExpectedToPay" => "3.00",
        "paymentStatus" => "Pending",
        "remind_a_day_before_status" => "Notification-pending.",
        "remind_a_week_before_status" => "Notification-pending.",
        "token" => "A2009076961",
    ],
    [
        "dateToPayId" => "2",
        "dueDate" => "30 November 2023",
        "priceExpectedToPay" => "3.00",
        "paymentStatus" => "Pending",
        "remind_a_day_before_status" => "Notification-pending.",
        "remind_a_week_before_status" => "Notification-pending.",
        "token" => "A2009076961",
    ],
];

$history = [
    [
        "id" => "2",
        "usertoken" => null,
        "token" => "TZK1kYxV",
        "status" => "1",
        "amount" => "100.00",
        "timestamp" => "1",
        "deleted" => null,
        "day" => "30",
        "month" => "November",
        "year" => "2023",
    ],
];

function mergeData($months, $history) {
    // Create a copy of $months to avoid modifying the original array
    $modifiedMonths = $months;

    foreach ($modifiedMonths as &$month) {
        $dueDateParts = explode(" ", $month['dueDate']);
        $monthNumber = date('m', strtotime($dueDateParts[1]));

        $historyFound = false; // Flag to check if history is found for the month

        foreach ($history as $historyItem) {
            if (
                (int) $historyItem['day'] === (int) $dueDateParts[0] &&
                $historyItem['month'] === $dueDateParts[1] &&
                (int) $historyItem['year'] === (int) $dueDateParts[2]
            ) {
                // Merge data if the dates match
                $month['historyData'] = [
                    "id" => $historyItem['id'],
                    "usertoken" => $historyItem['usertoken'],
                    "token" => $historyItem['token'],
                    "status" => $historyItem['status'],
                    "amount" => $historyItem['amount'],
                    "timestamp" => $historyItem['timestamp'],
                    "deleted" => $historyItem['deleted'],
                ];

                $historyFound = true; // Set the flag to true
            }
        }

        // If no history is found, add a status to the month data
        if (!$historyFound) {
            $month['status'] = "No payment history available";
        }
    }

    // Return the modified copy of $months
    return $modifiedMonths;
}

// Call the merge function
$modifiedMonthsData = mergeData($months, $history);

// Print the result
echo "<pre>";
echo json_encode($modifiedMonthsData, JSON_PRETTY_PRINT);
echo "</pre>";

#getAllLoanRecord::This method fetches all Loan records inthe databse

function getAllLoanRecord() {
    $dataArray = array();

    $sql = 'SELECT * FROM loan_records ORDER BY status = 0 DESC';

    try {
        $stmt = $this->conn->query($sql);
        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count === 0) {
            return false;
            exit;
        }

        $loanRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($loanRecords as $allLoanRecords) {
            // Extract additional data
            $verifyNextOfKin = $this->verifyNextOfKin($allLoanRecords['usertoken']);
            $getAllLoanGuarantors = $this->getAllLoanGuarantors($allLoanRecords['token']);
            $getAllLoanColleterals = $this->getAllLoanColleterals($allLoanRecords['token']);
            $getAllRecordsOfMonthExpectedToPay = $this->getAllRecordsOfMonthExpectedToPay($allLoanRecords['token']);
            $getProviderDetails = $this->getProviderInfoByID($allLoanRecords['providerid']);

            // Determine loan status and completion status
            $loanStatus = ($allLoanRecords['status'] == 1) ? 'Approved' : (($allLoanRecords['status'] == 2) ? 'Declined' : 'Pending');
            $isCompletedStatus = ($loanStatus === "Pending") ? 'Waiting for approval' : ($allLoanRecords['isCompletedStatus'] == 1 ? 'Payment completed.' : 'Payment-ongoing.');

            // Prepare the data array
            $array = [
                'packagePlan' => ($allLoanRecords['plan'] > 1) ? ($allLoanRecords['plan'] . ' months') : ($allLoanRecords['plan'] . ' month'),
                'fullname' => $allLoanRecords['fname'],
                'amountToBorrow' => $this->formatCurrency($allLoanRecords['amountToBorrow']),
                'loanStatus' => $loanStatus,
                'isCompletedStatus' => $isCompletedStatus,
                'usertoken' => $allLoanRecords['usertoken'],
                'amount_debited_so_far' => $this->formatCurrency($allLoanRecords['amount_debited_so_far']),
                'means_of_identity' => $allLoanRecords['means_of_identity'],
                'identity_number' => $allLoanRecords['identity_number'],
                'occupation' => $allLoanRecords['occupation'],
                'passport_photo' => $allLoanRecords['imageUrl'],
                'purpose_of_loan' => $allLoanRecords['purpose_of_loan'],
                'token' => $allLoanRecords['token'],
                'nextOfKin' => $verifyNextOfKin,
                'RequestedOn' => $this->formatDate($allLoanRecords['time']),
                'getAllLoanGuarantors' => $getAllLoanGuarantors,
                'getAllLoanColleterals' => $getAllLoanColleterals,
                'getAllRecordsOfMonthExpectedToPay' => $getAllRecordsOfMonthExpectedToPay,
                'preferredProvider' => $getProviderDetails,
            ];

            array_push($dataArray, $array);
        }
    } catch (PDOException $e) {
        return $this->outputData(false, "Error while retrieving loan:" . $e->getMessage(), null);
        exit;
    } finally {
        $stmt = null;
        $this->conn = null;
    }

    return $dataArray;
}

function getAllUserLoanRecord(int $usertoken) {
    $dataArray = array();
    $sql = "SELECT * FROM loan_records WHERE usertoken = :usertoken";
    try {
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':usertoken', $usertoken);
        $stmt->execute();
        $count = $stmt->rowCount();
        if ($count === 0) {
            $_SESSION['err'] = "No record found";
            return false;
        }
        $loanRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($loanRecords as $allLoanRecords) {
            // Extract additional data
            $verifyNextOfKin = $this->verifyNextOfKin($allLoanRecords['usertoken']);
            $getAllLoanGuarantors = $this->getAllLoanGuarantors($allLoanRecords['token']);
            $getAllLoanColleterals = $this->getAllLoanColleterals($allLoanRecords['token']);
            $getAllRecordsOfMonthExpectedToPay = $this->getAllRecordsOfMonthExpectedToPay($allLoanRecords['token']);
            $getProviderDetails = $this->getProviderInfoByID($allLoanRecords['providerid']);

            // Determine loan status and completion status
            $loanStatus = ($allLoanRecords['status'] == 1) ? 'Approved' : (($allLoanRecords['status'] == 2) ? 'Declined' : 'Pending');
            $isCompletedStatus = ($loanStatus === "Pending") ? 'Waiting for approval' : ($allLoanRecords['isCompletedStatus'] == 1 ? 'Payment completed.' : 'Payment-ongoing.');

            // Prepare the data array
            $array = [
                'packagePlan' => ($allLoanRecords['plan'] > 1) ? ($allLoanRecords['plan'] . ' months') : ($allLoanRecords['plan'] . ' month'),
                'fullname' => $allLoanRecords['fname'],
                'amountToBorrow' => $this->formatCurrency($allLoanRecords['amountToBorrow']),
                'loanStatus' => $loanStatus,
                'isCompletedStatus' => $isCompletedStatus,
                'usertoken' => $allLoanRecords['usertoken'],
                'amount_debited_so_far' => $this->formatCurrency($allLoanRecords['amount_debited_so_far']),
                'means_of_identity' => $allLoanRecords['means_of_identity'],
                'identity_number' => $allLoanRecords['identity_number'],
                'occupation' => $allLoanRecords['occupation'],
                'passport_photo' => $allLoanRecords['imageUrl'],
                'purpose_of_loan' => $allLoanRecords['purpose_of_loan'],
                'token' => $allLoanRecords['token'],
                'nextOfKin' => $verifyNextOfKin,
                'RequestedOn' => $this->formatDate($allLoanRecords['time']),
                'getAllLoanGuarantors' => $getAllLoanGuarantors,
                'getAllLoanColleterals' => $getAllLoanColleterals,
                'getAllRecordsOfMonthExpectedToPay' => $getAllRecordsOfMonthExpectedToPay,
                'preferredProvider' => $getProviderDetails,
            ];

            array_push($dataArray, $array);
        }

    } catch (PDOException $e) {
        $_SESSION['err'] = "Error while retreiving loan:" . $e->getMessage();
        return false;
    } finally {
        $stmt = null;
        $this->conn = null;
    }
    return $dataArray;
}
