<?php
include 'config.php';

// Define the assigned salesperson based on the logged-in user
session_start(); // Start the session
$loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Define the assigned salesperson for each user
$salespersons = array(
    'sales1' => 'Abhi',
    'sales2' => 'Shikha',
    'sales3' => 'Nav',
    'sales4' => 'Manroop'
);

// Fetch IDs with uploaded documents
$documentIDs = [];
$documentsQuery = $conn->query("SELECT DISTINCT LeadID FROM documentcollection");
if ($documentsQuery->num_rows > 0) {
    while ($row = $documentsQuery->fetch_assoc()) {
        $documentIDs[] = $row['LeadID'];
    }
}

// Fetch person's name for each ID
$personsData = [];
foreach ($documentIDs as $leadID) {
    $personQuery = $conn->query("SELECT FullName FROM personalinformation WHERE ID = '$leadID'");
    if ($personQuery->num_rows > 0) {
        $personData = $personQuery->fetch_assoc();
        $personsData[$leadID] = $personData['FullName'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Document IDs</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 900px;
            margin-top: 8%;

        }

        div#sidebar-menu {
            position: fixed;
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
    <?php include 'leftsidesales.php' ?>
    
    <div class="container" style="margin-left:90px !important;">
        <h1 class="my-4">Documents and Field Verifications</h1>
        <div class="list-group">
            <?php
            if (!empty($documentIDs)) {
                foreach ($documentIDs as $leadID) {
                    $personName = isset($personsData[$leadID]) ? $personsData[$leadID] : 'Unknown';
                    // Check if the logged-in user is a salesperson
                    if ($loggedInUser && array_key_exists($loggedInUser, $salespersons)) {
                        $assignedSalesperson = $salespersons[$loggedInUser];
                        if ($assignedSalesperson == $personName) {
                            echo '<div class="list-group-item">';
                            echo '<div>Complete Verification for ' . $personName . ' (ID: ' . $leadID . ')</div>';
                            echo '<div class="mt-2">';
                            echo '<a href="./verify_documents.php?id=' . $leadID . '" class="btn btn-primary mr-2">Document Verification</a>';
                            echo '<a href="./physicalv.php?id=' . $leadID . '" class="btn btn-primary">Field Verification</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                }
            } else {
                echo '<div class="alert alert-info" role="alert">No documents uploaded for verification.</div>';
            }
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
