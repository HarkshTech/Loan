<?php
    session_start();
    include 'config.php';
    
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    $username = $_SESSION['username'];
    $role = $_SESSION['role'];
    
    $loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;
    
    // Check if form for updating lead status is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leadID']) && isset($_POST['leadStatus'])) {
        $leadID = $_POST['leadID'];
        $leadStatus = $_POST['leadStatus'];
    
    
        // Redirect to avoid form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recovery Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1100px;
            margin: 100px auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .page-title-box {
            margin-bottom: 30px;
        }
        .table th {
            background-color: #343a40;
            color: white;
        }
        .table td {
            vertical-align: middle;
        }
        .lead-status {
            max-width: 150px;
        }
        .action-column {
            width: 180px;
        }
        .action-column select {
            width: 130px;
        }
        .details-row {
            display: none;
        }
        .masked-details {
            cursor: pointer;
            color: #007bff;
            text-decoration: underline;
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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome!</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="dashboardrecovery.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Assigned Recoveries</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="mb-4">Assigned Recoveries Management</h1>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Lead Details</th>
                        <th>Overdue Days</th>
                        <th>Assigned To</th>
                        <th>Assigned Date</th>
                        <th>Visit Needed</th>
                        <th>Case Status</th>
                        <th>Next Steps</th>
                        <th>Remarks</th>
                        <!--<th>Action</th>-->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($loggedInUser) {
                        $recoveryperson = $loggedInUser;

                        $sql = "SELECT * FROM recovery_data WHERE CaseStatus = 'Legal Case' ";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // Example masking function
                                // $maskedDetails = maskDetails($row["LeadDetails"]);  // Replace with your masking logic

                                echo "<tr>";
                                echo "<td>" . $row["ID"] . "</td>";
                                echo "<td>" . $row["LeadID"] . "</td>";
                                echo "<td>" . $row["Overdue_days"] . "</td>";
                                echo "<td>" . $row["AssignedTo"] . "</td>";
                                echo "<td>" . $row["AssignedDate"] . "</td>";
                                echo "<td>" . $row["VisitNeeded"] . "</td>";
                                echo "<td>" . $row["CaseStatus"] . "</td>";
                                echo "<td>" . $row["NextSteps"] . "</td>";
                                echo "<td>" . $row["Remarks"] . "</td>";
                                // echo "<td class='action-column'>
                                //         <button class='btn btn-primary masked-details' 
                                //                 data-id='" . $row["ID"] . "'
                                //                 data-details='" . $row["LeadDetails"] . "'
                                //                 data-toggle='modal' data-target='#remarksModal'>Update Remarks</button>
                                //       </td>";
                                // echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10' class='text-center'>No leads found</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10' class='text-center'>You are not authorized to view this data</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for entering remarks -->
    <div class="modal fade" id="remarksModal" tabindex="-1" role="dialog" aria-labelledby="remarksModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="remarksModalLabel">Enter Remarks</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="remarksForm">
                        <div class="form-group">
                            <label for="remarks">Remarks</label>
                            <input type="text" class="form-control" id="remarks" name="remarks" required>
                        </div>
                        <div class="form-group">
                            <label for="visitNeeded">Visit Needed</label>
                            <select class="form-control" id="visitNeeded" name="visitNeeded" required>
                                <option value="NO">NO</option>
                                <option value="YES">YES</option>
                            </select>
                        </div>
                        <div class="form-group" id="visitScheduleGroup" style="display:none;">
                            <label for="visitSchedule">Visit Schedule Date</label>
                            <input type="date" class="form-control" id="visitSchedule" name="visitSchedule">
                        </div>
                        <input type="hidden" id="id" name="id">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

