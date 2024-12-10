<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lead_id = $_POST['lead_id'];
    $account_number = $_POST['account_number'];
    $bank_name = $_POST['bank_name'];
    $ifsc_code = $_POST['ifsc_code']; // IFSC code now stored as branch code
    $account_type = $_POST['account_type'];
    $account_holder_name = $_POST['account_holder_name'];

    // Insert account information into the database with IFSC code stored as branch code
    $sql = "INSERT INTO account_information (LeadID, AccountNumber, BankName, BranchCode, AccountType, AccountHolderName) 
            VALUES ('$lead_id', '$account_number', '$bank_name', '$ifsc_code', '$account_type', '$account_holder_name')";

    if ($conn->query($sql) === TRUE) {
        echo "Account information added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close database connection
    $conn->close();
}
?>
