<?php
session_start();
$username = $_SESSION['username'];
$role = $_SESSION['role'];


include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'approve') {
    $lead_id = intval($_POST['lead_id']); // Ensure lead_id is an integer
    $username2 = $_SESSION['username'];
    $approver = $username2; // Get the approver's username

    echo $approver;

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO approval_information (LeadID, IsApproved, ApprovedBy) VALUES (?, 1, ?)");
    $stmt->bind_param("is", $lead_id, $approver);
    
    $stmt2 = $conn->prepare("UPDATE personalinformation SET StepReached='Loan Approved, Disbursal Pending' WHERE id=?");
    $stmt2->bind_param("i", $lead_id);

    if ($stmt->execute() && $stmt2->execute()) {
        echo "<div class='alert alert-success'>Application approved successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
    $stmt2->close();
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'reject') {
    $lead_id = intval($_POST['lead_id']); // Ensure lead_id is an integer
    $username3 = $_SESSION['username'];
    $rejector = $username3; // Get the rejector's username

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
    <title>Display Personal Information</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .vertical-menu.mm-active {
            left: 0px;
        }
        .main-content {
            margin: 20px; /* General margin around main content */
        }

        .table-container {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
            margin-top: 100px !important;
            width: 100%;
            margin: 0 auto;
        }

        .table th {
            background-color: #3f51b5;
            color: white;
            text-transform: uppercase;
            font-weight: 500;
        }

        .action-buttons a {
            margin: 3px;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .view-button {
            background-color: #4CAF50;
            color: white;
        }

        .edit-button {
            background-color: #5adbb5!important;
            color: white;
        }

        .field-button {
            background-color: #a881af !important;
            color: white;
        }

        .legal-button {
            background-color: #dd7973 !important;
            color: white;
        }

        .evaluations-button {
            background-color: #c96161 !important;
            color: white;
        }
        #updateButton{
            background-color:#6d8cf9!important;
        }

        .action-buttons a:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<div class="main-content">
    <?php
    if ($role == 'branchmanager') {
        include 'leftsidebranch.php';
    } else {
        include 'leftside.php';
    }

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if ($role == 'branchmanager') {
        // Fetch the list of salespersons under the logged-in branch manager
        $salespersons = [];
        $salespersonQuery = $conn->prepare("SELECT username FROM users WHERE branchmanager = ?");
        $salespersonQuery->bind_param("s", $username);
        $salespersonQuery->execute();
        $salespersonResult = $salespersonQuery->get_result();

        while ($row = $salespersonResult->fetch_assoc()) {
            $salespersons[] = $row['username'];
        }
        $salespersonQuery->close();

        // Convert the list of salespersons to a comma-separated string for SQL IN clause
        $salespersonList = "'" . implode("','", $salespersons) . "'";

        // Fetch records based on the specified criteria
        $sql = "
            SELECT pi.ID, pi.sno, pi.FullName,pi.StepReached, pi.LoanPurpose, le.evaluation_needed 
            FROM personalinformation pi
            LEFT JOIN legal_evaluations le ON pi.ID = le.lead_id
            WHERE pi.generatedby IN ('Self($username)', $salespersonList) 
            OR pi.assignedto IN ('Self($username)', $salespersonList, '$username')
        ";
    } else {
        $sql = "
            SELECT pi.ID, pi.sno, pi.FullName,pi.StepReached, pi.LoanPurpose, le.evaluation_needed 
            FROM personalinformation pi
            LEFT JOIN legal_evaluations le ON pi.ID = le.lead_id
        ";
    }
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<div class="table-container">';
        echo '<h1 class="mb-4">Applicant Details</h1>';
        echo '<div class="search-container">
            <input type="text" id="search" class="form-control" placeholder="Search...">
        </div>';
        echo '<table class="table table-striped table-bordered" id="resultsContainer">';
        echo '<thead><tr><th>ID</th><th>Sno</th><th>FullName</th><th>Lead Status</th><th>Action</th></tr></thead><tbody>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row["ID"] . '</td>';
            echo '<td>' . $row["sno"] . '</td>';
            echo '<td>' . $row["FullName"] . '</td>';
            echo '<td>' . $row["StepReached"] . '</td>';
            
            echo '<td class="action-buttons">';
            echo '<a class="btn btn-success view-button" href="view.php?id=' . $row["ID"] . '">View Applicant</a>';
            echo '<a class="btn btn-primary edit-button" href="edit.php?id=' . $row["ID"] . '">Documents Uploaded</a>';
            echo '<a class="btn btn-success field-button" href="approve_field.php?id=' . $row["ID"] . '">Field Verifications</a>';
            if ($row["LoanPurpose"] === 'LAP') {
                echo '<a class="btn btn-info legal-button" href="approve_legal.php?id=' . $row["ID"] . '">Legal Verification</a>';
                if ($row["evaluation_needed"] == 1) {
                    echo '<a class="btn btn-info evaluations-button" href="evaluations_approval.php?id=' . $row["ID"] . '">Evaluation Report</a>';
                }
            }
            echo '<a class="btn btn-primary update-button" href="update_form.php?id=' . $row["ID"] . '">Update Profile</a>';

             // Add the reject button here
            echo '<form method="POST" action="reject" style="display:inline;">';
            echo '<input type="hidden" name="lead_id" value="' . $row["ID"] . '">';
            echo '<input type="hidden" name="action" value="reject">';
            echo '<button type="submit" class="btn btn-danger update-button">Reject Loan</button>';
            echo '</form>';
            
            // Check verification status and conditionally display approval buttons
            $sql_verifications = "
                SELECT p.ID, p.FullName, p.PhoneNumber, l.LoanAmount, l.LoanPurpose, 
                       v.verificationStatus_Home AS PhysicalVerificationStatusHome, 
                        v.verificationStatus_Home_COAPP AS PhysicalVerificationStatusHome_COAPP, 
                        v.verificationStatus_Business AS PhysicalVerificationStatusBusiness, 
                        v.verificationStatus_Business_COAPP AS PhysicalVerificationStatusBusiness_COAPP,
                       le.evaluation_needed,
                       (SELECT status FROM evaluation_reports WHERE lead_id = p.ID ORDER BY created_at DESC LIMIT 1) AS evaluationRepsortStatus
                FROM personalinformation p 
                LEFT JOIN loandetails l ON p.ID = l.ID 
                LEFT JOIN VerificationForms v ON p.ID = v.leadID
                LEFT JOIN legal_evaluations le ON p.ID = le.lead_id
                WHERE p.ID = " . $row["ID"];

            $verifications_result = $conn->query($sql_verifications);
            if ($verifications_result->num_rows > 0) {
                $verification_row = $verifications_result->fetch_assoc();

                // Check verification statuses
                $digital_verification_completed = true; // Assuming digital verification is completed for simplicity
                $legal_verification_completed = true;
                $evaluation_report_completed = true;

                // Check legal verification statuses
                if ($verification_row['LoanPurpose'] == 'LAP') {
                    $legal_evaluations_sql = "
                        SELECT registree_status, fard_status, noc_status, old_registree_status, video_status 
                        FROM legal_evaluations 
                        WHERE lead_id = " . $verification_row['ID'];
                    $legal_evaluations_result = $conn->query($legal_evaluations_sql);

                    if ($legal_evaluations_result->num_rows > 0) {
                        $legal_row = $legal_evaluations_result->fetch_assoc();
                        if ($legal_row['registree_status'] !== 'Approved' || $legal_row['fard_status'] !== 'Approved' || $legal_row['noc_status'] !== 'Approved' || $legal_row['video_status'] !== 'Approved' || $legal_row['old_registree_status'] !== 'Approved') {
                            $legal_verification_completed = false;
                        }
                    } else {
                        $legal_verification_completed = false;
                    }
                }

                // Check for evaluation report status
                $evaluation_report_sql = "
                    SELECT status 
                    FROM evaluation_reports 
                    WHERE lead_id = " . $verification_row['ID'] . " 
                    ORDER BY created_at DESC LIMIT 1";
                $evaluation_report_result = $conn->query($evaluation_report_sql);

                if ($evaluation_report_result->num_rows > 0) {
                    $evaluation_row = $evaluation_report_result->fetch_assoc();
                    if ($evaluation_row['status'] !== 'Approved') {
                        $evaluation_report_completed = false;
                    }
                }

                // Determine the final verification status
                $verification_status = $digital_verification_completed && 
                                       $verification_row['PhysicalVerificationStatusHome'] == 'Approved' && 
                                       $verification_row['PhysicalVerificationStatusBusiness'] == 'Approved' &&
                                       $verification_row['PhysicalVerificationStatusHome_COAPP'] == 'Approved' && 
                                       $verification_row['PhysicalVerificationStatusBusiness_COAPP'] == 'Approved' && 
                                       $legal_verification_completed;
                
                if ($verification_row['evaluation_needed'] == 1) {
                    $verification_status = $verification_status && $evaluation_report_completed;
                }

                // Display the "Approve Loan" and "Disburse Loan" buttons based on verification status
                if ($verification_status) {
                    echo '<form method="POST" action="approve" style="display:inline;">';
                    echo '<input type="hidden" name="lead_id" value="' . $row["ID"] . '">';
                    echo '<input type="hidden" name="action" value="approve">';
                    echo '<button type="button" class="btn btn-info" onclick="proceedApproval(' . $row["ID"] . ')">Approve Loan</button>';
                    echo '</form>';
                    $leadId = $verification_row['ID'];
                    $loanAmount = $verification_row['LoanAmount'];
                    $loanPurpose = $verification_row['LoanPurpose'];

                    // Create the button    
                    echo '<button class="btn btn-info update-button" 
                        data-leadid="' . htmlspecialchars($leadId) . '" 
                        data-loanamount="' . htmlspecialchars($loanAmount) . '" 
                        data-loanpurpose="' . htmlspecialchars($loanPurpose) . '">
                        Disburse Loan
                    </button>';

                }
            }

            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-warning mt-4" role="alert">No results found</div>';
    }

    $conn->close();
    ?>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Custom JS for search functionality -->
<script>
    // Function to fetch results based on query
    // Function to fetch results based on query
function fetchResults(query) {
    var xhr = new XMLHttpRequest();
    // If the query is empty, send a request without any query to fetch all results
    var url = query ? 'search.php?q=' + encodeURIComponent(query) : 'search.php';
    xhr.open('GET', url, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('resultsContainer').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}
function proceedApproval(leadID) {
            if (confirm("Are you sure you want to proceed for approval?")) {
                $.ajax({
                    url: "",
                    type: "POST",
                    data: {
                        action: "approve",
                        lead_id: leadID
                    },
                    success: function (response) {
                        alert(response);
                        location.reload();
                    },
                    error: function (xhr, status, error) {
                        alert("An error occurred while processing the request: " + error);
                    }
                });
            }
        }
$(document).ready(function() {
    // Handle disburse button click
    $(document).on('click', '.update-button', function () {
        // Get data attributes
        var leadId = $(this).data('leadid');
        var loanAmount = $(this).data('loanamount');
        var loanPurpose = $(this).data('loanpurpose');

        // Redirect to EMI.php with query parameters
        window.location.href = 'EMI.php?leadId=' + leadId + '&loanAmount=' + loanAmount + '&loanPurpose=' + loanPurpose;
    });

});

// Check if there's a search query in the URL on page load
document.addEventListener('DOMContentLoaded', function() {
    var urlParams = new URLSearchParams(window.location.search);
    var queryParam = urlParams.get('q');
    if (queryParam) {
        document.getElementById('search').value = queryParam; // Set input field value
        fetchResults(queryParam); // Fetch results automatically
    } else {
        // Fetch all results if no query is present
        fetchResults('');
    }
});

// Event listener for input change in search field
document.getElementById('search').addEventListener('input', function() {
    var query = this.value.trim();
    // Fetch results based on query or fetch all results if input is empty
    fetchResults(query);
});


    // Check if there's a search query in the URL on page load
    document.addEventListener('DOMContentLoaded', function() {
        var urlParams = new URLSearchParams(window.location.search);
        var queryParam = urlParams.get('q');
        if (queryParam) {
            document.getElementById('search').value = queryParam; // Set input field value
            fetchResults(queryParam); // Fetch results automatically
        }
    });

    // Event listener for input change in search field
    document.getElementById('search').addEventListener('input', function() {
        var query = this.value.trim();
        if (query.length > 0) {
            fetchResults(query);
        } else {
            document.getElementById('resultsContainer').innerHTML = '';
        }
    });
</script>

</body>
</html>
