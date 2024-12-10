<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';
include 'leftbaraccounts.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['paymentReceived'])) {
    if (!empty($_POST['paymentReceived'])) {
        // Extract the values from the form
        $paymentReceived = $_POST['paymentReceived'];
        $agentName = isset($_POST['agentName']) ? mysqli_real_escape_string($conn, $_POST['agentName']) : '';
        $branchName = isset($_POST['branchName']) ? mysqli_real_escape_string($conn, $_POST['branchName']) : '';
        $payment = $_POST['payment'];
        // $paymentID = mysqli_real_escape_string($conn, $_POST['paymentID']);
        $leadID = mysqli_real_escape_string($conn, $_POST['leadID']);
        $paymentDate = date('Y-m-d'); // Assuming current date for payment
        $emiAmount = 0; // Assuming no EMI amount provided in form
        $overdueDays = 0; // Assuming no overdue days provided in form
        $status = 'pending'; // Assuming a default status of 'pending'

        // Determine the correct values for PaymentReceiver and ReceiverDetails
        $paymentReceiver = '';
        $receiverDetails = '';
        if ($paymentReceived === 'agent') {
            $paymentReceiver = $agentName;
            $receiverDetails = $branchName;
        } else if ($paymentReceived === 'branch') {
            $paymentReceiver = '';
            $receiverDetails = $branchName;
        } else if ($paymentReceived === 'head_office') {
            $paymentReceiver = 'head_office';
            $receiverDetails = '';
        }

        // Insert data into the database
        $sql = "INSERT INTO emi_payments (PaymentID, LeadID, PaymentDate, EMIAmount, OverdueDays, Status, PaymentReceiver, ReceiverDetails)
                VALUES ('', '$leadID', '$paymentDate', '$emiAmount', '$overdueDays', '$status', '$paymentReceiver', '$receiverDetails')";

        if (mysqli_query($conn, $sql)) {
            echo "Payment data inserted successfully.";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    } else {
        echo "Payment Received field is required.";
    }
}

$sql = "SELECT e.ID, e.LeadID, e.LoanAmount, e.EMIAmount, p.FullName, e.NextPaymentDate
        FROM emi_schedule e
        JOIN personalinformation p ON e.LeadID = p.ID
        WHERE e.PaidEMIs < e.TotalEMIs";
$result = mysqli_query($conn, $sql);
?>

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
        .action-button {
            width: 120px;
            padding: 8px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }
        .action-button:hover {
            background-color: #0056b3;
        }
        .form-group.branch-name {
            display: none;
        }
        .form-group.payment-details {
            display: none;
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
            .container .action-button {
                width: 100px;
            }
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1>Collect Payment</h1>
    <div class="table-responsive">
        <table class="table table-striped">
            <!-- Table header -->
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Lead ID</th>
                    <th>Loan Amount</th>
                    <th>EMI Amount</th>
                    <th>Full Name</th>
                    <th>Next Payment Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <!-- Table body -->
            <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['ID'] . "</td>";
                    echo "<td>" . $row['LeadID'] . "</td>";
                    echo "<td>₹" . number_format($row['LoanAmount'], 2) . "</td>";
                    echo "<td>₹" . number_format($row['EMIAmount'], 2) . "</td>";
                    echo "<td>" . $row['FullName'] . "</td>";
                    echo "<td>" . $row['NextPaymentDate'] . "</td>";
                    echo "<td class='action-column'>";
                    echo "<button class='btn btn-primary action-button' data-id='" . $row['ID'] . "' data-toggle='modal' data-target='#collectPaymentModal'>Collect Payment</button>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No pending payments found.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="collectPaymentModal" tabindex="-1" role="dialog" aria-labelledby="collectPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="collectPaymentModalLabel">Collect Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form content goes here -->
                <form class="action-form mt-2" method="POST" action="">
                    <input type="hidden" name="paymentID" id="paymentID">
                    <input type="hidden" name="leadID" id="leadID">
                    <div class="form-group">
                        <label for="paymentReceived">Payment Received:</label>
                        <select class="form-control" name="paymentReceived" id="paymentReceived">
                            <option value="agent">Agent</option>
                            <option value="branch">Branch</option>
                            <option value="head_office">Head Office</option>
                        </select>
                    </div>
                    <div class="form-group agent-name">
                        <label for="agentName">Agent Name:</label>
                        <input type="text" class="form-control" name="agentName" id="agentName">
                    </div>
                    <div class="form-group branch-name">
                        <label for="branchName">Branch Name:</label>
                        <input type="text" class="form-control" name="branchName" id="branchName">
                    </div>
                    <div class="form-group">
                        <label for="payment">Payment:</label>
                        <select class="form-control" name="payment" id="payment">
                            <option value="collect">Collect</option>
                            <option value="partial">Partial</option>
                            <option value="emi">Advance EMI</option>
                            <option value="penalty">Penalty</option>
                        </select>
                    </div>
                    <div class="form-group payment-details">
                        <label for="paymentAmount">Enter Payment Amount:</label>
                        <input type="text" class="form-control" name="paymentAmount" id="paymentAmount">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Your JavaScript code -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function () {
        $('#collectPaymentModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var modal = $(this);
            modal.find('#paymentID').val(id);

            // Fetch LeadID based on the ID, for demonstration assume it's the same as ID
            modal.find('#leadID').val(id);
        });

        $('#paymentReceived').change(function() {
            var selectedOption = $(this).val();
            if (selectedOption === 'agent') {
                $('.agent-name').show();
                $('.branch-name').show(); // Show both fields for Agent
            } else if (selectedOption === 'branch') {
                $('.agent-name').hide(); // Hide Agent field
                $('.branch-name').show(); // Show only Branch field
            } else {
                $('.agent-name').hide();
                $('.branch-name').hide();
            }
        });

        $('#payment').change(function() {
            var selectedOption = $(this).val();
            if (selectedOption === 'collect' || selectedOption === 'partial' || selectedOption === 'penalty') {
                $('.payment-details').show();
            } else {
                $('.payment-details').hide();
            }
        });
    });
    $(document).ready(function () {
    // Hide payment details fields by default
    $('.payment-details').hide();

    $('#collectPaymentModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var modal = $(this);
        modal.find('#paymentID').val(id);

        // Fetch LeadID based on the ID, for demonstration assume it's the same as ID
        modal.find('#leadID').val(id);
    });

    $('#paymentReceived').change(function() {
        var selectedOption = $(this).val();
        if (selectedOption === 'agent') {
            $('.agent-name').show();
            $('.branch-name').show(); // Show both fields for Agent
        } else if (selectedOption === 'branch') {
            $('.agent-name').hide(); // Hide Agent field
            $('.branch-name').show(); // Show only Branch field
        } else {
            $('.agent-name').hide();
            $('.branch-name').hide();
        }
    });

    $('#payment').change(function() {
        var selectedOption = $(this).val();
        if (selectedOption === 'collect' || selectedOption === 'partial' || selectedOption === 'emi' || selectedOption === 'penalty') {
            $('.payment-details').show(); // Show payment details fields when relevant options are selected
        } else {
            $('.payment-details').hide(); // Hide payment details fields otherwise
        }
    });
});
$(document).ready(function () {
    // Hide payment details fields by default
    $('.payment-details').hide();

    $('#collectPaymentModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var modal = $(this);
        modal.find('#paymentID').val(id);

        // Fetch LeadID based on the ID, for demonstration assume it's the same as ID
        modal.find('#leadID').val(id);
    });

    $('#paymentReceived').change(function() {
        var selectedOption = $(this).val();
        if (selectedOption === 'agent') {
            $('.agent-name').show();
            $('.branch-name').show(); // Show both fields for Agent
        } else if (selectedOption === 'branch') {
            $('.agent-name').hide(); // Hide Agent field
            $('.branch-name').show(); // Show only Branch field
        } else {
            $('.agent-name').hide();
            $('.branch-name').hide();
        }
    });

    $('#payment').change(function() {
        var selectedOption = $(this).val();
        if (selectedOption === 'collect' || selectedOption === 'partial' || selectedOption === 'emi' || selectedOption === 'penalty') {
            $('.payment-details').show(); // Show payment details fields when relevant options are selected
        } else {
            $('.payment-details').hide(); // Hide payment details fields otherwise
        }
    });

    // Form submission handling
    $('form.action-form').submit(function(event) {
        event.preventDefault(); // Prevent default form submission

        var paymentOption = $('#payment').val();
        if (paymentOption === 'collect') {
            // Fetch EMIAmount from emi_schedule using Ajax
            var leadID = $('#leadID').val();
            $.ajax({
                url: 'fetch_emi_amount.php', // Provide the correct path to your PHP script
                method: 'POST',
                data: { leadID: leadID },
                success: function(response) {
                    var emiAmount = parseFloat(response);
                    if (!isNaN(emiAmount)) {
                        // Set the EMIAmount field value
                        $('#paymentAmount').val(emiAmount);
                    } else {
                        alert('Failed to fetch EMIAmount.');
                    }
                    // Submit the form
                    $('form.action-form')[0].submit();
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    alert('Error fetching EMIAmount.');
                }
            });
        } else {
            // For other payment options, submit the form directly
            this.submit();
        }
    });
});



</script>
<script>
    $(document).ready(function () {
        // Hide payment details fields by default
        $('.payment-details').hide();

        $('#collectPaymentModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var modal = $(this);
            modal.find('#paymentID').val(id);

            // Fetch LeadID based on the ID, for demonstration assume it's the same as ID
            modal.find('#leadID').val(id);
        });

        $('#paymentReceived').change(function() {
            var selectedOption = $(this).val();
            if (selectedOption === 'agent') {
                $('.agent-name').show();
                $('.branch-name').show(); // Show both fields for Agent
            } else if (selectedOption === 'branch') {
                $('.agent-name').hide(); // Hide Agent field
                $('.branch-name').show(); // Show only Branch field
            } else {
                $('.agent-name').hide();
                $('.branch-name').hide();
            }
        });

        $('#payment').change(function() {
            var selectedOption = $(this).val();
            if (selectedOption === 'collect' || selectedOption === 'partial' || selectedOption === 'emi' || selectedOption === 'penalty') {
                $('.payment-details').show(); // Show payment details fields when relevant options are selected
            } else {
                $('.payment-details').hide(); // Hide payment details fields otherwise
            }

            // Hide payment amount input field if the selected option is "collect"
            if (selectedOption === 'collect') {
                $('#paymentAmount').hide();
            } else {
                $('#paymentAmount').show();
            }
        });

        // Form submission handling
        $('form.action-form').submit(function(event) {
            event.preventDefault(); // Prevent default form submission

            var paymentOption = $('#payment').val();
            if (paymentOption === 'collect') {
                // Fetch EMIAmount from emi_schedule using Ajax
                var leadID = $('#leadID').val();
                $.ajax({
                    url: 'fetch_emi_amount.php', // Provide the correct path to your PHP script
                    method: 'POST',
                    data: { leadID: leadID },
                    success: function(response) {
                        var emiAmount = parseFloat(response);
                        if (!isNaN(emiAmount)) {
                            // Set the EMIAmount field value
                            $('#paymentAmount').val(emiAmount);
                        } else {
                            alert('Failed to fetch EMIAmount.');
                        }
                        // Submit the form
                        $('form.action-form')[0].submit();
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        alert('Error fetching EMIAmount.');
                    }
                });
            } else {
                // For other payment options, submit the form directly
                this.submit();
            }
        });
    });
    


</script>

</body>
</html>
