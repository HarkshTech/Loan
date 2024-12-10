<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

include 'config.php'; // Include database configuration

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $leadID = $_POST['leadID'];
    $leadStatus = $_POST['leadStatus'];

    // Update lead status in the database
    $updateSql = "UPDATE personalinformation SET LeadStatus='$leadStatus' WHERE ID='$leadID'";
    if ($conn->query($updateSql) === TRUE) {
        if ($leadStatus == 'Hot Lead') {
            $updateHotLead = $conn->query("UPDATE personalinformation SET IsHotLead=TRUE,LoanStatus='Processing' WHERE ID='$leadID'");
        } else {
            $updateHotLead = $conn->query("UPDATE personalinformation SET IsHotLead=FALSE,LoanStatus='Not Interested' WHERE ID='$leadID'");
        }
        // If update successful, redirect back to the leads management page
        if($_SESSION['role']==='sales'){
        header("Location: hotleadsales.php");
        }
        elseif($_SESSION['role']==='branchmanager'){
        header("Location: bmleads2.php");
        }
        exit();
    } else {
        // If update fails, display an error message
        echo "Error updating lead status: " . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>
