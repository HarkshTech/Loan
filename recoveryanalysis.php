<?php
session_start();
$username = $_SESSION['username'];
$role = $_SESSION['role'];


if ($role === 'admin') {
    include 'leftside.php';
} elseif ($role === 'accounts') {
    include 'leftbaraccounts.php';
} elseif ($role === 'branchmanager') {
    include 'leftsidebranch.php';
}

switch ($role) {
    case 'admin':
        $redirecturl = 'dashboard.php';
        break;
    case 'branchmanager':
        $redirecturl = 'branchmanager.php';
        break;
    case 'accounts':
        $redirecturl = 'dashboardapproved_loans.php';
        break;
}

include 'config.php';

// Fetch distinct CaseStatus values
$sqlCaseStatus = "SELECT DISTINCT CaseStatus FROM recovery_data";
$caseStatusResults = $conn->query($sqlCaseStatus);

// Fetch distinct AssignedTo values
$sqlAssignedTo = "SELECT DISTINCT AssignedTo FROM recovery_data";
$assignedToResults = $conn->query($sqlAssignedTo);


// Initialize the query
$searchQuery = isset($_GET['search_query']) ? $_GET['search_query'] : '';

$sqlFetchFailedEMI = "SELECT * FROM recovery_data WHERE 1=1"; // Always true condition

// Apply filters if set
if (!empty($_GET['case_status'])) {
    $caseStatus = $conn->real_escape_string($_GET['case_status']);
    $sqlFetchFailedEMI .= " AND CaseStatus = '$caseStatus'";
}

if (!empty($_GET['assigned_to'])) {
    $assignedTo = $conn->real_escape_string($_GET['assigned_to']);
    $sqlFetchFailedEMI .= " AND AssignedTo = '$assignedTo'";
}

if (!empty($_GET['date_from'])) {
    $dateFrom = $conn->real_escape_string($_GET['date_from']);
    $sqlFetchFailedEMI .= " AND AssignedDate >= '$dateFrom'";
}

if (!empty($_GET['date_to'])) {
    $dateTo = $conn->real_escape_string($_GET['date_to']);
    $sqlFetchFailedEMI .= " AND AssignedDate <= '$dateTo'";
}

// Apply search filter if a query is provided
if (!empty($searchQuery)) {
    $sqlFetchFailedEMI .= " AND (LeadID LIKE '%$searchQuery%' 
                                OR AssignedTo LIKE '%$searchQuery%' 
                                OR CaseStatus LIKE '%$searchQuery%')";
}

$result = $conn->query($sqlFetchFailedEMI);

// Fetch recovery officers from users table
$sqlFetchRecoveryOfficers = "SELECT username FROM users WHERE role = 'recovery'";
$recoveryOfficers = $conn->query($sqlFetchRecoveryOfficers);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recoveries</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 60px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table th {
            background-color: #343a40;
            color: white;
        }

        .breadcrumb {
            background-color: #f8f9fa;
        }

        .breadcrumb a {
            color: #007bff;
        }

        .action-column {
            min-width: 200px;
        }

        .btn-assign {
            background-color: #007bff;
            color: white;
        }

        .page-title-box {
            padding: 15px;
            border-radius: 5px;
        }

        .page-title-box h4 {
            margin: 0;
        }

        .page-title-right {
            float: right;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 font-size-18">Welcome!</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo $redirecturl ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Recoveries</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="mt-4 mb-4 text-center">Recovery EMI Data</h1>
        <div class="row mb-4">
            <div class="col-md-12">
                <form method="GET" action="" class="form-inline justify-content-center">
                    <!-- Case Status Filter -->
                    <label for="case_status" class="mr-2">Case Status:</label>
                    <select id="case_status" name="case_status" class="form-control mr-3">
                        <option value="">All</option>
                        <?php
                        while ($row = $caseStatusResults->fetch_assoc()) {
                            $selected = isset($_GET['case_status']) && $_GET['case_status'] === $row['CaseStatus'] ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['CaseStatus']) . "' $selected>" . htmlspecialchars($row['CaseStatus']) . "</option>";
                        }
                        ?>
                    </select>

                    <!-- Assigned To Filter -->
                    <label for="assigned_to" class="mr-2">Assigned To:</label>
                    <select id="assigned_to" name="assigned_to" class="form-control mr-3">
                        <option value="">All</option>
                        <?php
                        while ($row = $assignedToResults->fetch_assoc()) {
                            $selected = isset($_GET['assigned_to']) && $_GET['assigned_to'] === $row['AssignedTo'] ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['AssignedTo']) . "' $selected>" . htmlspecialchars($row['AssignedTo']) . "</option>";
                        }
                        ?>
                    </select>

                    <!-- Assigned Date Filters -->
                    <label for="date_from" class="mr-2">Date From:</label>
                    <input type="date" id="date_from" name="date_from" class="form-control mr-3" value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">

                    <label for="date_to" class="mr-2">Date To:</label>
                    <input type="date" id="date_to" name="date_to" class="form-control mr-3" value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">

                    <!-- Search Button -->
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>


        <div class="table-responsive">
        <table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>Lead ID</th>
            <th>Assigned Date</th>
            <th>Assigned Person</th>
            <th>Overdue Days</th>
            <th>Visit Needed</th>
            <th>Next Steps</th>
            <th>Any Remarks</th>
            <th>Case Status</th>
            <th>Geolocation</th>
            <th>Images/Videos (Recovery Visit)</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['LeadID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['AssignedDate']) . "</td>";
                echo "<td>" . htmlspecialchars($row['AssignedTo']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Overdue_days']) . "</td>";
                echo "<td>" . htmlspecialchars($row['VisitNeeded']) . "</td>";
                echo "<td>" . htmlspecialchars($row['NextSteps']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Remarks']) . "</td>";
                echo "<td>" . htmlspecialchars($row['CaseStatus']) . "</td>";

                // Geolocation - Embed Google Maps iframe without API key
                if (!empty($row['visitgeolocation'])) {
                    $geoCoordinates = htmlspecialchars($row['visitgeolocation']);
                    echo "<td>
                        <iframe 
                            width='200' 
                            height='150' 
                            frameborder='0' 
                            style='border:0;' 
                            src='https://maps.google.com/maps?q={$geoCoordinates}&output=embed'>
                        </iframe>
                    </td>";
                } else {
                    echo "<td>No location available</td>";
                }

                // Visit Files - Label files as Image 1, Video 1, etc.
                if (!empty($row['visit_files'])) {
                    $files = explode(',', $row['visit_files']);
                    $fileLinks = '';
                    $imageCounter = 1;
                    $videoCounter = 1;

                    foreach ($files as $file) {
                        $filePath = htmlspecialchars($file);
                        $fileName = basename($filePath);
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                        if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'])) {
                            $fileLinks .= "<a href='{$filePath}' target='_blank'>Image {$imageCounter}</a><br>";
                            $imageCounter++;
                        } elseif (in_array($fileExtension, ['mp4', 'mov', 'avi', 'mkv', 'webm'])) {
                            $fileLinks .= "<a href='{$filePath}' target='_blank'>Video {$videoCounter}</a><br>";
                            $videoCounter++;
                        } else {
                            $fileLinks .= "<a href='{$filePath}' target='_blank'>File</a><br>";
                        }
                    }

                    echo "<td>{$fileLinks}</td>";
                } else {
                    echo "<td>No files uploaded</td>";
                }

                echo "<td>
                        <button class='btn btn-danger close-assignment' data-lead-id='" . htmlspecialchars($row['LeadID']) . "' data-toggle='modal' data-target='#closeAssignmentModal'>Close Assignment</button>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='11' class='text-center'>No records found</td></tr>";
        }
        ?>
    </tbody>
</table>

        </div>
    </div>

    <!-- Close Assignment Modal -->
    <div class="modal fade" id="closeAssignmentModal" tabindex="-1" role="dialog" aria-labelledby="closeAssignmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="closeAssignmentModalLabel">Close Assignment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="closeAssignmentForm" method="POST" action="">
                        <div class="form-group">
                            <label for="remarks">Enter Remarks:</label>
                            <textarea class="form-control" id="remarks" name="remarks" required></textarea>
                        </div>
                        <input type="hidden" id="leadId" name="leadId">
                        <button type="submit" class="btn btn-primary">Close Assignment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> <!-- Updated to full jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.close-assignment').on('click', function () {
                var leadId = $(this).data('lead-id');
                $('#leadId').val(leadId);
            });

            $('#closeAssignmentForm').on('submit', function (e) {
                e.preventDefault(); // Prevent the form from submitting the default way

                var formData = $(this).serialize(); // Serialize form data

                $.ajax({
                    type: 'POST',
                    url: '', // Sending to the same file
                    data: formData,
                    success: function (response) {
                        location.reload(); // Reload the page to see updated data
                    },
                    error: function (xhr, status, error) {
                        alert('Error: ' + error);
                    }
                });
            });
        });
    </script>
</body>

</html>

<?php
// Handle Close Assignment Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['leadId'])) {
    $leadId = intval($_POST['leadId']);
    $remarks = $_POST['remarks'];
    $username = $_SESSION['username']; // Get the current user's username

    // Get today's date in the desired format
    $todayDate = date('Y-m-d');

    // Format new remarks
    $formattedRemarks = "$todayDate: $remarks";

    // Fetch existing remarks
    $sqlFetchRemarks = "SELECT Remarks FROM recovery_data WHERE LeadID = ?";
    $stmtFetch = $conn->prepare($sqlFetchRemarks);
    $stmtFetch->bind_param("i", $leadId);
    $stmtFetch->execute();
    $result = $stmtFetch->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $existingRemarks = $row['Remarks'];

        // Combine existing remarks with the new formatted remarks
        $newRemarks = $existingRemarks ? $existingRemarks . "\n" . $formattedRemarks : $formattedRemarks;
    } else {
        $newRemarks = $formattedRemarks;
    }

    // Update the recovery_data table
    $sqlUpdate = "UPDATE recovery_data SET CaseStatus = ?, Remarks = ? WHERE LeadID = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $caseStatus = "Case Closed by $username";
    $stmtUpdate->bind_param("ssi", $caseStatus, $newRemarks, $leadId);

    if ($stmtUpdate->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmtUpdate->error]);
    }

    $stmtUpdate->close();
    $stmtFetch->close();
}
$conn->close();
?>