<?php
session_start();
$username = $_SESSION['username'];
$role = $_SESSION['role'];

include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Personal Information</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .vertical-menu.mm-active {
            left: 0px;
        }
        .main-content {
            margin: 20px; /* General margin around main content */
        }

        .table-container {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
            margin-top: 100px !important;
            width: 100%;
            margin: 0 auto;
        }

        .table th {
            background-color: #3f51b5;
            color: white;
            text-transform: uppercase;
            font-weight: 500;
        }

        .action-buttons a {
            margin: 3px;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .view-button {
            background-color: #4CAF50;
            color: white;
        }

        .edit-button {
            background-color: #5adbb5!important;
            color: white;
        }

        .field-button {
            background-color: #a881af !important;
            color: white;
        }

        .legal-button {
            background-color: #dd7973 !important;
            color: white;
        }

        .evaluations-button {
            background-color: #c96161 !important;
            color: white;
        }
        #updateButton{
            background-color:#6d8cf9!important;
        }
        #moreactionbutton{
            background-color:#6d8cf9!important;
        }

        .action-buttons a:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<div class="main-content">
    <?php
    if ($role == 'branchmanager') {
        include 'leftsidebranch.php';
    } else {
        include 'leftside.php';
    }

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

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

        // Fetch records based on the specified criteria
        $sql = "
            SELECT pi.ID, pi.sno, pi.FullName, pi.LoanPurpose, le.evaluation_needed, du.DocumentPath 
            FROM personalinformation pi
            LEFT JOIN legal_evaluations le ON pi.ID = le.lead_id
            LEFT JOIN documentuploads du ON pi.ID = du.LoanApplicationID
            WHERE pi.generatedby IN ('Self($username)', $salespersonList) 
            OR pi.assignedto IN ('Self($username)', $salespersonList, '$username')
        ";
    } else {
        $sql = "
            SELECT pi.ID, pi.sno, pi.FullName, pi.LoanPurpose, le.evaluation_needed, du.DocumentPath 
            FROM personalinformation pi
            LEFT JOIN legal_evaluations le ON pi.ID = le.lead_id
            LEFT JOIN documentuploads du ON pi.ID = du.LoanApplicationID
        ";
    }
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<div class="table-container">';
        echo '<h1 class="mb-4">Applicant Details</h1>';
        echo '<div class="search-container">
            <input type="text" id="search" class="form-control" placeholder="Search...">
        </div>';
        echo '<table class="table table-striped table-bordered" id="resultsContainer">';
        echo '<thead><tr><th>ID</th><th>Sno</th><th>FullName</th><th>Passport Image</th><th>Action</th></tr></thead><tbody>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row["ID"] . '</td>';
            echo '<td>' . $row["sno"] . '</td>';
            echo '<td>' . $row["FullName"] . '</td>';
            
            // Display the document link
            if (!empty($row["DocumentPath"])) {
                echo '<td><a href="' . $row["DocumentPath"] . '" target="_blank">View Passport Image</a></td>';
            } else {
                echo '<td>No Document</td>';
            }
            
            echo '<td class="action-buttons">';
            // Add the new button for triggering the modal
            echo '<a class="btn btn-warning" id="moreactionbutton" href="#" onclick="showActionModal(event, ' . $row["ID"] . ', \'' . $row["LoanPurpose"] . '\', ' . $row["evaluation_needed"] . ')">More Actions</a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-warning mt-4" role="alert" style="margin-top:80px !important;">No results found</div>';
    }

    $conn->close();
    ?>
</div>

<!-- Modal Structure -->
<div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalLabel">More Actions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Add your buttons here -->
                <a class="btn btn-block btn-success view-button" href="#" id="viewButton">View Applicant</a>
                <a class="btn btn-block btn-primary edit-button" href="#" id="editButton">Documents Uploaded</a>
                <a class="btn btn-block btn-success field-button" href="#" id="fieldButton">Field Verifications</a>
                <a class="btn btn-block btn-info legal-button" href="#" id="legalButton">Legal Verification</a>
                <a class="btn btn-block btn-info evaluations-button" href="#" id="evaluationsButton">Evaluation Report</a>
                <a class="btn btn-block btn-info update-button" href="#" id="updateButton">Update Applicant Profile</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    function showActionModal(event, applicantId, loanPurpose, evaluationNeeded) {
        event.preventDefault();
        
        // Update the href attributes of the buttons in the modal
        document.getElementById('viewButton').href = 'view.php?id=' + applicantId;
        document.getElementById('editButton').href = 'edit.php?id=' + applicantId;
        document.getElementById('fieldButton').href = 'approve_field.php?id=' + applicantId;
        document.getElementById('legalButton').href = 'approve_legal.php?id=' + applicantId;
        document.getElementById('evaluationsButton').href = 'evaluations_approval.php?id=' + applicantId;
        document.getElementById('updateButton').href = 'update_form.php?id=' + applicantId;

        // Show or hide buttons based on loanPurpose and evaluationNeeded
        if (loanPurpose === 'LAP') {
            document.getElementById('legalButton').style.display = 'block';
            if (evaluationNeeded == 1) {
                document.getElementById('evaluationsButton').style.display = 'block';
            } else {
                document.getElementById('evaluationsButton').style.display = 'none';
            }
        } else {
            document.getElementById('legalButton').style.display = 'none';
            document.getElementById('evaluationsButton').style.display = 'none';
        }

        // Show the modal
        $('#actionModal').modal('show');
    }

    // Function to fetch results based on query
    function fetchResults(query) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'search.php?q=' + encodeURIComponent(query), true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById('resultsContainer').innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }

    // Check if there's a search query in the URL on page load
    document.addEventListener('DOMContentLoaded', function() {
        var urlParams = new URLSearchParams(window.location.search);
        var queryParam = urlParams.get('q');
        if (queryParam) {
            document.getElementById('search').value = queryParam; // Set input field value
            fetchResults(queryParam); // Fetch results automatically
        }
    });

    // Event listener for input change in search field
    document.getElementById('search').addEventListener('input', function() {
        var query = this.value.trim();
        if (query.length > 0) {
            fetchResults(query);
        } else {
            document.getElementById('resultsContainer').innerHTML = '';
        }
    });
</script>

</body>
</html>
