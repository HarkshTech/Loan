<?php
include 'config.php';

$leadID = $_GET['leadID'];

$sql = "SELECT * FROM Recovery_Details WHERE LeadID = $leadID";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Row exists
    echo 'exists';
} else {
    // Row does not exist
    echo 'not_exists';
}

$conn->close();
?>
