<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

$query = isset($_POST['query']) ? $_POST['query'] : '';

$sql = "SELECT ai.ID as ApprovalID, ai.LeadID, pi.FullName, pi.Email, pi.PhoneNumber, pi.bank_account_no, pi.ifsc, pi.bank_name, ld.LoanAmount, ld.LoanPurpose 
        FROM approval_information ai 
        JOIN personalinformation pi ON ai.LeadID = pi.ID 
        JOIN loandetails ld ON ai.LeadID = ld.ID 
        WHERE ai.IsApproved = 1 
        AND ai.isDisbursed = 0 
        AND pi.LoanStatus != 'Rejected' 
        AND pi.StepReached != 'Rejected'";

// Check if a search query was provided and update the SQL accordingly
if ($query != '') {
    $sql .= " AND (pi.FullName LIKE '%" . mysqli_real_escape_string($conn, $query) . "%' 
                OR pi.Email LIKE '%" . mysqli_real_escape_string($conn, $query) . "%' 
                OR pi.PhoneNumber LIKE '%" . mysqli_real_escape_string($conn, $query) . "%' 
                OR ai.LeadID LIKE '%" . mysqli_real_escape_string($conn, $query) . "%')";
}

$resultApproval = mysqli_query($conn, $sql);

// Check for SQL query errors
if (!$resultApproval) {
    die("Error in SQL query: " . mysqli_error($conn));
}

// Check if any rows were returned and output the data in a table
if (mysqli_num_rows($resultApproval) > 0) {
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped">';
    echo '<thead class="thead-dark">';
    echo '<tr>';
    echo '<th>Approval ID</th>';
    echo '<th>LeadID</th>';
    echo '<th>FullName</th>';
    echo '<th>Email</th>';
    echo '<th>PhoneNumber</th>';
    echo '<th>Bank Account No</th>';
    echo '<th>IFSC</th>';
    echo '<th>Bank Name</th>';
    echo '<th>Loan Amount</th>';
    echo '<th>Loan Purpose</th>';
    echo '<th>Action</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Output data for each row
    while ($row = mysqli_fetch_assoc($resultApproval)) {
        echo '<tr>';
        echo '<td>' . $row["ApprovalID"] . '</td>';
        echo '<td>' . $row["LeadID"] . '</td>';
        echo '<td>' . $row["FullName"] . '</td>';
        echo '<td>' . $row["Email"] . '</td>';
        echo '<td>' . $row["PhoneNumber"] . '</td>';
        echo '<td>' . $row["bank_account_no"] . '</td>';
        echo '<td>' . $row["ifsc"] . '</td>';
        echo '<td>' . $row["bank_name"] . '</td>';
        echo '<td>' . $row["LoanAmount"] . '</td>';
        echo '<td>' . $row["LoanPurpose"] . '</td>';
        echo '<td><button class="btn btn-primary disburse-btn" data-leadid="' . $row["LeadID"] . '" data-loanamount="' . $row["LoanAmount"] . '" data-loanpurpose="' . $row["LoanPurpose"] . '">Disburse</button></td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
} else {
    echo "<div class='alert alert-info'>No approved loans found.</div>";
}

// Close the database connection
mysqli_close($conn);
?>
