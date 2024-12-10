<?php
include 'config.php';

// Retrieve data from the AJAX request
$leadId = $_POST['leadId'];
$loanAmount = $_POST['loanAmount'];
$loanPurpose = $_POST['loanPurpose'];
$tenure = $_POST['tenure'];
$sanctionedAmount = $_POST['sanctionedAmount'];
$emiAmount = $_POST['emiAmount'];
$interestRate = $_POST['interestRate'];
$emidate = $_POST['emidate'];

// Round off the EMI amount
$emiAmount = round($emiAmount); // Rounds to the nearest integer

// Insert data into the database
$sql = "INSERT INTO sanction_approvals (LeadID, LoanAmount, LoanPurpose, Tenure, SanctionedAmount, EMIAmount, firstemidate, InterestRate) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Bind parameters
    $stmt->bind_param("sdsdddss", $leadId, $loanAmount, $loanPurpose, $tenure, $sanctionedAmount, $emiAmount, $emidate, $interestRate);
    
    // Execute the statement and check for success
    if ($stmt->execute()) {
        echo "Data saved to database successfully!";
    } else {
        echo "Error saving data: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    echo "Failed to prepare the SQL statement.";
}

$conn->close();
?>
