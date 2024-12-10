<?php
session_start();
include 'config.php';

$query = isset($_GET['q']) ? $_GET['q'] : '';
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// SQL query based on user role
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

    // Base SQL query without search conditions
    $sql = "
        SELECT pi.ID, pi.sno, pi.FullName, pi.StepReached, pi.LoanPurpose, le.evaluation_needed 
        FROM personalinformation pi
        LEFT JOIN legal_evaluations le ON pi.ID = le.lead_id
        WHERE (pi.generatedby IN ('Self($username)', $salespersonList) 
               OR pi.assignedto IN ('Self($username)', $salespersonList, '$username'))
    ";

    // Add search conditions only if a query is provided
    if (!empty($query)) {
        $sql .= " AND (pi.ID LIKE ? OR pi.FullName LIKE ? OR pi.LoanPurpose LIKE ?)";
    }
} else {
    // Base SQL query for non-branch managers without search conditions
    $sql = "
        SELECT pi.ID, pi.sno, pi.FullName, pi.StepReached, pi.LoanPurpose, le.evaluation_needed 
        FROM personalinformation pi
        LEFT JOIN legal_evaluations le ON pi.ID = le.lead_id
    ";

    // Add search conditions only if a query is provided
    if (!empty($query)) {
        $sql .= " WHERE pi.ID LIKE ? OR pi.FullName LIKE ? OR pi.LoanPurpose LIKE ?";
    }
}

// Prepare and execute the statement
if (!empty($query)) {
    $stmt = $conn->prepare($sql);
    $searchTerm = '%' . $query . '%';
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare($sql);  // No parameters when there is no search query
}

$stmt->execute();
$result = $stmt->get_result();

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

// Output the results
if ($result->num_rows > 0) {
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
        
        // Reject Loan button
        echo '<form method="POST" action="reject" style="display:inline-block;">';
        echo '<input type="hidden" name="lead_id" value="' . $row["ID"] . '">';
        echo '<input type="hidden" name="action" value="reject">';
        echo '<button type="submit" class="btn btn-danger">Reject Loan</button>';
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
                echo '<form method="POST" action="approve">';
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
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
} else {
    echo '<div class="alert alert-warning mt-4" role="alert">No results found</div>';
}

$stmt->close();
$conn->close();
?>
