<?php 

    session_start();
    $username = $_SESSION['username'];
    $role = $_SESSION['role'];

if ($role === 'admin') {
    include 'leftside.php';
} elseif ($role === 'accounts') {
    include 'leftbaraccounts.php';
} elseif ($role === 'branchmanager') {
    include 'leftsidebranch.php';
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'reject') {
    include 'config.php';
    $lead_id = intval($_POST['lead_id']); // Ensure lead_id is an integer
    $rejector = $username; // Get the rejector's username

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO approval_information (LeadID, IsApproved, ApprovedBy) VALUES (?, 0, ?)");
    $stmt->bind_param("is", $lead_id, $rejector);

    $stmt2 = $conn->prepare("UPDATE personalinformation SET StepReached='Rejected', LoanStatus='Rejected' WHERE id=?");
    $stmt2->bind_param("i", $lead_id);

    if ($stmt->execute() && $stmt2->execute()) {
        echo "<div class='alert alert-danger'>Application rejected successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
    $stmt2->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Loans</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .page-title-right{
            margin-top:50px;
        }
    </style>
</head>

<body>
    

    <div class="container mt-4" style="margin-top:60px !important;">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Disbursals</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <input type="text" id="search-input" class="form-control" placeholder="Search by LeadID, Full Name, Email, or Phone Number">
            </div>
        </div>

        <?php
        // Include config.php to establish database connection
        include 'config.php';

        // Fetch data from approval_information table with the necessary checks
        $sqlApproval = "SELECT * FROM approval_information WHERE (IsApproved = 1 AND isDisbursed = 0)";
        $resultApproval = mysqli_query($conn, $sqlApproval);

        // Check if any rows were returned from the approval_information table
        if (mysqli_num_rows($resultApproval) > 0) {
            echo '<div class="table-responsive" id="loans-table">';
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

            // Output data of each row from approval_information for approved loans
            while ($rowApproval = mysqli_fetch_assoc($resultApproval)) {
                // Fetch data from personalinformation based on LeadID and check LoanStatus and StepReached
                $sqlPersonal = "SELECT ID, FullName, Email, PhoneNumber, bank_account_no, ifsc, bank_name, LoanStatus, StepReached 
                                FROM personalinformation 
                                WHERE ID = " . $rowApproval["LeadID"] . " 
                                AND LoanStatus != 'Rejected' 
                                AND StepReached != 'Rejected'";
                $resultPersonal = mysqli_query($conn, $sqlPersonal);
                $rowPersonal = mysqli_fetch_assoc($resultPersonal);

                // Fetch loan details from loandetails based on LeadID
                $sqlLoanDetails = "SELECT LoanAmount, LoanPurpose FROM loandetails WHERE ID = " . $rowApproval["LeadID"];
                $resultLoanDetails = mysqli_query($conn, $sqlLoanDetails);
                $rowLoanDetails = mysqli_fetch_assoc($resultLoanDetails);

                // Only display if the LeadID exists in the personal information with valid statuses
                if ($rowPersonal && $rowLoanDetails) {
                    echo '<tr>';
                    echo '<td>' . $rowApproval["ID"] . '</td>';
                    echo '<td>' . $rowApproval["LeadID"] . '</td>';
                    echo '<td>' . $rowPersonal["FullName"] . '</td>';
                    echo '<td>' . $rowPersonal["Email"] . '</td>';
                    echo '<td>' . $rowPersonal["PhoneNumber"] . '</td>';
                    echo '<td>' . $rowPersonal["bank_account_no"] . '</td>';
                    echo '<td>' . $rowPersonal["ifsc"] . '</td>';
                    echo '<td>' . $rowPersonal["bank_name"] . '</td>';
                    echo '<td>' . $rowLoanDetails["LoanAmount"] . '</td>';
                    echo '<td>' . $rowLoanDetails["LoanPurpose"] . '</td>';
                    echo '<td>
                        <button class="btn btn-primary disburse-btn" data-leadid="' . $rowApproval["LeadID"] . '" data-loanamount="' . $rowLoanDetails["LoanAmount"] . '" data-loanpurpose="' . $rowLoanDetails["LoanPurpose"] . '">Disburse</button>
                        <form method="POST" class="d-inline reject-form" onsubmit="return confirm(\'Are you sure you want to reject this application?\');">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="lead_id" value="' . $rowApproval["LeadID"] . '">
                            <button type="submit" class="btn btn-danger reject-btn">Reject</button>
                        </form>
                    </td>';
                    echo '</tr>';
                }
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
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Handle search input
            $('#search-input').on('keyup', function() {
                var query = $(this).val();
                $.ajax({
                    url: 'fetch_approved_loans.php',
                    method: 'POST',
                    data: { query: query },
                    success: function(data) {
                        $('#loans-table').html(data);
                    }
                });
            });

            // Handle disburse button click
            $(document).on('click', '.disburse-btn', function() {
                var leadId = $(this).data('leadid');
                var loanAmount = $(this).data('loanamount');
                var loanPurpose = $(this).data('loanpurpose');

                // Redirect to EMI.php with query parameters
                window.location.href = 'EMI.php?leadId=' + leadId + '&loanAmount=' + loanAmount + '&loanPurpose=' + loanPurpose;
            });
        });
    </script>
</body>

</html>
