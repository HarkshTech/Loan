<?php
session_start();
// Include database configuration file
error_reporting(E_ALL);
ini_set('display_errors', 1);


if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'];


include 'config.php'; // Assuming this file contains database connection logic

// Fetch IDs with uploaded documents
$documentIDs = [];
if($role=='admin' || $role=='branchmanager'){ 
    $documentsQuery = $conn->query("SELECT DISTINCT LeadID FROM documentcollection");
}
elseif($role=='verifier'){ 
    $username = $_SESSION['username'];
    $documentsQuery = $conn->query("SELECT DISTINCT LeadID FROM documentcollection WHERE legalverifier='$username' OR fieldverifier='$username'");
}
if ($documentsQuery->num_rows > 0) {
    while ($row = $documentsQuery->fetch_assoc()) {
        $documentIDs[] = $row['LeadID'];
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
if (!empty($documentIDs)) {
    $ids = implode(',', $documentIDs);
    $statusQuery = $conn->query("SELECT lead_id, registree_status, registree_remarks, fard_status, fard_remarks, noc_status, noc_remarks, video_status, video_remarks, evaluation_needed FROM legal_evaluations WHERE lead_id IN ($ids)");
    if ($statusQuery->num_rows > 0) {
        while ($row = $statusQuery->fetch_assoc()) {
            $statusRemarksData[$row['lead_id']] = $row;
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

// Fetch all verifiers from users table
$verifiers = [];
$verifierQuery = $conn->query("SELECT username, role FROM users WHERE role='verifier'");
if ($verifierQuery->num_rows > 0) {
    while ($row = $verifierQuery->fetch_assoc()) {
        $verifiers[] = $row['username'];
    }
}

// Handle form submission to update verifiers
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leadID = $_POST['leadID'];
    $fieldVerifier = $_POST['fieldVerifier'];
    $legalVerifier = $_POST['legalVerifier'];

    $updateQuery = $conn->prepare("UPDATE documentcollection SET fieldverifier = ?, legalverifier = ? WHERE LeadID = ?");
    $updateQuery->bind_param('sss', $fieldVerifier, $legalVerifier, $leadID);
    if ($updateQuery->execute()) {
        $message = "Verifiers updated successfully.";
    } else {
        $message = "Error updating verifiers.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Verifications</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
            margin-top:100px;
        }

        .page-title-box {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ddd;
        }

        .page-title-box h4 {
            margin: 0;
            font-size: 1.5rem;
            color: #333;
        }

        .page-title-box ol.breadcrumb {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .page-title-box ol.breadcrumb li {
            display: inline;
            font-size: 1rem;
        }

        .page-title-box ol.breadcrumb li + li:before {
            content: " / ";
            padding: 0 5px;
        }

        .list-group-item {
            border: none;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .list-group-item:hover {
            background-color: #f1f1f1;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .chip {
            display: inline-block;
            padding: 6px 12px;
            font-size: 0.875rem;
            border-radius: 16px;
            margin: 4px;
            color: #fff;
            text-transform: uppercase;
        }

        .chip.completed {
            background-color: #28a745;
        }

        .chip.pending {
            background-color: #ffc107;
        }

        .chip.rejected {
            background-color: #dc3545;
        }

        .btn-custom {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 8px;
            color: #ffffff;
            background-color: #007bff;
            font-size: 0.875rem;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-custom:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .btn-custom:active {
            background-color: #004080;
            transform: scale(1.02);
        }

        .btn-group-custom {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-group label {
            font-weight: 500;
        }

        .alert-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #d1ecf1;
            color: #0c5460;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            .btn-group-custom {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <?php
    if ($role === 'admin') {
        include 'leftside.php'; // Assuming this includes the left-side 
        $redirecturl='dashboard.php';
    } elseif ($role === 'verifier') {
        include 'leftsideverifier.php';
        $redirecturl='dashboardverifier.php';
    }
    elseif ($role === 'branchmanager') {
        include 'leftsidebranch.php';
        $redirecturl='branchmanager.php';
    }
    ?>

    <div class="container">
        <div class="page-title-box d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Document Verification</h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo $redirecturl;?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Document Verification</li>
            </ol>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <input type="text" id="search-input" class="form-control" placeholder="Search by Name, Lead ID, or Phone Number">
            </div>
            <div class="col-md-6 mb-3">
                <select id="filter-select" class="form-select">
                    <option value="all">All</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="list-group" id="document-ids-list-group">
                    <?php if (!empty($documentIDs)) : ?>
                        <?php foreach ($documentIDs as $leadID) : ?>
                            <?php
                            $personName = isset($personsData[$leadID]['FullName']) ? $personsData[$leadID]['FullName'] : 'Unknown';
                            $loanPurpose = isset($personsData[$leadID]['LoanPurpose']) ? $personsData[$leadID]['LoanPurpose'] : 'Unknown';
                            $statusRemarks = isset($statusRemarksData[$leadID]) ? $statusRemarksData[$leadID] : [];
                            $fieldEvaluation = isset($fieldEvaluationData[$leadID]) ? $fieldEvaluationData[$leadID] : [];
                            $evaluationNeeded = isset($statusRemarks['evaluation_needed']) ? $statusRemarks['evaluation_needed'] : 0;

                            $chips = [];
                            $chipStatus = '';

                            // Legal Verification Chip
                            if ($loanPurpose === 'LAP') {
                                if (isset($statusRemarks['registree_status'], $statusRemarks['fard_status'], $statusRemarks['noc_status'], $statusRemarks['video_status'], $statusRemarks['old_registree_status']) &&
                                    $statusRemarks['registree_status'] === 'Rejected' &&
                                    $statusRemarks['fard_status'] === 'Rejected' &&
                                    $statusRemarks['noc_status'] === 'Rejected' &&
                                    $statusRemarks['video_status'] === 'Rejected' &&
                                    $statusRemarks['old_registree_status'] === 'Rejected'
                                ) {
                                    $chips[] = ['label' => 'Legal Verification', 'class' => 'rejected'];
                                    $chipStatus = 'rejected';
                                } else {
                                    $chips[] = ['label' => 'Legal Verification', 'class' => 'completed'];
                                    $chipStatus = 'completed';
                                }
                            }

                            // Field Verification Chip
                            if (isset($fieldEvaluation['verificationStatus_Home']) || isset($fieldEvaluation['verificationStatus_Business'])) {
                                if ($fieldEvaluation['verificationStatus_Home'] === 'Rejected' || $fieldEvaluation['verificationStatus_Business'] === 'Rejected') {
                                    $chips[] = ['label' => 'Field Verification', 'class' => 'rejected'];
                                    $chipStatus = 'rejected';
                                } else {
                                    $chips[] = ['label' => 'Field Verification', 'class' => 'completed'];
                                    $chipStatus = 'completed';
                                }
                            } else {
                                $chips[] = ['label' => 'Field Verification', 'class' => 'pending'];
                                $chipStatus = ($chipStatus !== 'rejected') ? 'pending' : $chipStatus;
                            }

                            // Evaluation Reports Chip
                            if ($loanPurpose === 'LAP' && $evaluationNeeded == 1) {
                                if (isset($statusRemarks['evaluation_needed']) && $statusRemarks['evaluation_needed'] == 1) {
                                    $chips[] = ['label' => 'Evaluation Report', 'class' => 'completed'];
                                    $chipStatus = 'completed';
                                } else {
                                    $chips[] = ['label' => 'Evaluation Report', 'class' => 'pending'];
                                    $chipStatus = ($chipStatus !== 'rejected') ? 'pending' : $chipStatus;
                                }
                            }
                            ?>

                            <div class="list-group-item" data-lead-id="<?php echo htmlspecialchars($leadID); ?>" data-person-name="<?php echo htmlspecialchars(strtolower($personName)); ?>" data-status="<?php echo htmlspecialchars($chipStatus); ?>">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($personName); ?> (ID: <?php echo htmlspecialchars($leadID); ?>)</h5>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($loanPurpose); ?></span>
                                </div>
                                <div class="mb-2">
                                    <?php foreach ($chips as $chip) : ?>
                                        <span class="chip <?php echo htmlspecialchars($chip['class']); ?>"><?php echo htmlspecialchars($chip['label']); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="btn-group-custom">
                                            <?php if ($role === 'admin' || $role==='branchmanager') : ?>
                                                <a href="./verify_documents.php?id=<?php echo htmlspecialchars($leadID); ?>" class="btn-custom">Document Verification</a>
                                            <?php endif; ?>
                                            <a href="./physicalv.php?id=<?php echo htmlspecialchars($leadID); ?>" class="btn-custom">Field Verification</a>
                                            <a href="./field_verification_coapp.php?id=<?php echo htmlspecialchars($leadID); ?>" class="btn-custom">Field Verification (CO-APP.)</a>
                                            <?php if ($loanPurpose === 'LAP') : ?>
                                                <a href="./legal_evaluation.php?id=<?php echo htmlspecialchars($leadID); ?>" class="btn-custom">Legal Evaluation</a>
                                                <?php if ($evaluationNeeded == 1) : ?>
                                                    <a href="./evaluations.php?id=<?php echo htmlspecialchars($leadID); ?>" class="btn-custom">Evaluation Reports</a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php 
                                        if($role=='admin' || $role=='branchmanager'){
                                    ?>
                                    <div class="col-md-6">
                                        <form method="POST" action="">
                                            <input type="hidden" name="leadID" value="<?php echo htmlspecialchars($leadID); ?>">
                                            <div class="mb-3">
                                                <label for="field-verifier-<?php echo htmlspecialchars($leadID); ?>" class="form-label">Field Verifier:</label>
                                                <select id="field-verifier-<?php echo htmlspecialchars($leadID); ?>" name="fieldVerifier" class="form-select">
                                                    <option value="">Select Field Verifier</option>
                                                    <?php foreach ($verifiers as $verifier) : ?>
                                                        <option value="<?php echo htmlspecialchars($verifier); ?>"><?php echo htmlspecialchars($verifier); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="legal-verifier-<?php echo htmlspecialchars($leadID); ?>" class="form-label">Legal Verifier:</label>
                                                <select id="legal-verifier-<?php echo htmlspecialchars($leadID); ?>" name="legalVerifier" class="form-select">
                                                    <option value="">Select Legal Verifier</option>
                                                    <?php foreach ($verifiers as $verifier) : ?>
                                                        <option value="<?php echo htmlspecialchars($verifier); ?>"><?php echo htmlspecialchars($verifier); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn-custom">Update Verifiers</button>
                                            <?php if (isset($message)) : ?>
                                                <div class="alert-info mt-2"><?php echo htmlspecialchars($message); ?></div>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                    <?php 
                                        }
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="alert-info" role="alert">No documents uploaded for verification.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
    // Function to reorder the items by status
    function reorderByStatus() {
        var pendingItems = [];
        var otherItems = [];

        $('#document-ids-list-group .list-group-item').each(function () {
            var status = $(this).data('status');
            if (status === 'pending') {
                pendingItems.push($(this));
            } else {
                otherItems.push($(this));
            }
        });

        // Clear the current list
        $('#document-ids-list-group').empty();

        // Append pending items first, then the rest
        pendingItems.forEach(function (item) {
            $('#document-ids-list-group').append(item);
        });

        otherItems.forEach(function (item) {
            $('#document-ids-list-group').append(item);
        });
    }

    // Reorder items by status on page load (pending first)
    reorderByStatus();

    // Search functionality
    $('#search-input').on('input', function () {
        var query = $(this).val().toLowerCase();
        $('#document-ids-list-group .list-group-item').each(function () {
            var leadId = $(this).data('lead-id').toString().toLowerCase();
            var personName = $(this).data('person-name').toLowerCase();
            if (leadId.includes(query) || personName.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Filter functionality
    $('#filter-select').on('change', function () {
        var filter = $(this).val();
        $('#document-ids-list-group .list-group-item').each(function () {
            var status = $(this).data('status');
            if (filter === 'all' || status === filter) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});

    </script>
</body>

</html>
