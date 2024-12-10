<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$loggedInUser = $_SESSION['username'];
$role = $_SESSION['role']; // Retrieve user role

// Fetch salespersons under the logged-in user (branch manager)
$salespersons = [];
$salespersonsQuery = $conn->query("SELECT username FROM users WHERE branchmanager = '$loggedInUser' AND role='sales'");
if ($salespersonsQuery->num_rows > 0) {
    while ($row = $salespersonsQuery->fetch_assoc()) {
        $salespersons[] = $row['username'];
    }
}

// Convert the salespersons array to a string for SQL IN clause
$salespersonsList = "'" . implode("','", $salespersons) . "'";
$selfGeneratedBy = implode(", ", array_map(function($value) {
    return "'Self($value)'";
}, $salespersons));

// Fetch lead IDs associated with the logged-in user and their salespersons
$leadIDs = [];
$personalInfoQuery = $conn->query("SELECT ID FROM personalinformation WHERE generatedby = 'Self($loggedInUser)' OR generatedby IN ($selfGeneratedBy) OR assignedto IN ($salespersonsList,'$loggedInUser')");
if ($personalInfoQuery->num_rows > 0) {
    while ($row = $personalInfoQuery->fetch_assoc()) {
        $leadIDs[] = $row['ID'];
    }
}

// Fetch IDs with uploaded documents
$documentIDs = [];
if (!empty($leadIDs)) {
    $leadIDsList = implode(',', $leadIDs);
    $documentsQuery = $conn->query("SELECT DISTINCT LeadID FROM documentcollection WHERE LeadID IN ($leadIDsList)");
    if ($documentsQuery->num_rows > 0) {
        while ($row = $documentsQuery->fetch_assoc()) {
            $documentIDs[] = $row['LeadID'];
        }
    }
}

// Fetch person's name and LoanPurpose for each ID
$personsData = [];
if (!empty($documentIDs)) {
    $ids = implode(',', $documentIDs);
    $personQuery = $conn->query("SELECT ID, FullName, LoanPurpose FROM personalinformation WHERE ID IN ($ids)");
    while ($row = $personQuery->fetch_assoc()) {
        $personsData[$row['ID']] = [
            'FullName' => $row['FullName'],
            'LoanPurpose' => $row['LoanPurpose']
        ];
    }
}

// Fetch status and remarks for each ID from legal evaluations
$statusRemarksData = [];
$evaluationNeededData = [];
if (!empty($documentIDs)) {
    $ids = implode(',', $documentIDs);
    $statusQuery = $conn->query("SELECT lead_id, registree_status, registree_remarks, fard_status, fard_remarks, noc_status, noc_remarks, video_status, video_remarks, evaluation_needed FROM legal_evaluations WHERE lead_id IN ($ids)");
    if ($statusQuery->num_rows > 0) {
        while ($row = $statusQuery->fetch_assoc()) {
            $statusRemarksData[$row['lead_id']] = $row;
            $evaluationNeededData[$row['lead_id']] = $row['evaluation_needed'];
        }
    }
}

// Fetch status and remarks for each ID from field evaluations
$fieldEvaluationData = [];
if (!empty($documentIDs)) {
    $ids = implode(',', $documentIDs);
    $fieldQuery = $conn->query("SELECT leadID, verificationStatus_Home, verificationNotes_Home, verificationStatus_Business, businessVerificationNotes FROM VerificationForms WHERE leadID IN ($ids)");
    if ($fieldQuery->num_rows > 0) {
        while ($row = $fieldQuery->fetch_assoc()) {
            $fieldEvaluationData[$row['leadID']] = $row;
        }
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

        .chip {
            display: inline-block;
            padding: 0.25em 0.75em;
            font-size: 0.875em;
            border-radius: 0.5em;
            margin: 0.25em;
        }

        .chip.completed {
            background-color: #28a745;
            color: white;
        }

        .chip.pending {
            background-color: #ffc107;
            color: white;
        }

        .chip.rejected {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>

<body>
    <?php include 'leftsidebranch.php'; ?>
    <div class="container" style="margin-top:100px !important;">
        <div class="row">
            <div class="col-12">
                <div class="d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome!</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="branchmanager.php">Dashboard</a></li>
                            <li class="breadcrumb-item">Welcome!</li>
                            <li class="breadcrumb-item active">Document and Field Verifications</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="my-4">Documents and Field Verifications</h1>
        <!-- Search bar and filter dropdown -->
        <div class="row mb-3">
            <div class="col-md-8">
                <input type="text" id="search-input" class="form-control" placeholder="Search by Name, Lead ID, or Phone Number">
            </div>
            <div class="col-md-4">
                <select id="filter-select" class="form-control">
                    <option value="all">All</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        <div class="list-group" id="document-ids-list-group">
            <?php if (!empty($documentIDs)) : ?>
                <?php foreach ($documentIDs as $leadID) : ?>
                    <?php
                    $personName = isset($personsData[$leadID]['FullName']) ? $personsData[$leadID]['FullName'] : 'Unknown';
                    $loanPurpose = isset($personsData[$leadID]['LoanPurpose']) ? $personsData[$leadID]['LoanPurpose'] : 'Unknown';
                    $statusRemarks = isset($statusRemarksData[$leadID]) ? $statusRemarksData[$leadID] : [];
                    $fieldEvaluation = isset($fieldEvaluationData[$leadID]) ? $fieldEvaluationData[$leadID] : [];
                    $evaluationNeeded = isset($evaluationNeededData[$leadID]) ? $evaluationNeededData[$leadID] : 0;

                    // Determine chip statuses
                    $chips = [];
                    $chipStatus = 'completed';

                    // Legal Verification Chip
                    if (isset($statusRemarks['registree_status']) || isset($statusRemarks['fard_status']) || isset($statusRemarks['noc_status']) || isset($statusRemarks['video_status'])) {
                        if ($statusRemarks['registree_status'] == 'Rejected' || $statusRemarks['fard_status'] == 'Rejected' || $statusRemarks['noc_status'] == 'Rejected' || $statusRemarks['video_status'] == 'Rejected') {
                            $chips[] = ['label' => 'Legal Verification', 'class' => 'rejected'];
                            $chipStatus = 'rejected';
                        } else {
                            $chips[] = ['label' => 'Legal Verification', 'class' => 'completed'];
                        }
                    } else {
                        $chips[] = ['label' => 'Legal Verification', 'class' => 'pending'];
                        $chipStatus = ($chipStatus !== 'rejected') ? 'pending' : $chipStatus;
                    }

                    // Field Verification Chip
                    if (isset($fieldEvaluation['verificationStatus_Home']) || isset($fieldEvaluation['verificationStatus_Business'])) {
                        if ($fieldEvaluation['verificationStatus_Home'] == 'Rejected' || $fieldEvaluation['verificationStatus_Business'] == 'Rejected') {
                            $chips[] = ['label' => 'Field Verification', 'class' => 'rejected'];
                            $chipStatus = 'rejected';
                        } else {
                            $chips[] = ['label' => 'Field Verification', 'class' => 'completed'];
                        }
                    } else {
                        $chips[] = ['label' => 'Field Verification', 'class' => 'pending'];
                        $chipStatus = ($chipStatus !== 'rejected') ? 'pending' : $chipStatus;
                    }
                    ?>
                    <div class="list-group-item chip-status-<?= $chipStatus ?>">
                        <div class="lead-info"><strong>Lead ID:</strong> <?= $leadID ?></div>
                        <div class="lead-info"><strong>Person Name:</strong> <?= $personName ?></div>
                        <div class="lead-info"><strong>Loan Purpose:</strong> <?= $loanPurpose ?></div>
                        <div class="chips">
                            <?php foreach ($chips as $chip) : ?>
                                <span class="chip <?= $chip['class'] ?>"><?= $chip['label'] ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-2 btn-group-custom" style="display:flex;gap:5px;">
                            <a href="./verify_documents.php?id=<?= $leadID ?>" class="btn btn-primary">Document Verification</a>
                            <a href="./physicalv.php?id=<?= $leadID ?>" class="btn btn-primary">Field Verification</a>
                            <a href="./field_verification_coapp.php?id=<?= $leadID ?>" class="btn btn-primary">Field Verification (CO-APP.)</a>
                            <?php if ($loanPurpose == 'LAP') : ?>
                                <a href="./legal_evaluation.php?id=<?= $leadID ?>" class="btn btn-primary">Legal Evaluation</a>
                                <?php if ($evaluationNeeded == 1) : ?>
                                    <a href="./evaluations.php?id=<?= $leadID ?>" class="btn btn-primary">Evaluation Reports</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="alert alert-info">No records found.</div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // JavaScript for search functionality
        document.getElementById('search-input').addEventListener('input', function() {
            var searchQuery = this.value.toLowerCase();
            var items = document.querySelectorAll('#document-ids-list-group .list-group-item');
            items.forEach(function(item) {
                var text = item.textContent.toLowerCase();
                if (text.includes(searchQuery)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // JavaScript for filter functionality
        document.getElementById('filter-select').addEventListener('change', function() {
            var filterValue = this.value;
            var items = document.querySelectorAll('#document-ids-list-group .list-group-item');
            items.forEach(function(item) {
                if (filterValue === 'all') {
                    item.style.display = '';
                } else {
                    var chipStatus = item.classList.contains('chip-status-' + filterValue);
                    item.style.display = chipStatus ? '' : 'none';
                }
            });
        });
    </script>
</body>
</html>
