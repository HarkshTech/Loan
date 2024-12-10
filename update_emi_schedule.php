<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'config.php';
$source = $_POST['source'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from AJAX request
    if ($source === 'EMI.php') {
        $leadId = $_POST['leadId'];
        $loanAmount = $_POST['loanAmount'];
        $loanPurpose = $_GET['loanPurpose'];
        $sanctionedAmount = $_POST['sanctionedAmount'];
        $tenure = $_POST['tenure'];
        $interestRate = $_POST['interestRate'];
        $emidate = $_POST['emidate'];
    } elseif ($source === 'EMI-new.php') {
        $leadId = $_POST['leadId'];
        $loanAmount = $_POST['loanAmount'];
        $loanPurpose = $_POST['loanPurpose'];
        $sanctionedAmount = $_POST['sanctionedAmount'];
        $tenure = $_POST['tenure'];
        $interestRate = $_POST['interestRate'];
        $emidate = $_POST['emidate'];
    }

    // Determine the next payment date (next consecutive month's 5th day)
    $nextPaymentDate = $emidate;

    // Calculate monthly installment (EMI)
    $monthlyInterest = $sanctionedAmount * ($interestRate / 100) / 12; // Monthly interest
    if ($source === 'EMI.php') {
        $emiAmount = ($sanctionedAmount / $tenure) + $monthlyInterest;
    } elseif ($source === 'EMI-new.php') {
        $emiAmount = $_POST['emiAmount'];
    }

    // Round off EMI amount as per rules
    $emiAmount = round($emiAmount); // Rounds to nearest integer, >=0.50 ceils, <0.50 floors

    // Prepare and execute the SQL INSERT query
    $sqlInsert = "INSERT INTO emi_schedule (LeadID, datestarted, LoanAmount, LoanPurpose, InterestRate, TotalEMIs, PaidEMIs, EMIAmount, Status, sanctionedAmount, NextPaymentDate) 
                  VALUES ('$leadId', '$nextPaymentDate', '$loanAmount', '$loanPurpose', $interestRate, '$tenure', '0', '$emiAmount', 'Active', '$sanctionedAmount', '$nextPaymentDate')";

    if ($conn->query($sqlInsert) === TRUE) {
        // Update approval_information table for the given leadId
        $sqlUpdate = "UPDATE approval_information SET IsDisbursed = 1 WHERE LeadID = $leadId";

        if ($conn->query($sqlUpdate) === TRUE) {
            echo "New record inserted successfully and approval information updated.";
        } else {
            echo "Error updating approval information: " . $conn->error;
        }

        $sqlUpdate2 = "UPDATE personalinformation SET LoanStatus = 'Active', StepReached='Disbursed' WHERE ID = $leadId";
        $conn->query($sqlUpdate2);
    } else {
        echo "Error inserting record: " . $conn->error;
    }
}

$conn->close();
?>
