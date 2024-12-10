<?php
// Include database configuration file
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    // Redirect to the login page or another appropriate page
    header("Location: index.php");
    exit();
}
$role = $_SESSION['role'];
if ($role == 'admin') {
    $redirect = 'dashboard.php';
}
if ($role == 'branchmanager') {
    $redirect = 'branchmanager.php';
}

include 'config.php'; // Assuming this file contains database connection logic

// Fetch evaluation reports
$reportsQuery = $conn->query("SELECT er.report_id, er.lead_id, er.evaluator_name, er.report_file, er.status, er.remarks, pi.FullName 
                              FROM evaluation_reports er
                              JOIN personalinformation pi ON er.lead_id = pi.ID
                              ORDER BY er.created_at DESC");

$evaluationReports = [];
if ($reportsQuery->num_rows > 0) {
    while ($row = $reportsQuery->fetch_assoc()) {
        $evaluationReports[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['report_id'])) {
    $report_id = $_POST['report_id'];
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : '';

    // Update Applicant status and remarks
    if ($status) {
        $updateQuery = $conn->prepare("UPDATE evaluation_reports SET status = ?, remarks = ? WHERE report_id = ?");
        $updateQuery->bind_param('ssi', $status, $remarks, $report_id);
        $updateQuery->execute();
        
        $fetchID = "SELECT lead_id FROM evaluation_reports WHERE report_id = $report_id";
        $resultfid = $conn->query($fetchID);

        if ($resultfid->num_rows > 0) {
            while ($rowfid = $resultfid->fetch_assoc()) {
                $lid = $rowfid['lead_id'];
                $update = "UPDATE personalinformation SET StepReached='Evaluation Report Status Updated by ADMIN' WHERE ID=$lid";
                $conn->query($update);
            }
        }
    }

    // Refresh the page to reflect changes
    header("Location: evaluations_approval.php");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Evaluation Reports</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 900px;
            margin-top: 8%;
        }

        .page-title-box {
            margin-bottom: 20px;
        }

        .list-group-item {
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }

        .lead-info {
            margin-bottom: 10px;
        }

        .alert-danger {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    
    <?php 
    if ($role == 'admin') {
        include 'leftside.php'; // Assuming this includes the left-side navigation 
    } elseif ($role == 'branchmanager') {
        include 'leftsidebranch.php'; // Assuming this includes the left-side navigation
    }
    ?>
    <div class="container">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Approve Evaluation Reports</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo $redirect; ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Approve Evaluation Reports</li>
                </ol>
            </div>
        </div>

        <h1 class="my-4">Evaluation Reports</h1>
        
        <div class="form-group">
            <input type="text" id="search" class="form-control" value="<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>" placeholder="Search by evaluator name or person name">
        </div>

        <div class="list-group" id="evaluation-reports-list">
            <?php if (!empty($evaluationReports)) : ?>
                <?php foreach ($evaluationReports as $report) : ?>
                    <div class="list-group-item">
                        <!-- General Details -->
                        <div class="lead-info">
                            <strong>ID:</strong> <?php echo $report['report_id']; ?>
                        </div>
                        <div class="lead-info">
                            <strong>Lead ID:</strong> <?php echo $report['lead_id']; ?>
                        </div>
                        <div class="lead-info">
                            <strong>Person Name:</strong> <?php echo $report['FullName']; ?>
                        </div>
                        
                        <div class="row">
                            <!-- Applicant Details -->
                            <div class="col-md-12">
                                <div class="lead-info">
                                    <strong>Evaluator Name:</strong> <?php echo $report['evaluator_name']; ?>
                                </div>
                                <div class="lead-info">
                                    <strong>Evaluation Report:</strong> <a href="<?php echo $report['report_file']; ?>" target="_blank">View Report</a>
                                </div>
                                <div class="lead-info">
                                    <strong>Status:</strong> <?php echo $report['status']; ?>
                                </div>
                                <?php if ($report['status'] == 'Rejected') : ?>
                                    <div class="lead-info">
                                        <strong>Remarks:</strong> <?php echo $report['remarks']; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Update Status Form for Applicant -->
                                <form method="post" class="mt-2">
                                    <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                    <div class="form-group">
                                        <label for="status_<?php echo $report['report_id']; ?>">Status</label>
                                        <select class="form-control" id="status_<?php echo $report['report_id']; ?>" name="status" onchange="toggleRemarks(this, <?php echo $report['report_id']; ?>)">
                                            <option value="Pending" <?php echo $report['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Approved" <?php echo $report['status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                            <option value="Rejected" <?php echo $report['status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="remarks_group_<?php echo $report['report_id']; ?>" style="display: <?php echo $report['status'] == 'Rejected' ? 'block' : 'none'; ?>;">
                                        <label for="remarks_<?php echo $report['report_id']; ?>">Remarks</label>
                                        <textarea class="form-control" id="remarks_<?php echo $report['report_id']; ?>" name="remarks"><?php echo $report['remarks']; ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Update Status</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="alert alert-info" role="alert">No evaluation reports available for approval.</div>
            <?php endif; ?>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            // Function to fetch evaluation reports
            function fetchReports(query = '') {
                $.ajax({
                    url: 'fetch_reports.php',
                    method: 'GET',
                    data: { query: query },
                    success: function (data) {
                        $('#evaluation-reports-list').html(data);
                    }
                });
            }

            // Initially fetch all reports
            fetchReports();

            // Fetch reports on search input
            $('#search').on('keyup', function () {
                var query = $(this).val();
                fetchReports(query);
            });
        });

        function toggleRemarks(selectElement, reportId) {
            var remarksGroup = document.getElementById('remarks_group_' + reportId);
            if (selectElement.value === 'Rejected') {
                remarksGroup.style.display = 'block';
            } else {
                remarksGroup.style.display = 'none';
            }
        }
    </script>

</body>

</html>
