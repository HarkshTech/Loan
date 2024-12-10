<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leadId = $_POST['leadId'];
    $paymentReceiver = $_POST['paymentReceiver'];
    $agentName = isset($_POST['agentName']) ? $_POST['agentName'] : '';
    $branchName = isset($_POST['branchName']) ? $_POST['branchName'] : '';
    $paymentType = $_POST['paymentType'];
    $paymentAmount = isset($_POST['paymentAmount']) ? $_POST['paymentAmount'] : 0;
    $penaltyAmount = isset($_POST['penaltyAmount']) ? $_POST['penaltyAmount'] : 0;
    $penaltyReason = isset($_POST['penaltyReason']) ? $_POST['penaltyReason'] : '';

    // Insert receiver details
    $insertReceiverSql = "INSERT INTO payment_receivers (LeadID, ReceiverType, AgentName, BranchName) VALUES ('$leadId', '$paymentReceiver', '$agentName', '$branchName')";
    if ($conn->query($insertReceiverSql) !== TRUE) {
        echo "<div class='alert alert-danger'>Error inserting receiver details: " . $conn->error . "</div>";
    }

    // Existing payment processing code based on $paymentType...
    if ($paymentType == 'collect_payment') {
        
        // Process full EMI payment
        $updateSql = "UPDATE emi_schedule SET PaidEMIs = PaidEMIs + 1, PartialPayment = 0, PenaltyAmount = 0 WHERE LeadID = $leadId AND PaidEMIs < TotalEMIs";
                            if ($conn->query($updateSql) === TRUE) {
                                $fetchSql = "SELECT EMIAmount, overdue_days FROM emi_schedule WHERE LeadID = $leadId";
                                $fetchResult = mysqli_query($conn, $fetchSql);
                                $row = mysqli_fetch_assoc($fetchResult);
                                $emiAmount = $row['EMIAmount'];
                                $overdueDays = $row['overdue_days'];
                                $status = 'Paid';

                                $insertSql = "INSERT INTO emi_payments (LeadID, PaymentDate, EMIAmount, OverdueDays, Status) VALUES ('$leadId', NOW(), '$emiAmount', '$overdueDays', '$status')";
                                if ($conn->query($insertSql) === TRUE) {
                                    $updateDatesSql = "UPDATE emi_schedule SET LastPaymentDate = NOW(), NextPaymentDate = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE LeadID = $leadId";
                                    if ($conn->query($updateDatesSql) === TRUE) {
                                        echo "<div class='alert alert-success'>Payment collected successfully.</div>";
                                    } else {
                                        echo "<div class='alert alert-danger'>Error updating payment dates: " . $conn->error . "</div>";
                                    }
                                } else {
                                    echo "<div class='alert alert-danger'>Error collecting payment: " . $conn->error . "</div>";
                                }
                            } else {
                                echo "<div class='alert alert-danger'>Error updating EMI schedule: " . $conn->error . "</div>";
                            }
    } elseif ($paymentType == 'partial_payment') {
        // Process partial payment
        $fetchSql = "SELECT EMIAmount, PartialPayment, NextPaymentDate FROM emi_schedule WHERE LeadID = $leadId";
                            $fetchResult = mysqli_query($conn, $fetchSql);
                            $row = mysqli_fetch_assoc($fetchResult);
                            $emiAmount = $row['EMIAmount'];
                            $partialPayment = $row['PartialPayment'];
                            $nextPaymentDate = $row['NextPaymentDate'];

                            $newPartialPayment = $partialPayment + $paymentAmount;
                            if ($newPartialPayment >= $emiAmount) {
                                $remainingAmount = $newPartialPayment - $emiAmount;
                                $updateSql = "UPDATE emi_schedule SET PaidEMIs = PaidEMIs + 1, PartialPayment = $remainingAmount, PenaltyAmount = 0 WHERE LeadID = $leadId";
                                if ($conn->query($updateSql) === TRUE) {
                                    $status = 'Paid';
                                    $insertSql = "INSERT INTO emi_payments (LeadID, PaymentDate, EMIAmount, OverdueDays, Status) VALUES ('$leadId', NOW(), '$emiAmount', 0, '$status')";
                                    if ($conn->query($insertSql) === TRUE) {
                                        // Update NextPaymentDate based on your formula
                                        $nextPaymentDate = date('Y-m-d', strtotime("+30 days")); // Example: Add 30 days
                                        $updateDatesSql = "UPDATE emi_schedule SET LastPaymentDate = NOW(), NextPaymentDate = '$nextPaymentDate' WHERE LeadID = $leadId";
                                        if ($conn->query($updateDatesSql) === TRUE) {
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
                            } else {
                                $updateSql = "UPDATE emi_schedule SET PartialPayment = $newPartialPayment WHERE LeadID = $leadId";
                                if ($conn->query($updateSql) === TRUE) {
                                    echo "<div class='alert alert-success'>Partial payment collected successfully.</div>";
                                } else {
                                    echo "<div class='alert alert-danger'>Error updating partial payment: " . $conn->error . "</div>";
                                }
                            }
                       
    } elseif ($paymentType == 'advance_emis') {
        // Process advance EMI payment
        $numOfEmis = $_POST['numOfEmis'];
                            $fetchSql = "SELECT EMIAmount, PaidEMIs, PartialPayment, NextPaymentDate FROM emi_schedule WHERE LeadID = $leadId";
                            $fetchResult = mysqli_query($conn, $fetchSql);
                            $row = mysqli_fetch_assoc($fetchResult);
                            $emiAmount = $row['EMIAmount'];
                            $paidEMIs = $row['PaidEMIs'];
                            $partialPayment = $row['PartialPayment'];
                            $oldNextPaymentDate = $row['NextPaymentDate'];
                        
                            $newPaidEMIs = $paidEMIs + $numOfEmis;
                            $nextPaymentDate = date('Y-m-d', strtotime("$oldNextPaymentDate + $numOfEmis months"));
                        
                            $updateSql = "UPDATE emi_schedule SET PaidEMIs = $newPaidEMIs, NextPaymentDate = '$nextPaymentDate', LastPaymentDate = NOW() WHERE LeadID = $leadId";
                            if ($conn->query($updateSql) === TRUE) {
                                echo "<div class='alert alert-success'>Advance payment for $numOfEmis EMIs collected successfully.</div>";
                            } else {
                                echo "<div class='alert alert-danger'>Error updating advance payment: " . $conn->error . "</div>";
                            }
    } elseif ($paymentType == 'penalty') {
        // Process penalty payment
        if ($penaltyAmount > 0) {
                            $penaltySql = "INSERT INTO emi_penalties (LeadID, PenaltyAmount, Reason) VALUES ('$leadId', '$penaltyAmount', '$penaltyReason')";
                            if ($conn->query($penaltySql) !== TRUE) {
                                echo "<div class='alert alert-danger'>Error inserting penalty: " . $conn->error . "</div>";
                            }
                        }
    }

    mysqli_close($conn);
    header("Location: collect_payment.php"); // Redirect back to the form page
}
?>
