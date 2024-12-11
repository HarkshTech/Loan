<?php
    session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    if (!isset($_SESSION['username'])) {
        // Redirect to the login page if not logged in
        header("Location: login.php");
        exit();
    }
    include 'config.php';
    
    date_default_timezone_set('Asia/Kolkata');

// Set the time zone to Asia/Kolkata in MySQL
$conn->query("SET time_zone = '+05:30'");

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
            /*margin-left: 20% !important;*/
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
    include 'config.php';
    if($_SESSION['role']==='admin'){
            $redirectUrl= 'dashboard.php';
        }
        elseif($_SESSION['role']==='branchmanager'){
            $redirectUrl= 'branchmanager.php';
        }
        elseif($_SESSION['role']==='accounts'){
            $redirectUrl= 'dashboardapproved_loans.php';
        }
    

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['leadId'])) {
        // Retrieve form data
        $leadId = $_POST['leadId'];
        
        $paymentType = $_POST['paymentType'];
        $paymentReceiver = $_POST['paymentReceiver'];
        $receiverDetails = $_POST['receiverDetails'];
        $paymentD=$_POST['paymentDate'];
        
        $paymentAmount = isset($_POST['paymentAmount']) ? $_POST['paymentAmount'] : 0;
        $penaltyAmount = isset($_POST['penaltyAmount']) ? $_POST['penaltyAmount'] : 0;
        $penaltyReason = isset($_POST['penaltyReason']) ? $_POST['penaltyReason'] : '';
        $user=$_SESSION['username'];

        // Prepare SQL query based on the payment type
        switch ($paymentType) {
            case 'collect_payment':
                if ($paymentType === 'collect_payment') {
                    $updateSql = "UPDATE emi_schedule SET PaidEMIs = PaidEMIs + 1 WHERE LeadID = $leadId AND PaidEMIs < TotalEMIs";
                    if ($conn->query($updateSql) === TRUE) {
                        $fetchSql = "SELECT EMIAmount, overdue_days, LastPaymentDate, NextPaymentDate, PaidEMIs, TotalEMIs FROM emi_schedule WHERE LeadID = $leadId";
                        $fetchResult = mysqli_query($conn, $fetchSql);
                        $row = mysqli_fetch_assoc($fetchResult);
                
                        // Ensure LastPaymentDate and NextPaymentDate are valid
                        $lastPaymentDate = !empty($row['LastPaymentDate']) ? $row['LastPaymentDate'] : '0000-00-00'; // Assign a default value if empty
                        $nextPaymentDate = !empty($row['NextPaymentDate']) ? $row['NextPaymentDate'] : '0000-00-00'; // Assign a default value if empty
                
                        $emiAmount = $row['EMIAmount'];
                        $overdueDays = $row['overdue_days'];
                        $paidEMIs = $row['PaidEMIs'];
                        $totalEMIs = $row['TotalEMIs'];
                        $status = 'Paid';
                        
                        // Insert the payment record
                        $insertSql = "INSERT INTO emi_payments (LeadID, PaymentDate, EMIAmount, PaymentType, OverdueDays, Status, PaymentReceiver, ReceiverDetails, bmapproval, superapproval, collector) 
                                      VALUES ('$leadId', '$paymentD', '$emiAmount', '$paymentType', '$overdueDays', '$status', '$paymentReceiver', '$receiverDetails', '0', '0', '$user')";
                        if ($conn->query($insertSql) === TRUE) {
                            // Calculate the next payment date as the same day of the next month
                            $calcnextPaymentDate = date('Y-m-d', strtotime('+1 month', strtotime($nextPaymentDate)));
                            
                            // Keep track of old dates
                            $oldLastPaymentDate = $lastPaymentDate;
                            $oldNextPaymentDate = $nextPaymentDate;
                            
                            $updateDatesSql = "UPDATE emi_schedule 
                                              SET LastPaymentDate = NOW(), 
                                                  NextPaymentDate = '$calcnextPaymentDate',
                                                  oldLastPaymentDate = '$oldLastPaymentDate',
                                                  oldNextPaymentDate = '$oldNextPaymentDate'
                                              WHERE LeadID = $leadId";
                            
                            if ($conn->query($updateDatesSql) === TRUE) {
                                echo "<div class='alert alert-success'>Payment collected successfully.</div>";
                            } else {
                                echo "<div class='alert alert-danger'>Error updating payment dates: " . $conn->error . "</div>";
                            }
                
                            // Check if PaidEMIs is equal to TotalEMIs and update LoanStatus if true
                            if ($paidEMIs >= $totalEMIs) {
                                $updateLoanStatusSql = "UPDATE personalinformation SET LoanStatus = 'Closed' WHERE ID = $leadId";
                                if ($conn->query($updateLoanStatusSql) === TRUE) {
                                    echo "<div class='alert alert-success'>Loan status updated to Closed.</div>";
                                } else {
                                    echo "<div class='alert alert-danger'>Error updating loan status: " . $conn->error . "</div>";
                                }
                            }
                
                            echo "<div class='alert alert-success'>Payment Collected Successfully, Submitted for Approval.</div>";
                        } else {
                            echo "<div class='alert alert-danger'>Error collecting payment: " . $conn->error . "</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>Error updating EMI schedule: " . $conn->error . "</div>";
                    }
                }
                
                break;



            case 'partial_payment':
                $partialPaymentAmount = $_POST['partialPaymentAmount'];
                if ($paymentType === 'partial_payment') {
                    $partialpayment = $_POST['partialPaymentAmount'];
                    $fetchSql = "SELECT EMIAmount, PartialPayment, LastPaymentDate, NextPaymentDate, PaidEMIs, TotalEMIs FROM emi_schedule WHERE LeadID = '$leadId'";
                    $fetchResult = mysqli_query($conn, $fetchSql);
                    $row = mysqli_fetch_assoc($fetchResult);
                    $emiAmount = $row['EMIAmount'];
                    $partialPayment = $row['PartialPayment'];
                    $nextPaymentDate = $row['NextPaymentDate'];
                    $paidEMIs = $row['PaidEMIs'];
                    $totalEMIs = $row['TotalEMIs'];
            
                    $newPartialPayment = $partialPayment + $partialpayment;
                    function getOrdinalSuffix($number) {
                        if (!in_array(($number % 100), array(11,12,13))){
                            switch ($number % 10) {
                                case 1:  return 'st';
                                case 2:  return 'nd';
                                case 3:  return 'rd';
                            }
                        }
                        return 'th';
                    }
                    
                    if ($newPartialPayment >= $emiAmount) {
                        // Calculate the exact amount that leads to full EMI payment
                        $amountUsedForEMI = $emiAmount - $partialPayment;  // This will be the difference between EMI amount and previous partial payment
                    
                        // Calculate the remaining partial payment for the next EMI cycle
                        $remainingAmount = $newPartialPayment - $emiAmount;
                    
                        // Increment the PaidEMIs to reflect which EMI was just paid
                        $updatedPaidEMIs = $paidEMIs + 1; // This is the current EMI number being completed
                    
                        // Prepare a message that includes the number of the EMI being completed
                        $emiMessage = "Partial Payment Adjusted in " . $updatedPaidEMIs . getOrdinalSuffix($updatedPaidEMIs) . " EMI";
                    
                        // Update the emi_schedule with the remaining amount and increment PaidEMIs
                        $updateSql = "UPDATE emi_schedule SET PaidEMIs = $updatedPaidEMIs, PartialPayment = $remainingAmount WHERE LeadID = $leadId";
                        
                        if ($conn->query($updateSql) === TRUE) {
                            $status = 'Paid';
                    
                            // Insert only the amount that led to the full EMI payment and include EMI number in the payment type
                            $insertSql = "INSERT INTO emi_payments (LeadID, PaymentDate, EMIAmount, PaymentType, OverdueDays, Status, PaymentReceiver, ReceiverDetails, bmapproval, superapproval, collector) 
                                          VALUES ('$leadId', NOW(), '$amountUsedForEMI', '$emiMessage', 0, '$status', '$paymentReceiver', '$receiverDetails', '0', '0', '$user')";
                    
                            if ($conn->query($insertSql) === TRUE) {
                                // Continue with updating NextPaymentDate and other fields
                                $nextPaymentDate = new DateTime($nextPaymentDate);
                                $nextPaymentDate->modify('first day of next month');
                                $nextPaymentDate->modify('+' . $nextPaymentDate->format('j') . ' days');
                    
                                $updateDatesSql = "UPDATE emi_schedule 
                                                  SET LastPaymentDate = NOW(), 
                                                      NextPaymentDate = '" . $nextPaymentDate->format('Y-m-d') . "'
                                                  WHERE LeadID = $leadId";
                                                  
                                if ($conn->query($updateDatesSql) === TRUE) {
                                    // Check if PaidEMIs is equal to TotalEMIs and update LoanStatus if true
                                    if ($updatedPaidEMIs >= $totalEMIs) {
                                        $updateLoanStatusSql = "UPDATE personalinformation SET LoanStatus = 'Closed' WHERE ID = $leadId";
                                        if ($conn->query($updateLoanStatusSql) === TRUE) {
                                            echo "<div class='alert alert-success'>Loan status updated to Closed.</div>";
                                        } else {
                                            echo "<div class='alert alert-danger'>Error updating loan status: " . $conn->error . "</div>";
                                        }
                                    }
                                    echo "<div class='alert alert-success'>Partial payment collected and full EMI paid successfully. Remaining amount: ₹" . number_format($remainingAmount, 2) . "</div>";
                                } else {
                                    echo "<div class='alert alert-danger'>Error updating payment dates: " . $conn->error . "</div>";
                                }
                            } else {
                                echo "<div class='alert alert-danger'>Error collecting payment: " . $conn->error . "</div>";
                            }
                        } else {
                            echo "<div class='alert alert-danger'>Error updating EMI schedule: " . $conn->error . "</div>";
                        }
                    }
                    
                     else {
                        $updateSql = "UPDATE emi_schedule SET PartialPayment = $newPartialPayment WHERE LeadID = $leadId";
                        if ($conn->query($updateSql) === TRUE) {
                            $status = 'Paid';
                            $insertSql = "INSERT INTO emi_payments (LeadID, PaymentDate, EMIAmount, PaymentType, OverdueDays, Status, PaymentReceiver, ReceiverDetails, bmapproval, superapproval, collector) 
                                          VALUES ('$leadId', NOW(), '$partialPaymentAmount', 'Partial Payment', 0, '$status', '$paymentReceiver', '$receiverDetails', '0', '0', '$user')";
                            if ($conn->query($insertSql) === TRUE) {
                                echo "<div class='alert alert-success'>Partial payment collected successfully.</div>";
                            } else {
                                echo "<div class='alert alert-danger'>Error collecting payment: " . $conn->error . "</div>";
                            }
                        } else {
                            echo "<div class='alert alert-danger'>Error updating partial payment: " . $conn->error . "</div>";
                        }
                    }
                }
                break;




            case 'advance_emi':
        if ($paymentType === 'advance_emi') {
            $status = 'Paid';
            $numOfEmis = intval($_POST['advanceEMICount']);
            $fetchSql = "SELECT * FROM emi_schedule WHERE LeadID = $leadId";
            $fetchResult = mysqli_query($conn, $fetchSql);
            $row = mysqli_fetch_assoc($fetchResult);
            $emiAmount = intval($row['EMIAmount']);
            $paidEMIs = $row['PaidEMIs'];
            $totalEMIs = $row['TotalEMIs']; // Assuming this is the total number of EMIs to be paid
            $partialPayment = $row['PartialPayment'];
            $oldNextPaymentDate = $row['NextPaymentDate'];
            
            $newPaidEMIs = $paidEMIs + $numOfEmis;
            
            // Calculate the new NextPaymentDate based on the old NextPaymentDate and number of EMIs paid in advance
            $nextPaymentDate = new DateTime($oldNextPaymentDate);
            $nextPaymentDate->modify("+$numOfEmis months");
            
            $updateSql = "UPDATE emi_schedule SET PaidEMIs = $newPaidEMIs, NextPaymentDate = '".$nextPaymentDate->format('Y-m-d')."', LastPaymentDate = NOW() WHERE LeadID = $leadId";
            if ($conn->query($updateSql) === TRUE) {
                // Check if PaidEMIs is equal to TotalEMIs and update LoanStatus if true
                if ($newPaidEMIs >= $totalEMIs) {
                    $updateLoanStatusSql = "UPDATE personalinformation SET LoanStatus = 'Closed' WHERE ID = $leadId";
                    if ($conn->query($updateLoanStatusSql) === TRUE) {
                        echo "<div class='alert alert-success'>Loan status updated to Closed.</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Error updating loan status: " . $conn->error . "</div>";
                    }
                }
                
                $success = true;
                for ($i = 0; $i < $numOfEmis; $i++) {
                    $insertSql = "INSERT INTO emi_payments (LeadID, PaymentDate, EMIAmount, PaymentType, OverdueDays, Status, PaymentReceiver, ReceiverDetails, bmapproval, superapproval, collector) 
                                  VALUES ('$leadId', NOW(), $emiAmount, '$paymentType', '0', '$status', '$paymentReceiver', '$receiverDetails', '0', '0', '$user')";
                    if ($conn->query($insertSql) !== TRUE) {
                        $success = false;
                        echo "<div class='alert alert-danger'>Error inserting advance payment record for EMI " . ($i + 1) . ": " . $conn->error . "</div>";
                        break;
                    }
                }
                if ($success) {
                    echo "<div class='alert alert-success'>Advance payment for $numOfEmis EMIs collected successfully.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Error updating advance payment: " . $conn->error . "</div>";
            }
        }
        break;



            case 'penalty':
                $penaltyRemarks=$_POST['penaltyRemarks'];
                $penaltyAmount = floatval($_POST['penaltyAmount']);
                $status='Paid';
                $oldpenalty="SELECT PenaltyAmount FROM emi_schedule WHERE LeadID='$leadId'";
                $result=$conn->query($oldpenalty);
                $row = mysqli_fetch_assoc($result);
                $newpenalty=$row['PenaltyAmount']+$penaltyAmount;
                $penaltysql="UPDATE emi_schedule SET PenaltyAmount= $newpenalty WHERE LeadID=$leadId";
                $conn->query($penaltysql);
                
                $updatepayments="INSERT INTO emi_payments (LeadID,PaymentDate,EMIAmount,PaymentType,Status,PaymentReceiver,ReceiverDetails,bmapproval,superapproval,remarks) VALUES('$leadId',NOW(),'$penaltyAmount','$penaltyAmount','$paymentType','$status','$paymentReceiver','$receiverDetails','0','0','$penaltyRemarks')";
                $conn->query($updatepayments);
                break;

            default:
                echo "Invalid payment type.";
                exit;
        }

    }
    ?>
    

    <div class="container" style="margin-top:8%;">
        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="<?php echo $redirectUrl;?>">Dashboard</a></li>
                                            <li class="breadcrumb-item">Welcome !</li>
                                            <li class="breadcrumb-item active">EMI Collections</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
        <h1>Collect Payment</h1>
        <div class="form-group">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by Customer Name or Lead ID">
    </div>
        <div class="table-responsive">
            <table class="table table-striped" id="tableContainer">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Lead ID</th>
                        <th>Loan Amount</th>
                        <th>EMI Amount</th>
                        <th>Partial Payment</th>
                        <th>Penalty</th>
                        <th>Customer Name</th>
                        <th>Collection Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT e.ID, e.LeadID, e.sanctionedAmount, e.EMIAmount, e.PartialPayment, e.PenaltyAmount, p.FullName, e.NextPaymentDate
FROM emi_schedule e
JOIN personalinformation p ON e.LeadID = p.ID
WHERE e.PaidEMIs < e.TotalEMIs
ORDER BY e.LeadID;
";
                    $result = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['ID'] . "</td>";
                            echo "<td>" . $row['LeadID'] . "</td>";
                            echo "<td>₹" . number_format($row['sanctionedAmount'], 2) . "</td>";
                            echo "<td>₹" . number_format($row['EMIAmount'], 2) . "</td>";
                            echo "<td>₹" . $row['PartialPayment'] . "</td>";
                            echo "<td>₹" . $row['PenaltyAmount'] . "</td>";
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
                        
                        <label for="paymentDate">Payment Date:</label>
                        <input type="date" id="paymentDate" name="paymentDate" value="<?php echo date('Y-m-d'); ?>" required>

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
                        <div class="form-group" id="penaltyRemarks" style="display:none;">
                            <label for="penaltyRemarks">Reason for Penalty:</label>
                            <input type="text" class="form-control" id="penaltyRemarks" name="penaltyRemarks">
                        </div>

                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
$(document).ready(function() {
    // Existing code...

    // Search functionality
    $('#searchInput').on('input', function() {
        var searchQuery = $(this).val();
        $.ajax({
            url: 'search_emis.php',
            method: 'POST',
            data: { searchQuery: searchQuery },
            success: function(response) {
                $('#tableContainer').html(response);
            },
            error: function(xhr, status, error) {
                alert('An error occurred: ' + error);
            }
        });
    });

    // Load initial table content
    $.ajax({
        url: 'search_emis.php',
        method: 'POST',
        success: function(response) {
            $('#tableContainer').html(response);
        },
        error: function(xhr, status, error) {
            alert('An error occurred: ' + error);
        }
    });

    // Prevent focus event from bubbling up
    $('#partialPaymentAmount').on('focusin', function(event) {
        event.stopPropagation();
    });
});
</script>

    
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
            $('#penaltyRemarks').show();
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
                location.reload();
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