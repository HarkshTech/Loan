<?php
// Include database configuration file
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('config.php');

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch users based on filter condition (removed filter handling)
$sql = "SELECT p.ID, p.FullName, p.PhoneNumber, l.LoanAmount, l.LoanPurpose, 
               v.verificationStatus_Home AS PhysicalVerificationStatusHome, 
               v.verificationStatus_Business AS PhysicalVerificationStatusBusiness
        FROM personalinformation p 
        INNER JOIN loandetails l ON p.ID = l.ID 
        LEFT JOIN VerificationForms v ON p.ID = v.leadID
        WHERE p.ID NOT IN (SELECT LeadID FROM approval_information)";
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
];

// Process approval action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'approve') {
    $lead_id = intval($_POST['lead_id']); // Ensure lead_id is an integer

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO approval_information (LeadID, IsApproved, ApprovedBy) VALUES (?, 1, 'Your Name')");
    $stmt->bind_param("i", $lead_id);

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
            /*margin-left: 20% !important;*/
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
    </style>
</head>
<body>
    <?php include 'leftbarpendingapprovals.php'; ?>
    <div class="container mt-4" style="margin-top: 9% !important">
        <!-- Filter form removed -->

        <?php
        if ($result) {
            if ($result->num_rows > 0) {
                // Output table headers and user data
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

                    // Add button for Proceed for Approval only if both verifications are completed
                    if ($verification_status == "Digital Verification Completed" && $row['PhysicalVerificationStatusHome'] == 'Approved' && $row['PhysicalVerificationStatusBusiness'] == 'Approved') {
                        echo "<tr>
                                <td>{$row['ID']}</td>
                                <td>{$row['FullName']}</td>
                                <td>{$row['PhoneNumber']}</td>
                                <td>{$row['LoanAmount']}</td>
                                <td>{$row['LoanPurpose']}</td>
                                <td>{$row['PhysicalVerificationStatusHome']}</td>
                                <td>{$row['PhysicalVerificationStatusBusiness']}</td>
                                <td>{$documents_list}</td>
                                <td>{$verification_status}</td>
                                <td><button type='button' class='btn btn-primary' onclick='proceedApproval({$row['ID']})'>Proceed for Approval</button></td>
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
                                <td>{$verification_status}</td>
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
    </div>

    <!-- Bootstrap JS CDN and other scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function proceedApproval(id) {
            if (confirm('Are you sure you want to proceed with approval for ID ' + id + '?')) {
                console.log("Lead ID:", id); // Log the lead_id
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $_SERVER["PHP_SELF"]; ?>',
                    data: { action: 'approve', lead_id: id },
                    success: function(response) {
                        // console.log(response); // Log the response for debugging
                        if (response.includes("successfully")) {
                            alert("Application approved successfully!");
                            location.reload(); // Reload the page after successful approval
                        } else {
                            alert("Error: " + response); // Display other responses if needed
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error processing approval:', error);
                        alert('Error processing approval: ' + error);
                    }
                });
            }
        }
    </script>
</body>
</html>
