<?php
include 'config.php';

$leadID = $_POST['leadID'];

$sql = "UPDATE Recovery_Details SET VisitNeeded = 1 WHERE LeadID = $leadID";

if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
?>
