<?php
include 'config.php'; // Include your database configuration file

// Function to redirect to show_loan_data.php
function redirectToShowLoanData() {
    header("Location: show_loan_data.php");
    exit; // Ensure script execution stops after redirection
}

// Fetch all data from the database
$sql = "SELECT * FROM emi_schedule";
$result = $conn->query($sql);

// Check if any data exists
if ($result->num_rows > 0) {
    // Data found, display it in a table
    echo '<table class="styled-table">';
    echo '<tr><th>ID</th><th>LeadID</th><th>LoanAmount</th><th>SanctionedAmount</th><th>LoanPurpose</th><th>Action</th></tr>'; // Add other column headers as needed
    while ($row = $result->fetch_assoc()) {
        // Output each row of data
        echo '<tr>';
        echo '<td>' . $row['ID'] . '</td>';
        echo '<td>' . $row['LeadID'] . '</td>';
        echo '<td>' . $row['LoanAmount'] . '</td>';
        echo '<td>' . $row['sanctionedAmount'] . '</td>';
        echo '<td>' . $row['LoanPurpose'] . '</td>';
        // Add action buttons with form submission
        echo '<td>';
        echo '<form method="post" onsubmit="redirectToShowLoanData()">';
        echo '<input type="hidden" name="loanId" value="' . $row['ID'] . '">';
        echo '<button class="disbursal-btn" type="submit" name="action" value="disbursal">Disbursal</button>';
        echo '</form>';
        echo '</td>';
        // Add other columns as needed
        echo '</tr>';
    }
    echo '</table>';
} else {
    // No data found
    echo 'No loan data found';
}

// Close the database connection
$conn->close();
?>
<style>
/* CSS for styling the table */
.styled-table {
    width: 100%;
    border-collapse: collapse;
    border: 2px solid #1C84EE;
    border-radius: 8px;
    overflow: hidden;
    margin-top: 20px;
}

.styled-table th, .styled-table td {
    padding: 12px;
    text-align: left;
}

.styled-table th {
    background-color: #1C84EE;
    color: white;
}

.styled-table tbody tr:nth-child(even) {
    background-color: #f2f2f2;
}

.styled-table tbody tr:hover {
    background-color: #d9edf7;
}

.disbursal-btn {
    border: none;
    padding: 8px 16px;
    cursor: pointer;
    font-size: 14px;
    margin-right: 8px;
    border-radius: 4px;
    background-color: #1C84EE;
    color: white;
}

.disbursal-btn:hover {
    background-color: #0f5c8a;
}
</style>
