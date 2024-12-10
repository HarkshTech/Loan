<?php
session_start();
include 'config.php';

$q = $_GET['q'] ?? '';

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo '<div class="alert alert-warning mt-4" role="alert">Please log in to search.</div>';
    exit();
}

$loggedInUser = $_SESSION['username'];

$salespersons = [];
$salespersonsQuery = $conn->query("SELECT username FROM users WHERE branchmanager = '$loggedInUser' AND role='sales'");
if ($salespersonsQuery->num_rows > 0) {
    while ($row = $salespersonsQuery->fetch_assoc()) {
        $salespersons[] = $row['username'];
    }
}

$salespersonsList = "'" . implode("','", $salespersons) . "'";
$selfGeneratedBy = implode(", ", array_map(function($value) {
    return "'Self($value)'";
}, $salespersons));

$leadIDs = [];
$personalInfoQuery = $conn->query("SELECT ID FROM personalinformation WHERE (generatedby = 'Self($loggedInUser)' OR generatedby IN ($selfGeneratedBy) OR assignedto IN ($salespersonsList,'$loggedInUser')) AND (ID LIKE '%$q%' OR FullName LIKE '%$q%' OR LoanPurpose LIKE '%$q%')");
if ($personalInfoQuery->num_rows > 0) {
    while ($row = $personalInfoQuery->fetch_assoc()) {
        $leadIDs[] = $row['ID'];
    }
}

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

$statusRemarksData = [];
if (!empty($documentIDs)) {
    $ids = implode(',', $documentIDs);
    $statusQuery = $conn->query("SELECT lead_id, registree_status, registree_remarks, fard_status, fard_remarks, noc_status, noc_remarks, video_status, video_remarks FROM legal_evaluations WHERE lead_id IN ($ids)");
    if ($statusQuery->num_rows > 0) {
        while ($row = $statusQuery->fetch_assoc()) {
            $statusRemarksData[$row['lead_id']] = $row;
        }
    }
}

$fieldEvaluationData = [];
if (!empty($documentIDs)) {
    $ids = implode(',', $documentIDs);
    $fieldQuery = $conn->query("SELECT leadID, verificationStatus_Home, verificationNotes_Home,verificationStatus_Business,businessVerificationNotes FROM VerificationForms WHERE leadID IN ($ids)");
    if ($fieldQuery->num_rows > 0) {
        while ($row = $fieldQuery->fetch_assoc()) {
            $fieldEvaluationData[$row['leadID']] = $row;
        }
    }
}

if (!empty($documentIDs)) {
    foreach ($documentIDs as $leadID) {
        $personName = isset($personsData[$leadID]['FullName']) ? $personsData[$leadID]['FullName'] : 'Unknown';
        $loanPurpose = isset($personsData[$leadID]['LoanPurpose']) ? $personsData[$leadID]['LoanPurpose'] : 'Unknown';
        $statusRemarks = isset($statusRemarksData[$leadID]) ? $statusRemarksData[$leadID] : [];
        $fieldEvaluation = isset($fieldEvaluationData[$leadID]) ? $fieldEvaluationData[$leadID] : [];

        echo '<div class="list-group-item">';
        echo '<div class="lead-info"><strong>Lead ID:</strong> ' . $leadID . '</div>';
        echo '<div class="lead-info"><strong>Person Name:</strong> ' . $personName . '</div>';
        echo '<div class="lead-info"><strong>Loan Purpose:</strong> ' . $loanPurpose . '</div>';

        if (!empty($statusRemarks)) {
            echo '<div class="mt-2"><strong>Legal Evaluation:</strong><ul>';
            if ($statusRemarks['registree_status'] == 'Rejected') {
                echo '<li><strong>Registree Status:</strong> ' . $statusRemarks['registree_status'] . '</li>';
                echo '<li><strong>Registree Remarks:</strong> ' . $statusRemarks['registree_remarks'] . '</li>';
            }
            if ($statusRemarks['fard_status'] == 'Rejected') {
                echo '<li><strong>Fard Status:</strong> ' . $statusRemarks['fard_status'] . '</li>';
                echo '<li><strong>Fard Remarks:</strong> ' . $statusRemarks['fard_remarks'] . '</li>';
            }
            if ($statusRemarks['noc_status'] == 'Rejected') {
                echo '<li><strong>NOC Status:</strong> ' . $statusRemarks['noc_status'] . '</li>';
                echo '<li><strong>NOC Remarks:</strong> ' . $statusRemarks['noc_remarks'] . '</li>';
            }
            if ($statusRemarks['video_status'] == 'Rejected') {
                echo '<li><strong>Video Status:</strong> ' . $statusRemarks['video_status'] . '</li>';
                echo '<li><strong>Video Remarks:</strong> ' . $statusRemarks['video_remarks'] . '</li>';
            }
            echo '</ul></div>';
        }

        if (!empty($fieldEvaluation)) {
            echo '<div class="mt-2"><strong>Field Evaluation:</strong><ul>';
            if ($fieldEvaluation['verificationStatus_Home'] == 'Rejected') {
                echo '<li><strong>Home Status:</strong> ' . $fieldEvaluation['verificationStatus_Home'] . '</li>';
                echo '<li><strong>Remarks:</strong> ' . $fieldEvaluation['verificationNotes_Home'] . '</li>';
            }
            if ($fieldEvaluation['verificationStatus_Business'] == 'Rejected') {
                echo '<li><strong>Business Status:</strong> ' . $fieldEvaluation['verificationStatus_Business'] . '</li>';
                echo '<li><strong>Remarks:</strong> ' . $fieldEvaluation['businessVerificationNotes'] . '</li>';
            }
            echo '</ul></div>';
        }

        echo '<div class="mt-2">';
        echo '<a href="./verify_documents.php?id=' . $leadID . '" class="btn btn-primary mr-2">Document Verification</a>';
        echo '<a href="./physicalv.php?id=' . $leadID . '" class="btn btn-primary mr-2">Field Verification</a>';
        if ($loanPurpose == 'LAP') {
            echo '<a href="./legal_evaluation.php?id=' . $leadID . '" class="btn btn-primary">Legal Evaluation</a>';
            echo '<a href="./evaluations.php?id=' .$leadID. '" class="btn btn-primary">Evaluation Reports</a>';
        }
        echo '</div></div>';
    }
} else {
    echo '<div class="alert alert-info" role="alert">No documents uploaded for verification.</div>';
}

$conn->close();
?>
