<?php
include 'config.php'; // Include your database configuration file

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'show_disbursal') {
    // Retrieve loanId from the form submission
    $loanId = isset($_POST['loanId']) ? $_POST['loanId'] : '';

    // Fetch data from the database for the given loanId
    $sql = "SELECT * FROM emi_schedule WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $loanId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if data exists for the given loanId
    if ($result->num_rows > 0) {
        // Data found, fetch and save it to another table or perform any other actions as needed
        while ($row = $result->fetch_assoc()) {
            // Example: Insert the data into another table
            $insertSql = "INSERT INTO emi_schedule (LeadID, LoanAmount, sanctionedAmount, LoanPurpose) VALUES (?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($insertSql);
            $stmtInsert->bind_param("sdds", $row['LeadID'], $row['LoanAmount'], $row['sanctionedAmount'], $row['LoanPurpose']);
            $stmtInsert->execute();
            $stmtInsert->close();
        }
        // Display success message in alert
        echo "<script>alert('Data disbursed successfully.'); window.location='show_loan_data.php';</script>";
    } else {
        // No data found for the given loanId
        echo 'No loan data found for Loan ID: ' . $loanId;
    }
    // Close the prepared statement
    $stmt->close();
} else {
    // Redirect the user or handle the error in a different way if the form was not submitted properly
    echo 'Error: Invalid form submission.';
}

// Close the database connection
$conn->close();
?>
