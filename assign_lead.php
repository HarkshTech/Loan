<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leadID = $_POST['leadID'];
    $assignedTo = $_POST['assignedTo'];

    // Update AssignedTo in the database
    $updateSql = "UPDATE personalinformation SET assignedto='$assignedTo' WHERE ID=$leadID";
    if ($conn->query($updateSql) === TRUE) {
        echo "Assigned To updated successfully";
    } else {
        echo "Error updating Assigned To: " . $conn->error;
    }

    $conn->close();
    header('Location: lead.php'); // Redirect back to the leads management page
    exit();
}
?>
