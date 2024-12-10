<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Legal Verifications</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
        .table-container {
            margin-top: 20px;
        }
        .table thead th {
            background-color: #007bff;
            color: white;
        }
        /*mandeep css*/
        body[data-sidebar-size=sm] .vertical-menu {
            left:0px !important;
        }
        .vertical-menu{
            left:0px !important;
        }
    </style>
</head>
<body>

<div class="container">
    <?php 
        include 'leftside.php';
    ?>
    <h1 class="text-center">Pending Legal Verifications</h1>

    <?php
    // Database connection
    include 'config.php';

    // Fetch LeadIDs from personalinformation table where LeadStatus is Hot Lead and LoanPurpose is LAP
    $leadIDsQuery = "SELECT pi.ID as LeadID, pi.FullName
                     FROM personalinformation pi
                     WHERE pi.LeadStatus='Hot Lead' AND pi.LoanPurpose='LAP' AND pi.LoanStatus NOT IN ('Rejected','Disbursed')";
    $leadIDsResult = $conn->query($leadIDsQuery);
    $leadIDs = [];
    while ($row = $leadIDsResult->fetch_assoc()) {
        $leadIDs[] = $row;
    }

    $totalPendingLegalVerifications = 0;
    $totalNotInitiatedLegalVerifications = 0;

    if ($leadIDsResult->num_rows > 0) {
        echo '<div class="table-container">';
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>LeadID</th><th>FullName</th><th>Legal Verification Status</th></tr></thead>';
        echo '<tbody>';

        // Output leadIDs and their legal verification status
        foreach ($leadIDs as $lead) {
            $leadID = $lead["LeadID"];
            $fullName = $lead["FullName"];

            // Check if leadID is present in legal_evaluations table
            $legalQuery = "SELECT * FROM legal_evaluations WHERE lead_id = $leadID";
            $legalResult = $conn->query($legalQuery);

            if ($legalResult->num_rows > 0) {
                $legalRow = $legalResult->fetch_assoc();
                $statusMessages = [];

                // Check statuses of documents
                if ($legalRow["registree_status"] == 'Pending' || $legalRow["registree_status"] == 'Rejected') {
                    $statusMessages[] = "Registree: " . $legalRow["registree_status"];
                }
                if ($legalRow["fard_status"] == 'Pending' || $legalRow["fard_status"] == 'Rejected') {
                    $statusMessages[] = "Fard: " . $legalRow["fard_status"];
                }
                if ($legalRow["noc_status"] == 'Pending' || $legalRow["noc_status"] == 'Rejected') {
                    $statusMessages[] = "NOC: " . $legalRow["noc_status"];
                }
                if ($legalRow["old_registree_status"] == 'Pending' || $legalRow["old_registree_status"] == 'Rejected') {
                    $statusMessages[] = "Old Registree: " . $legalRow["old_registree_status"];
                }
                if ($legalRow["video_status"] == 'Pending' || $legalRow["video_status"] == 'Rejected') {
                    $statusMessages[] = "Video: " . $legalRow["video_status"];
                }

                if (!empty($statusMessages)) {
                    $totalPendingLegalVerifications++;
                    $status = implode('<br>', $statusMessages);

                    echo '<tr>';
                    echo '<td>' . $leadID . '</td>';
                    echo '<td>' . $fullName . '</td>';
                    echo '<td>' . $status . '</td>';
                    echo '</tr>';
                }
            } else {
                $totalNotInitiatedLegalVerifications++;
                $status = "Legal Verification Not Initiated Yet";

                echo '<tr>';
                echo '<td>' . $leadID . '</td>';
                echo '<td>' . $fullName . '</td>';
                echo '<td>' . $status . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table>';
        echo '</div>';
    } else {
        echo "<p class='text-center'>No records found</p>";
    }

    // Display total counts only if there are pending or not initiated verifications
    $conn->close();
    ?>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
