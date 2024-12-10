<?php
include 'config.php';

$leadID = $_POST['leadID'];

$sql = "INSERT INTO Recovery_Details (LeadID, VisitNeeded) VALUES ($leadID, 1)";

if ($conn->query($sql) === TRUE) {
    echo "New record inserted successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
