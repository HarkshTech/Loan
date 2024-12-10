<?php

session_start();
$bmname = $_SESSION['username'];
$role = $_SESSION['role'];
include 'config.php';

// Approve payment
if (isset($_POST['approve']) && isset($_POST['LeadID'])) {
    $paymentID = $_POST['approve'];
    $leadId = $_POST['LeadID'];
    
    $sql = "UPDATE emi_payments SET bmapproval = '1', remarks ='' WHERE PaymentID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param("i", $paymentID);
    $stmt->execute();
    
    // start
    
    // end
    $stmt->close();
}

// Reject payment
if (isset($_POST['reject']) && isset($_POST['remarks']) && isset($_POST['LeadID'])) {
    $paymentID = $_POST['reject'];
    $remarks = $_POST['remarks'];
    $leadId = $_POST['LeadID'];
    
    $conn->begin_transaction();

    try {
        // 2=payment rejected, 1=approved, 0=pending
        $sql = "UPDATE emi_payments SET bmapproval = '2', remarks = ? WHERE PaymentID = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param("si", $remarks, $paymentID);
        $stmt->execute();
        $stmt->close();

        $updateSql = "UPDATE emi_schedule SET PaidEMIs = PaidEMIs - 1 WHERE LeadID = ? AND PaidEMIs > 0";
        $stmt2 = $conn->prepare($updateSql);
        if ($stmt2 === false) {
            throw new Exception($conn->error);
        }
        $stmt2->bind_param("i", $leadId);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

// Search payments
$searchQuery = "SELECT * FROM emi_payments WHERE 1=1";
$conditions = [];
$params = [];

if (!empty($_POST['PaymentID'])) {
    $conditions[] = "PaymentID = ?";
    $params[] = $_POST['PaymentID'];
}
if (!empty($_POST['LeadOrName'])) {
    $conditions[] = "(LeadID = ? OR PaymentReceiver LIKE ?)";
    $params[] = $_POST['LeadOrName'];
    $params[] = "%" . $_POST['LeadOrName'] . "%";
}
if (!empty($_POST['PaymentDateFrom']) && !empty($_POST['PaymentDateTo'])) {
    $conditions[] = "PaymentDate BETWEEN ? AND ?";
    $params[] = $_POST['PaymentDateFrom'];
    $params[] = $_POST['PaymentDateTo'];
}

// Filter by approval status
if (isset($_POST['filterStatus'])) {
    switch ($_POST['filterStatus']) {
        case 'pending':
            $conditions[] = "(bmapproval = '0' OR superapproval = '0')";
            break;
        case 'approved':
            $conditions[] = "(bmapproval = '1' AND superapproval = '1')";
            break;
        case 'rejected':
            $conditions[] = "(bmapproval = '2' OR superapproval = '2')";
            break;
    }
}

if ($conditions) {
    $searchQuery .= " AND " . implode(" AND ", $conditions);
}

$stmt = $conn->prepare($searchQuery);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMI Payments</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
        }

        .search-bar {
            margin-bottom: 20px;
            background: #ffffff;
            padding: 5px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: 75px;
        }

        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }

        table {
            width: 100%;
            table-layout: fixed;
        }

        th, td {
            word-wrap: break-word;
        }

        .table thead th {
            background-color: #343a40;
            color: #ffffff;
        }

        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        
        table#paymentTable {
            width: 180% !important;
        }

        .btn-approve, .btn-reject {
            display: flex;
            align-items: center;
            padding: 6px 12px;
            font-size: 14px;
            margin: 5px;
        }

        .btn-approve .fa-check, .btn-reject .fa-times {
            margin-right: 5px;
        }

        .modal-content {
            border-radius: 8px;
        }

        .modal-header, .modal-footer {
            border: none;
        }

        .form-inline .form-group {
            /*margin-right: 15px;*/
        }

        .chip {
            display: inline-block;
            padding: 0.5rem;
            font-size: 0.9rem;
            border-radius: 25px;
            color: white;
        }

        .chip.pending {
            background-color: orange;
        }

        .chip.approved {
            background-color: green;
        }

        .chip.rejected {
            background-color: red;
        }
        
        .filter-buttons {
            margin-bottom: 20px;
            /*text-align: center;*/
        }

        .filter-buttons button {
            margin: 5px;
        }
/*        form#searchForm {*/
/*    height: 20pc;*/
/*}*/
    </style>
    <script>
        $(document).ready(function () {
    function searchPayments() {
        $.ajax({
            type: "POST",
            url: "approve_payments_bm.php",
            data: $("#searchForm").serialize(),
            success: function (response) {
                var newBody = $(response).find("#paymentTable tbody").html();
                $("#paymentTable tbody").html(newBody);
            }
        });
    }

    function filterPayments(status) {
        $.ajax({
            type: "POST",
            url: "approve_payments_bm.php",
            data: { filterStatus: status },
            success: function (response) {
                var newBody = $(response).find("#paymentTable tbody").html();
                $("#paymentTable tbody").html(newBody);
            }
        });
    }

    $("#searchForm input").on("input", function () {
        searchPayments();
    });

    $(document).on("click", ".approve-btn", function () {
        var paymentID = $(this).data("id");
        if (confirm("Sure, Approve Payment: " + paymentID + "?")) {
            $.ajax({
                type: "POST",
                url: "approve_payments_bm.php",
                data: { approve: paymentID, LeadID: $(this).closest('tr').find('td:nth-child(2)').text() },
                success: function (response) {
                    var newBody = $(response).find("#paymentTable tbody").html();
                    $("#paymentTable tbody").html(newBody);
                },
                error: function (xhr, status, error) {
                    console.log("Error: " + error);
                }
            });
        }
    });

    $(document).on("click", ".reject-btn", function () {
        var paymentID = $(this).data("id");
        if (confirm("Sure, Reject Payment: " + paymentID + "?")) {
            $('#rejectPaymentID').val(paymentID);
            $('#rejectLeadID').val($(this).closest('tr').find('td:nth-child(2)').text());
            $('#remarksModal').modal('show');
        }
    });

    $('#submitRemarks').click(function () {
        var paymentID = $('#rejectPaymentID').val();
        var remarks = $('#remarks').val();
        var leadID = $('#rejectLeadID').val();
        $.ajax({
            type: "POST",
            url: "approve_payments_bm.php",
            data: { reject: paymentID, remarks: remarks, LeadID: leadID },
            success: function (response) {
                var newBody = $(response).find("#paymentTable tbody").html();
                $("#paymentTable tbody").html(newBody);
                $('#remarksModal').modal('hide');
            },
            error: function (xhr, status, error) {
                console.log("Error: " + error);
            }
        });
    });

    // Filter button handlers
    $('#filter-all').click(function () {
        filterPayments('all');
    });
    $('#filter-pending').click(function () {
        filterPayments('pending');
    });
    $('#filter-approved').click(function () {
        filterPayments('approved');
    });
    $('#filter-rejected').click(function () {
        filterPayments('rejected');
    });
});

        
        
    </script>
</head>

<body>
    <?php include 'leftsidebranch.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="branchmanager.php">Dashboard</a></li>
                            <li class="breadcrumb-item">Welcome !</li>
                            <li class="breadcrumb-item active">Approve Collected EMI's</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h2 class="text-center mb-4">Collected Payments</h2>
        <form id="searchForm" class="search-bar form-inline justify-content-center" method="POST">
            <div class="form-group">
                <label for="PaymentID" class="mr-2">Payment ID:</label>
                <input type="text" class="form-control mr-2" id="PaymentID" name="PaymentID">
            </div>
            <div class="form-group">
                <label for="LeadOrName" class="mr-2">Lead ID or Name:</label>
                <input type="text" class="form-control mr-2" id="LeadOrName" name="LeadOrName">
            </div>
            <div class="form-group">
                <label for="PaymentDateFrom" class="mr-2">Date From:</label>
                <input type="date" class="form-control mr-2" id="PaymentDateFrom" name="PaymentDateFrom">
            </div>
            <div class="form-group">
                <label for="PaymentDateTo" class="mr-2">Date To:</label>
                <input type="date" class="form-control mr-2" id="PaymentDateTo" name="PaymentDateTo">
            </div>
        </form>
        <div class="filter-buttons">
            <button id="filter-all" class="btn btn-secondary">All</button>
            <button id="filter-pending" class="btn btn-warning">Pending</button>
            <button id="filter-approved" class="btn btn-success">Approved</button>
            <button id="filter-rejected" class="btn btn-danger">Rejected</button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered" id="paymentTable">
                <thead class="thead-dark">
                    <tr>
                        <th>Payment ID</th>
                        <th>LeadID</th>
                        <th>Payment Date</th>
                        <th>EMI Amount</th>
                        <th>PaymentType</th>
                        <th>Overdue Days</th>
                        <th>Status</th>
                        <th>Receiver</th>
                        <!--<th>Receiver Details</th>-->
                        <th>BM Approval</th>
                        <th>Super Approval</th>
                        <th>Collected By</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['PaymentID'] ?></td>
                        <td><?= $row['LeadID'] ?></td>
                        <td><?= $row['PaymentDate'] ?></td>
                        <td><?= $row['EMIAmount'] ?></td>
                        <td><?= $row['PaymentType'] ?></td>
                        <td><?= $row['OverdueDays'] ?></td>
                        <td><?= $row['Status'] ?></td>
                        <td><?= $row['PaymentReceiver'] ?></td>
                        <!--<td><?= $row['ReceiverDetails'] ?></td>-->
                        <td>
                            <?php if ($row['bmapproval'] == 0): ?>
                                <span class="chip pending">Pending</span>
                            <?php elseif ($row['bmapproval'] == 1): ?>
                                <span class="chip approved">Approved</span>
                            <?php elseif ($row['bmapproval'] == 2): ?>
                                <span class="chip rejected">Rejected</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['superapproval'] == 0): ?>
                                <span class="chip pending">Pending</span>
                            <?php elseif ($row['superapproval'] == 1): ?>
                                <span class="chip approved">Approved</span>
                            <?php elseif ($row['superapproval'] == 2): ?>
                                <span class="chip rejected">Rejected</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $row['collector'] ?></td>
                        <td><?= $row['remarks'] ?></td>
                        <td>
                            <button class="btn btn-success btn-approve approve-btn" data-id="<?= $row['PaymentID'] ?>"><i class="fa fa-check"></i> Approve</button>
                            <button class="btn btn-danger btn-reject reject-btn" data-id="<?= $row['PaymentID'] ?>"><i class="fa fa-times"></i> Reject</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for remarks -->
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
                    <div class="form-group">
                        <label for="remarks">Remarks:</label>
                        <textarea class="form-control" id="remarks" rows="3"></textarea>
                        <input type="hidden" id="rejectPaymentID">
                        <input type="hidden" id="rejectLeadID">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="submitRemarks">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
