<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Loan Details</title>
<style>
  
    .sidebar {
        width: 50px; /* Initial width when collapsed */
        background-color: #192b3c;
        color: #fff;
        transition: width 0.3s ease;
    }
    .sidebar:hover {
        width: 200px; /* Width on hover */
    }
    .content {
        flex: 1;
        padding: 20px; /* Adjust padding as needed */
        transition: margin-left 0.3s ease; /* Smooth transition for margin */
    }
   table {
    margin-top: 8%;
    caption-side: bottom;
    border-collapse: collapse;
    margin-left: 2%;
    width: 96%;
}
    th, td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    th {
        background-color: #f2f2f2;
    }
    .action-buttons button {
        padding: 5px 10px;
        margin-right: 5px;
        border: none;
        cursor: pointer;
        border-radius: 3px;
        background-color: #007bff;
        color: #fff;
    }
    .action-buttons button:hover {
        background-color: #0056b3;
    }
</style>
</head>
<body>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'leftside.php';
// Include config.php to establish database connection
include 'config.php';
?>

<div class="sidebar" id="sidebar">
    <!-- Sidebar content here -->
</div>

<div class="content" id="main-content">

<?php
// Fetch data from the 'approval_information' table
$sqlApproval = "SELECT * FROM approval_information";
$resultApproval = mysqli_query($conn, $sqlApproval);

// Check if any rows were returned from the approval_information table
if (mysqli_num_rows($resultApproval) > 0) {
    echo "<table>
            <tr>
                <th>Approval ID</th>
                <th>LeadID</th>
                <th>IsApproved</th>
                <th>ApprovalTimestamp</th>
                <th>ApprovedBy</th>
                <th>Action</th>
                <th>Loan Type</th>
                <th>Action</th>
            </tr>";

    // Output data of each row from approval_information
    while ($rowApproval = mysqli_fetch_assoc($resultApproval)) {
        echo "<tr>";
        echo "<td>" . $rowApproval["ID"] . "</td>";
        echo "<td>" . $rowApproval["LeadID"] . "</td>";
        echo "<td>" . $rowApproval["IsApproved"] . "</td>";
        echo "<td>" . $rowApproval["ApprovalTimestamp"] . "</td>";
        echo "<td>" . $rowApproval["ApprovedBy"] . "</td>";

        // Fetch corresponding data from loandetails based on LeadID
        $leadID = $rowApproval["LeadID"];
        $sqlLoanDetails = "SELECT * FROM loandetails WHERE ID = $leadID";
        $resultLoanDetails = mysqli_query($conn, $sqlLoanDetails);

        // Check if any rows were returned from loandetails for the LeadID
        if (mysqli_num_rows($resultLoanDetails) > 0) {
            $rowLoanDetails = mysqli_fetch_assoc($resultLoanDetails);
            echo "<td>" . $rowLoanDetails["LoanAmount"] . "</td>";
            echo "<td>" . $rowLoanDetails["LoanPurpose"] . "</td>";
        } else {
            // If no corresponding data found in loandetails, display N/A
            echo "<td colspan='2'>N/A</td>"; // Display N/A for LoanAmount and LoanPurpose
        }

        echo "<td class='action-buttons'>
                <button onclick=\"openDetailsWindow(" . $rowApproval["ID"] . ", '" . $rowApproval["LeadID"] . "', '" . $rowApproval["IsApproved"] . "', '" . $rowApproval["ApprovalTimestamp"] . "', '" . $rowApproval["ApprovedBy"] . "', '" . ($rowLoanDetails["LoanAmount"] ?? '') . "', '" . ($rowLoanDetails["LoanPurpose"] ?? '') . "')\">View</button>
                <button onclick=\"acceptLoan('" . $rowApproval["LeadID"] . "')\">Accept</button>
                <button onclick=\"rejectLoan('" . $rowApproval["LeadID"] . "')\">Reject</button>
              </td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No records found";
}

// Close the database connection
mysqli_close($conn);
?>

</div>

<script>
    function openDetailsWindow(id, leadID, isApproved, approvalTimestamp, approvedBy, loanAmount, loanPurpose) {
        var details = {
            id: id,
            leadID: leadID,
            isApproved: isApproved,
            approvalTimestamp: approvalTimestamp,
            approvedBy: approvedBy,
            loanAmount: loanAmount,
            loanPurpose: loanPurpose
        };
        
        var detailsUrl = "details.php?" + new URLSearchParams(details).toString();
        window.open(detailsUrl, '_blank');
    }

    function acceptLoan(leadID) {
        // Send an AJAX request to process_loan.php with the LeadID and action type
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "process_loan.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    alert(xhr.responseText); // Display the response from process_loan.php
                    // Optionally, reload the page or update UI after successful acceptance
                    location.reload(); // Reload the page to reflect the updated data
                } else {
                    alert('Error accepting loan');
                }
            }
        };
        xhr.send("leadID=" + encodeURIComponent(leadID) + "&action=accept"); // Send LeadID and action type to process_loan.php
    }

    function rejectLoan(leadID) {
        // Send an AJAX request to process_loan.php with the LeadID and action type
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "process_loan.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    alert(xhr.responseText); // Display the response from process_loan.php
                    // Optionally, reload the page or update UI after successful rejection
                    location.reload(); // Reload the page to reflect the updated data
                } else {
                    alert('Error rejecting loan');
                }
            }
        };
        xhr.send("leadID=" + encodeURIComponent(leadID) + "&action=reject"); // Send LeadID and action type to process_loan.php
    }

    // Adjust content margin based on sidebar width
    function adjustContentMargin() {
        var sidebarWidth = document.getElementById('sidebar').offsetWidth;
        document.getElementById('main-content').style.marginLeft = sidebarWidth + 'px';
    }

    window.addEventListener('load', adjustContentMargin);
    window.addEventListener('resize', adjustContentMargin);
</script>

</body>
</html>
