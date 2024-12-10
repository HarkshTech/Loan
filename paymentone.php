<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collect Payment</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 900px;
            margin-top: 18%;
            margin-left: 20% !important;
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
            width: 250px;
        }
        .action-column select {
            width: 150px;
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
                width: 200px;
            }
            .container .action-column select {
                width: 120px;
            }
        }
    </style>
</head>
<body>
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include 'config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['leadId'])) {
        // Retrieve form data
        $leadId = $_POST['leadId'];
        $paymentType = $_POST['paymentType'];
        $paymentReceiver = $_POST['paymentReceiver'];
        $receiverDetails = $_POST['receiverDetails'];

        // Prepare SQL query based on the payment type
        switch ($paymentType) {
            case 'collect_payment':
                $sql = "INSERT INTO emi_payments (LeadID, PaymentReceiver, ReceiverDetails, PaymentDate)
                        VALUES (?, ?, ?, NOW())";
                break;

            case 'partial_payment':
                $partialPaymentAmount = $_POST['partialPaymentAmount'];
                $sql = "INSERT INTO emi_payments (LeadID, PaymentReceiver, ReceiverDetails, PaymentAmount, PaymentDate)
                        VALUES (?, ?, ?, ?, NOW())";
                break;

            case 'advance_emi':
                $advanceEMICount = $_POST['advanceEMICount'];
                $sql = "INSERT INTO emi_payments (LeadID, PaymentReceiver, ReceiverDetails, EMIAdvances, PaymentDate)
                        VALUES (?, ?, ?, ?, NOW())";
                break;

            case 'penalty':
                $penaltyAmount = $_POST['penaltyAmount'];
                $sql = "INSERT INTO emi_payments (LeadID, PaymentReceiver, ReceiverDetails, PenaltyAmount, PaymentDate)
                        VALUES (?, ?, ?, ?, NOW())";
                break;

            default:
                echo "Invalid payment type.";
                exit;
        }

        // Prepare and bind parameters
        if ($stmt = mysqli_prepare($conn, $sql)) {
            switch ($paymentType) {
                case 'collect_payment':
                    mysqli_stmt_bind_param($stmt, "sss", $leadId, $paymentReceiver, $receiverDetails);
                    break;

                case 'partial_payment':
                    mysqli_stmt_bind_param($stmt, "sssd", $leadId, $paymentReceiver, $receiverDetails, $partialPaymentAmount);
                    break;

                case 'advance_emi':
                    mysqli_stmt_bind_param($stmt, "ssdi", $leadId, $paymentReceiver, $receiverDetails, $advanceEMICount);
                    break;

                case 'penalty':
                    mysqli_stmt_bind_param($stmt, "ssdi", $leadId, $paymentReceiver, $receiverDetails, $penaltyAmount);
                    break;
            }

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                echo "<div class='alert alert-success'>Payment recorded successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        } else {
            echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        }

        // Close the connection
        // mysqli_close($conn);
    }
    ?>

    <?php include 'leftbaraccounts.php'; ?>
    <div class="container mt-4">
        <h1>Collect Payment</h1>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Lead ID</th>
                        <th>Loan Amount</th>
                        <th>EMI Amount</th>
                        <th>Customer Name</th>
                        <th>Collection Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT e.ID, e.LeadID, e.LoanAmount, e.EMIAmount, p.FullName, e.NextPaymentDate
                            FROM emi_schedule e
                            JOIN personalinformation p ON e.LeadID = p.ID
                            WHERE e.PaidEMIs < e.TotalEMIs";
                    $result = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['ID'] . "</td>";
                            echo "<td>" . $row['LeadID'] . "</td>";
                            echo "<td>₹" . number_format($row['LoanAmount'], 2) . "</td>";
                            echo "<td>₹" . number_format($row['EMIAmount'], 2) . "</td>";
                            echo "<td>" . $row['FullName'] . "</td>";
                            echo "<td>" . $row['NextPaymentDate'] . "</td>";
                            echo "<td class='action-column'>
                                    <button type='button' class='btn btn-primary' data-toggle='modal' data-target='#actionModal' data-lead-id='" . $row['LeadID'] . "'>Select Action</button>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No pending payments found.</td></tr>";
                    }

                    // mysqli_close($conn);
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionModalLabel">Select Action</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="actionForm">
                        <div class="form-group">
                            <label for="paymentReceiver">Payment Receiver</label>
                            <select class="form-control" id="paymentReceiver" name="paymentReceiver" required>
                                <option value="">Select</option>
                                <option value="Agent">Agent</option>
                                <option value="Branch">Branch</option>
                                <option value="Head Office">Head Office</option>
                            </select>
                        </div>
                        <div class="form-group" id="agentDetails" style="display:none;">
                            <label for="agentName">Agent Name</label>
                            <input type="text" class="form-control" id="agentName" name="agentName">
                            <label for="agentBranch">Branch</label>
                            <input type="text" class="form-control" id="agentBranch" name="agentBranch">
                        </div>
                        <div class="form-group" id="branchDetails" style="display:none;">
                            <label for="branchName">Branch Name</label>
                            <input type="text" class="form-control" id="branchName" name="branchName">
                        </div>
                        <div class="form-group">
                            <label for="actionType">Action</label>
                            <select class="form-control" id="actionType" name="actionType" required>
                                <option value="">Select</option>
                                <option value="collect_payment">Collect Payment</option>
                                <option value="partial_payment">Partial Payment</option>
                                <option value="advance_emi">Advance EMIs</option>
                                <option value="penalty">Penalty</option>
                            </select>
                        </div>
                        <input type="hidden" name="leadId" id="actionLeadId">
                        <button type="submit" class="btn btn-primary">Next</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Modal for Payment Forms -->
    <div class="modal fade" id="dynamicModal" tabindex="-1" aria-labelledby="dynamicModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dynamicModalLabel">Payment Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm" method="POST" action="">
                        <input type="hidden" name="leadId" id="paymentLeadId">
                        <input type="hidden" name="paymentType" id="paymentType">
                        <input type="hidden" name="paymentReceiver" id="paymentReceiverHidden">
                        <input type="hidden" name="receiverDetails" id="receiverDetailsHidden">

                        <div class="form-group" id="partialPaymentAmountDiv" style="display:none;">
                            <label for="partialPaymentAmount">Partial Payment Amount</label>
                            <input type="number" class="form-control" id="partialPaymentAmount" name="partialPaymentAmount">
                        </div>
                        <div class="form-group" id="advanceEMICountDiv" style="display:none;">
                            <label for="advanceEMICount">Number of Advance EMIs</label>
                            <input type="number" class="form-control" id="advanceEMICount" name="advanceEMICount">
                        </div>
                        <div class="form-group" id="penaltyAmountDiv" style="display:none;">
                            <label for="penaltyAmount">Penalty Amount</label>
                            <input type="number" class="form-control" id="penaltyAmount" name="penaltyAmount">
                        </div>

                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {
    $('#actionModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var leadId = button.data('lead-id');
        var modal = $(this);
        modal.find('#actionLeadId').val(leadId);
    });

    $('#paymentReceiver').on('change', function() {
        var receiver = $(this).val();
        if (receiver === 'Agent') {
            $('#agentDetails').show();
            $('#branchDetails').hide();
        } else if (receiver === 'Branch') {
            $('#agentDetails').hide();
            $('#branchDetails').show();
        } else {
            $('#agentDetails').hide();
            $('#branchDetails').hide();
        }
    });

    $('#actionForm').on('submit', function(event) {
        event.preventDefault();
        var actionType = $('#actionType').val();
        var leadId = $('#actionLeadId').val();
        var paymentReceiver = $('#paymentReceiver').val();
        var receiverDetails = '';

        if (paymentReceiver === 'Agent') {
            receiverDetails = 'Name: ' + $('#agentName').val() + ', Branch: ' + $('#agentBranch').val();
        } else if (paymentReceiver === 'Branch') {
            receiverDetails = 'Branch: ' + $('#branchName').val();
        }

        $('#paymentLeadId').val(leadId);
        $('#paymentType').val(actionType);
        $('#paymentReceiverHidden').val(paymentReceiver);
        $('#receiverDetailsHidden').val(receiverDetails);

        if (actionType === 'partial_payment') {
            $('#partialPaymentAmountDiv').show();
            $('#advanceEMICountDiv').hide();
            $('#penaltyAmountDiv').hide();
        } else if (actionType === 'advance_emi') {
            $('#partialPaymentAmountDiv').hide();
            $('#advanceEMICountDiv').show();
            $('#penaltyAmountDiv').hide();
        } else if (actionType === 'penalty') {
            $('#partialPaymentAmountDiv').hide();
            $('#advanceEMICountDiv').hide();
            $('#penaltyAmountDiv').show();
        } else {
            $('#partialPaymentAmountDiv').hide();
            $('#advanceEMICountDiv').hide();
            $('#penaltyAmountDiv').hide();
        }

        $('#actionModal').modal('hide');

        setTimeout(function() {
            $('#dynamicModal').modal('show');
        }, 500); // Add a delay to ensure the first modal is fully hidden
    });

    $('#paymentForm').on('submit', function(event) {
        event.preventDefault();
        $.ajax({
            url: '', // Replace with your server URL
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#dynamicModal').modal('hide');
                $('.container').prepend(response);
                location.reload(); // Reload the page to fetch updated data
            },
            error: function(xhr, status, error) {
                alert('An error occurred: ' + error);
            }
        });
    });

    // Prevent focus event from bubbling up
    $('#partialPaymentAmount').on('focusin', function(event) {
        event.stopPropagation();
    });
});

    </script>

</body>
</html>
