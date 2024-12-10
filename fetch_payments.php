<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'config.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$query = "
    SELECT ep.PaymentID, ep.LeadID, ep.PaymentDate, ep.EMIAmount, pi.FullName, es.PaidEMIs, es.TotalEMIs 
    FROM emi_payments ep
    JOIN personalinformation pi ON ep.LeadID = pi.ID
    JOIN emi_schedule es ON ep.LeadID = es.LeadID
    WHERE (ep.LeadID LIKE ? OR pi.FullName LIKE ?)
    AND ep.bmapproval = '1' AND ep.superapproval='1'
";

$params = ["%$search%", "%$search%"];
$types = "ss";

if ($start_date) {
    $query .= " AND ep.PaymentDate >= ?";
    $params[] = $start_date;
    $types .= "s";
}

if ($end_date) {
    $query .= " AND ep.PaymentDate <= ?";
    $params[] = $end_date;
    $types .= "s";
}

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);

$stmt->execute();
$result = $stmt->get_result();

$payments = [];
while ($row = $result->fetch_assoc()) {
    $row['PendingEMIs'] = $row['TotalEMIs'] - $row['PaidEMIs'];
    $payments[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($payments);
?>
