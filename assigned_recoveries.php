<?php
session_start();
include 'config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$username = $_SESSION['username'];
$role = $_SESSION['role'];

if($role==='admin'){
    $redirect='dashboard.php';
}
elseif($role==='branchmanager'){
    $redirect='branchmanager.php';
}
elseif($role==='recovery'){
    $redirect='dashboardrecovery.php';
}


$loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Check if form for updating lead status is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leadID']) && isset($_POST['leadStatus'])) {
    $leadID = $_POST['leadID'];
    $leadStatus = $_POST['leadStatus'];

    // Redirect to avoid form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Check if remarks update is requested via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['remarks']) && isset($_POST['visitNeeded']) && isset($_POST['visitSchedule'])) {
    $id = $_POST['id'];
    $remarks = $_POST['remarks'];
    $visitNeeded = $_POST['visitNeeded'];
    $visitSchedule = $_POST['visitSchedule'];

    // Append today's date to the remarks
    $currentDate = date('Y-m-d');
    $remarks = "$currentDate: $remarks";

    // Set visit status based on visitNeeded
    $visitStatus = ($visitNeeded === 'YES') ? 'Pending' : '';

    // Update remarks and other fields in recovery_data table
    $sqlUpdateRemarks = "UPDATE recovery_data SET Remarks = ?, VisitNeeded = ?, visitscheduled = ?, visitstatus = ? WHERE ID = ?";
    $stmt = $conn->prepare($sqlUpdateRemarks);
    $stmt->bind_param("ssssi", $remarks, $visitNeeded, $visitSchedule, $visitStatus, $id);

    if ($stmt->execute()) {
        echo "Remarks updated successfully.";
    } else {
        echo "Failed to update remarks.";
    }

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
                            <li class="breadcrumb-item"><a href="<?php echo $redirect;?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Assigned Recoveries</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="mb-4" style="font-size: 20px; line-height: 27px;">Assigned Recoveries Management</h1>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Lead ID</th>
                        <th>Lead Details</th>
                        <th>Overdue Days</th>
                        <th>EMI Amount</th>
                        <th>Assigned To</th>
                        <th>Assigned Date</th>
                        <th>Visit Needed</th>
                        <th>Case Status</th>
                        <th>Next Steps</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($loggedInUser) {
                        $recoveryperson = $loggedInUser;

                    if($_SESSION['role']==='recovery'){
                        $sql = "SELECT rd.*,pi.ID leadid, pi.FullName, pi.PhoneNumber, pi.Address, es.EMIAmount
                                FROM recovery_data rd
                                JOIN personalinformation pi ON rd.LeadID = pi.ID
                                LEFT JOIN emi_schedule es ON rd.LeadID = es.LeadID
                                WHERE rd.AssignedTo = ? AND (rd.Remarks IS NULL OR rd.Remarks = '')";
                    }
                    else{
                        $sql = "SELECT rd.*,pi.ID leadid, pi.FullName, pi.PhoneNumber, pi.Address, es.EMIAmount
                                FROM recovery_data rd
                                JOIN personalinformation pi ON rd.LeadID = pi.ID
                                LEFT JOIN emi_schedule es ON rd.LeadID = es.LeadID
                                WHERE (rd.Remarks IS NULL OR rd.Remarks = '')";
                    }
                        $stmt = $conn->prepare($sql);
                    if($_SESSION['role']==='recovery'){
                        $stmt->bind_param("s", $recoveryperson);
                    }
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // Mask the details
                                $maskedFullName = str_repeat('*', strlen($row["FullName"]) - 2) . substr($row["FullName"], -2);
                                $maskedPhoneNumber = str_repeat('*', strlen($row["PhoneNumber"]) - 4) . substr($row["PhoneNumber"], -4);
                                $maskedAddress = str_repeat('*', strlen($row["Address"]) - 4) . substr($row["Address"], -4);

                                echo "<tr class='main-row'>";
                                echo "<td>" . $row["ID"] . "</td>";
                                echo "<td>" . $row["leadid"] . "</td>";
                                echo "<td><span class='masked-details' data-id='" . $row["ID"] . "' data-fullname='" . $row["FullName"] . "' data-phone='" . $row["PhoneNumber"] . "' data-address='" . $row["Address"] . "'>
                                        $maskedFullName, $maskedPhoneNumber, $maskedAddress
                                      </span></td>";
                                echo "<td>" . $row["Overdue_days"] . "</td>";
                                echo "<td>" . $row["EMIAmount"] . "</td>";
                                echo "<td>" . $row["AssignedTo"] . "</td>";
                                echo "<td>" . $row["AssignedDate"] . "</td>";
                                echo "<td>" . $row["VisitNeeded"] . "</td>";
                                echo "<td>" . $row["CaseStatus"] . "</td>";
                                echo "<td>" . $row["NextSteps"] . "</td>";
                                echo "<td>" . $row["Remarks"] . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10' class='text-center'>No leads found</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10' class='text-center'>You are not authorized to view this data</td></tr>";
                    }

                    $stmt->close();
                    $conn->close();
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
                    <!-- Section to display hidden details -->
                    <div id="detailsSection" style="margin-bottom: 15px;">
                        <p><strong>Full Name:</strong> <span id="detailFullName"></span></p>
                        <p><strong>Phone Number:</strong> <span id="detailPhoneNumber"></span></p>
                        <p><strong>Address:</strong> <span id="detailAddress"></span></p>
                    </div>
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

    <script>
    $(document).ready(function () {
        $('.masked-details').on('click', function () {
            var id = $(this).data('id');
            var fullName = $(this).data('fullname');
            var phoneNumber = $(this).data('phone');
            var address = $(this).data('address');

            $('#id').val(id);
            $('#detailFullName').text(fullName);
            $('#detailPhoneNumber').text(phoneNumber);
            $('#detailAddress').text(address);

            $('#remarks').val('');
            $('#visitNeeded').val('NO');
            $('#visitSchedule').val('');
            $('#visitScheduleGroup').hide();

            $('#remarksModal').modal('show');
        });

        $('#visitNeeded').on('change', function () {
            if ($(this).val() === 'YES') {
                $('#visitScheduleGroup').show();
            } else {
                $('#visitScheduleGroup').hide();
            }
        });

        $('#remarksForm').on('submit', function (e) {
            e.preventDefault();

            var id = $('#id').val();
            var remarks = $('#remarks').val();
            var visitNeeded = $('#visitNeeded').val();
            var visitSchedule = visitNeeded === 'YES' ? $('#visitSchedule').val() : '';

            $.ajax({
                url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                type: 'POST',
                data: {
                    id: id,
                    remarks: remarks,
                    visitNeeded: visitNeeded,
                    visitSchedule: visitSchedule
                },
                success: function (response) {
                    alert(response);
                    $('#remarksModal').modal('hide');
                    location.reload();  // Reload the page to see the updates
                },
                error: function () {
                    alert('An error occurred while updating remarks.');
                }
            });
        });
    });
    </script>
</body>
</html>
