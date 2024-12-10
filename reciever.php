<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leadId = $_POST['leadId'];
    $paymentType = $_POST['paymentType'];
    $paymentReceiver = $_POST['paymentReceiver'];
    $receiverDetails = $_POST['receiverDetails'];

    if ($paymentType == 'collect_payment') {
        $sql = "INSERT INTO payments (LeadID, PaymentReceiver, ReceiverDetails, PaymentDate, EMIAmount, OverdueDays, Status)
                VALUES ('$leadId', '$paymentReceiver', '$receiverDetails', NOW(), 0, 0, 'Collected')";
    } elseif ($paymentType == 'partial_payment') {
        $partialPaymentAmount = $_POST['partialPaymentAmount'];
        $sql = "INSERT INTO payments (LeadID, PaymentReceiver, ReceiverDetails, PaymentDate, EMIAmount, OverdueDays, Status)
                VALUES ('$leadId', '$paymentReceiver', '$receiverDetails', NOW(), '$partialPaymentAmount', 0, 'Partial')";
    } elseif ($paymentType == 'advance_emi') {
        $advanceEMICount = $_POST['advanceEMICount'];
        $sql = "INSERT INTO payments (LeadID, PaymentReceiver, ReceiverDetails, PaymentDate, EMIAmount, OverdueDays, Status)
                VALUES ('$leadId', '$paymentReceiver', '$receiverDetails', NOW(), 0, '$advanceEMICount', 'Advance EMI')";
    } elseif ($paymentType == 'penalty') {
        $penaltyAmount = $_POST['penaltyAmount'];
        $sql = "INSERT INTO payments (LeadID, PaymentReceiver, ReceiverDetails, PaymentDate, EMIAmount, OverdueDays, Status)
                VALUES ('$leadId', '$paymentReceiver', '$receiverDetails', NOW(), '$penaltyAmount', 0, 'Penalty')";
    }

    if (mysqli_query($conn, $sql)) {
        echo "Payment recorded successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

    mysqli_close($conn);
} else {
    echo "Invalid request method.";
}
?>
