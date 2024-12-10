<?php
// Include database configuration file
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'branchmanager'])) {
    header("Location: index.php");
    exit();
}

$approver = $_SESSION['username']; 
$role = $_SESSION['role'];

include('config.php');

date_default_timezone_set('Asia/Kolkata');

// Set the time zone to Asia/Kolkata in MySQL
$conn->query("SET time_zone = '+05:30'");

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch users based on filter condition (removed filter handling)
$sql = "SELECT p.ID, p.FullName, p.PhoneNumber, l.LoanAmount, l.LoanPurpose, 
               v.verificationStatus_Home AS PhysicalVerificationStatusHome, 
               v.verificationStatus_Home_COAPP AS PhysicalVerificationStatusHome_COAPP, 
               v.verificationStatus_Business AS PhysicalVerificationStatusBusiness, 
               v.verificationStatus_Business_COAPP AS PhysicalVerificationStatusBusiness_COAPP,
               le.evaluation_needed,
               le.registree_status,
               le.fard_status,
               le.noc_status,
               le.old_registree_status,
               (CASE 
                    WHEN le.evaluation_needed = 1 THEN 
                        (SELECT status FROM evaluation_reports WHERE lead_id = p.ID ORDER BY created_at DESC LIMIT 1)
                    ELSE 
                        NULL
                END) AS evaluationReportStatus
        FROM personalinformation p 
        LEFT JOIN loandetails l ON p.ID = l.ID 
        LEFT JOIN VerificationForms v ON p.ID = v.leadID
        LEFT JOIN legal_evaluations le ON p.ID = le.lead_id
        WHERE p.ID NOT IN (SELECT LeadID FROM approval_information)
        ORDER BY 
            (CASE 
                WHEN v.verificationStatus_Home = 'Approved' 
                     AND v.verificationStatus_Business = 'Approved'
                     AND le.registree_status = 'Approved'
                     AND le.fard_status = 'Approved'
                     AND le.noc_status = 'Approved'
                     AND le.old_registree_status = 'Approved'
                     THEN 0 
                ELSE 1
            END) ASC";


$result = $conn->query($sql);

// Define display names for documents
$documentDisplayNames = [
    'Document1' => 'Aadhar Card (Applicant)',
    'Document2' => 'Pan Card (Applicant)',
    'Document3' => '3 Cheque (Applicant)',
    'Document4' => 'Aadhar Card (Nominee)',
    'Document5' => 'Pan Card (Nominee)',
    'Document6' => '3 Cheque (Nominee)',
    'Document7' => 'Registree',
    'Document8' => 'Fard',
    'Document9' => 'Stamp Paper',
    'Document10' => 'A/C Statement',
    'Document11' => 'Old Registree',
    'Document12' => 'Electricity Bill',
    'Document13' => 'CIBIL Report',
];

// Process approval action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'approve') {
    $lead_id = intval($_POST['lead_id']); // Ensure lead_id is an integer

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO approval_information (LeadID, IsApproved, ApprovedBy) VALUES (?, 1, ?)");
    $stmt->bind_param("is", $lead_id, $approver);
    
    $stmt2 = $conn->prepare("UPDATE personalinformation SET StepReached='Loan Approved, Disbursal Pending' WHERE id=?");
    $stmt2->bind_param("i", $lead_id);

    if ($stmt->execute()) {
        echo "Application approved successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Verification</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 900px;
            margin-top: 8%;
        }

        .table .thead-dark th {
            color: #fff;
            background-color: #000000;
            border-color: #454d55;
        }

        .lead-status {
            max-width: 150px;
        }

        .action-column {
            width: 180px;
        }

        /* Adjust width of the select element */
        .action-column select {
            width: 130px;
        }

        @media (max-width: 768px) {
            .container {
                margin-top: 20px;
            }

            .container table {
                font-size: 14px;
            }

            .container th,
            .container td {
                padding: 8px;
            }

            .container .action-column {
                width: 150px;
            }

            .container .action-column select {
                width: 110px;
            }
        }
        .page-title-box {
            padding-top: 50px !important;
        }
    </style>
</head>
<body>
    <?php
    if($role==='admin'){
        include 'leftside.php';
    }
    elseif($role==='branchmanager'){
        include 'leftsidebranch.php';
    }
    ?>
    <div class="container mt-4" style="margin-top: 9% !important">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Approve Verified Loans</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <input type="text" class="form-control mb-3" id="query" placeholder="Search by ID or Name">
        <?php
        if ($result) {
            if ($result->num_rows > 0) {
                // Output table headers and user data
                echo "<table class='table' id='tableContainer'>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Mobile Number</th>
                                <th>Loan Amount</th>
                                <th>Loan Purpose</th>
                                <th>Physical Verification Status (Home)</th>
                                <th>Physical Verification Status (Business)</th>
                                <th>Documents</th>
                                <th>Verification Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>";

                while ($row = $result->fetch_assoc()) {
                    // Fetch and display documents and their verification status
                    $documents_sql = "SELECT * FROM documentcollection WHERE LeadID = " . $row['ID'];
                    $documents_result = $conn->query($documents_sql);
                    $documents_list = "";
                    $digital_verification_completed = true; // Flag to track digital verification completion

                    while ($doc_row = $documents_result->fetch_assoc()) {
                        foreach ($documentDisplayNames as $key => $displayName) {
                            if (!empty($doc_row[$key])) {
                                $documents_list .= "<a href='" . $doc_row[$key] . "' target='_blank'>" . $displayName . "</a><br>";
                                // Check if the document's status is not 'Accepted'
                                $status_key = 'Status' . substr($key, -1); // Get the corresponding status key
                                if (isset($doc_row[$status_key]) && $doc_row[$status_key] !== 'Accepted') {
                                    $digital_verification_completed = false;
                                }
                            }
                        }
                    }

                    // Initialize the verification statuses
                    $legal_verification_completed = true;
                    $evaluation_report_present = false;
                    $evaluation_report_completed = true;

                    // Check legal verification statuses
                    if ($row['LoanPurpose'] == 'LAP') {
                        $legal_evaluations_sql = "SELECT * FROM legal_evaluations WHERE lead_id = " . $row['ID'];
                        $legal_evaluations_result = $conn->query($legal_evaluations_sql);

                        if ($legal_evaluations_result->num_rows > 0) {
                            $legal_row = $legal_evaluations_result->fetch_assoc();
                            if ($legal_row['registree_status'] !== 'Approved' || $legal_row['fard_status'] !== 'Approved' || $legal_row['noc_status'] !== 'Approved' || $legal_row['old_registree_status'] !== 'Approved') {
                                $legal_verification_completed = false;
                            }
                        } else {
                            $legal_verification_completed = false;
                        }
                    }

                    // Check for evaluation report status
                    if ($row['evaluation_needed'] == 1) {
                        $evaluation_report_present = true;
                        if ($row['evaluationReportStatus'] !== 'Approved') {
                            $evaluation_report_completed = false;
                        }
                    }


                    // Determine the final verification status
                    $verification_status = $digital_verification_completed && 
                                           $row['PhysicalVerificationStatusHome'] == 'Approved' && 
                                           $row['PhysicalVerificationStatusBusiness'] == 'Approved' && 
                                           $row['PhysicalVerificationStatusHome_COAPP'] == 'Approved' && 
                                           $row['PhysicalVerificationStatusBusiness_COAPP'] == 'Approved' &&
                                           $legal_verification_completed &&
                                           $evaluation_report_completed;

                    // Display the button or pending status based on verification status and evaluation report
                    if ($verification_status) {
                        echo "<tr data-approval-status='approved'>
                                <td>{$row['ID']}</td>
                                <td>{$row['FullName']}</td>
                                <td>{$row['PhoneNumber']}</td>
                                <td>{$row['LoanAmount']}</td>
                                <td>{$row['LoanPurpose']}</td>
                                <td>Applicant:{$row['PhysicalVerificationStatusHome']}<br>CO-Applicant:{$row['PhysicalVerificationStatusHome']}</td>
                                <td>Applicant:{$row['PhysicalVerificationStatusBusiness']}<br>CO-Applicant:{$row['PhysicalVerificationStatusBusiness']}</td>
                                <td>{$documents_list}</td>
                                <td>All Verifications Completed</td>
                                <td><button type='button' class='btn btn-primary' onclick='proceedApproval({$row['ID']})'>Proceed for Approval</button></td>
                            </tr>";
                    } else {
                        echo "<tr data-approval-status='pending'>
                                <td>{$row['ID']}</td>
                                <td>{$row['FullName']}</td>
                                <td>{$row['PhoneNumber']}</td>
                                <td>{$row['LoanAmount']}</td>
                                <td>{$row['LoanPurpose']}</td>
                                <td>Applicant:{$row['PhysicalVerificationStatusHome']}<br>CO-Applicant:{$row['PhysicalVerificationStatusHome_COAPP']}</td>
                                <td>Applicant:{$row['PhysicalVerificationStatusBusiness']}<br>CO-Applicant:{$row['PhysicalVerificationStatusBusiness_COAPP']}</td>
                                <td>{$documents_list}</td>
                                <td>Verification Pending</td>
                                <td>Verification Pending</td>
                            </tr>";
                    }
                }

                echo "</tbody>
                    </table>";
            } else {
                echo "No records found.";
            }
        } else {
            echo "Error: " . $conn->error;
        }

        // Close database connection
        $conn->close();
        ?>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
    var rows = $('#tableContainer tbody tr').get();
    rows.sort(function(a, b) {
        var statusA = $(a).data('approval-status');
        var statusB = $(b).data('approval-status');
        if (statusA === 'approved' && statusB !== 'approved') {
            return -1;
        }
        if (statusB === 'approved' && statusA !== 'approved') {
            return 1;
        }
        return 0; // Keep original order if both are the same
    });
    $.each(rows, function(index, row) {
        $('#tableContainer tbody').append(row); // Append each row in the new order
    });
});

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
            $("#query").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#tableContainer tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>
</body>
</html>
