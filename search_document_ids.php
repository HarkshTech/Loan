<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php'; // Assuming this file contains database connection logic

$query = isset($_POST['query']) ? $_POST['query'] : '';
$documentIDs = [];
if (!empty($query)) {
    $query = $conn->real_escape_string($query);

    // Fetch IDs with uploaded documents based on the search query
    $documentsQuery = $conn->query("SELECT DISTINCT LeadID FROM documentcollection WHERE LeadID LIKE '%$query%'");
    if ($documentsQuery->num_rows > 0) {
        while ($row = $documentsQuery->fetch_assoc()) {
            $documentIDs[] = $row['LeadID'];
        }
    }

    // Fetch person's name and LoanPurpose for each ID
    $personsData = [];
    if (!empty($documentIDs)) {
        $ids = implode(',', $documentIDs);
        $personQuery = $conn->query("SELECT ID, FullName, LoanPurpose FROM personalinformation WHERE ID IN ($ids) AND (FullName LIKE '%$query%' OR PhoneNumber LIKE '%$query%')");
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
        $statusQuery = $conn->query("SELECT lead_id, registree_status, registree_remarks, fard_status, fard_remarks, noc_status, noc_remarks, video_status, video_remarks FROM legal_evaluations WHERE lead_id IN ($ids)");
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

    // Generate the HTML for the filtered results
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
                echo '<a href="./evaluations.php?id=' . $leadID . '" class="btn btn-primary">Evaluation Reports</a>';
            }
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="alert alert-info" role="alert">No documents uploaded for verification.</div>';
    }
} else {
    echo '<div class="alert alert-info" role="alert">Please enter a search query.</div>';
}

$conn->close();
?>
