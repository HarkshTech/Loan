<?php
// Include database configuration file
include('config.php');

// Fetch the search query if provided
$searchQuery = isset($_GET['query']) ? $_GET['query'] : '';

// Construct the base SQL query
$sql = "SELECT p.ID, p.FullName, p.PhoneNumber, l.LoanAmount, l.LoanPurpose, 
               v.verificationStatus_Home AS PhysicalVerificationStatusHome, 
               v.verificationStatus_Business AS PhysicalVerificationStatusBusiness,
               (SELECT status FROM evaluation_reports WHERE lead_id = p.ID ORDER BY created_at DESC LIMIT 1) AS evaluationReportStatus
        FROM personalinformation p 
        LEFT JOIN loandetails l ON p.ID = l.ID 
        LEFT JOIN VerificationForms v ON p.ID = v.leadID
        WHERE p.ID NOT IN (SELECT LeadID FROM approval_information)";

// Add search filters if a search query is provided
if (!empty($searchQuery)) {
    $searchQueryEscaped = $conn->real_escape_string($searchQuery);
    $sql .= " AND (p.ID LIKE '%$searchQueryEscaped%' OR p.FullName LIKE '%$searchQueryEscaped%')";
}

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

if ($result) {
    if ($result->num_rows > 0) {
        echo "<table class='table'>
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

            // Update verification status based on digital verification completion flag
            $verification_status = $digital_verification_completed ? "Digital Verification Completed" : "Verification Pending";

            // Check for Legal Evaluations only for LAP Loan Purpose
            $legal_verification_completed = true;
            $evaluation_report_completed = true;

            if ($row['LoanPurpose'] == 'LAP') {
                $legal_evaluations_sql = "SELECT registree_status, fard_status, noc_status, old_registree_status, video_status FROM legal_evaluations WHERE lead_id = " . $row['ID'];
                $legal_evaluations_result = $conn->query($legal_evaluations_sql);

                if ($legal_evaluations_result->num_rows > 0) {
                    $legal_row = $legal_evaluations_result->fetch_assoc();
                    if ($legal_row['registree_status'] !== 'Approved' || $legal_row['fard_status'] !== 'Approved' || $legal_row['noc_status'] !== 'Approved' || $legal_row['video_status'] !== 'Approved' || $legal_row['old_registree_status'] !== 'Approved') {
                        $legal_verification_completed = false;
                    }
                } else {
                    $legal_verification_completed = false;
                }

                // Check for evaluation report status
                $evaluation_report_sql = "SELECT status FROM evaluation_reports WHERE lead_id = " . $row['ID'] . " ORDER BY created_at DESC LIMIT 1";
                $evaluation_report_result = $conn->query($evaluation_report_sql);

                if ($evaluation_report_result->num_rows > 0) {
                    $evaluation_row = $evaluation_report_result->fetch_assoc();
                    if ($evaluation_row['status'] !== 'Approved') {
                        $evaluation_report_completed = false;
                    }
                } else {
                    $evaluation_report_completed = false;
                }
            }

            // Add button for Proceed for Approval only if all verifications are completed
            if ($verification_status == "Digital Verification Completed" &&
                $row['PhysicalVerificationStatusHome'] == 'Approved' &&
                $row['PhysicalVerificationStatusBusiness'] == 'Approved' &&
                $legal_verification_completed &&
                $evaluation_report_completed) {
                echo "<tr>
                        <td>{$row['ID']}</td>
                        <td>{$row['FullName']}</td>
                        <td>{$row['PhoneNumber']}</td>
                        <td>{$row['LoanAmount']}</td>
                        <td>{$row['LoanPurpose']}</td>
                        <td>{$row['PhysicalVerificationStatusHome']}</td>
                        <td>{$row['PhysicalVerificationStatusBusiness']}</td>
                        <td>{$documents_list}</td>
                        <td>All Verifications Completed</td>
                        <td><button type='button' class='btn btn-primary approve-btn' data-id='{$row['ID']}'>Proceed for Approval</button></td>
                    </tr>";
            } else {
                // Display the row without the button
                echo "<tr>
                        <td>{$row['ID']}</td>
                        <td>{$row['FullName']}</td>
                        <td>{$row['PhoneNumber']}</td>
                        <td>{$row['LoanAmount']}</td>
                        <td>{$row['LoanPurpose']}</td>
                        <td>{$row['PhysicalVerificationStatusHome']}</td>
                        <td>{$row['PhysicalVerificationStatusBusiness']}</td>
                        <td>{$documents_list}</td>
                        <td>Verification Pending</td>
                        <td>Verification Pending</td>
                    </tr>";
            }
        }

        echo "</tbody></table>";
    } else {
        echo "No users found.";
    }
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close database connection
$conn->close();
?>
