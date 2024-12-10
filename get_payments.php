<?php
include 'config.php';

// Fetch all payments
$sql = "SELECT * FROM emi_payments";
$result = $conn->query($sql);

$payments = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
}

echo json_encode($payments);

$conn->close();
?>