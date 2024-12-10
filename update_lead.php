<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['leadID']) && isset($_POST['leadStatus'])) {
        $leadID = $conn->real_escape_string($_POST['leadID']);
        $leadStatus = $conn->real_escape_string($_POST['leadStatus']);

        // Update lead status
        $updateStatus = $conn->query("UPDATE personalinformation SET LeadStatus='$leadStatus' WHERE ID='$leadID'");

        // Update IsHotLead status if marked as hot lead
        if ($leadStatus == 'Hot Lead') {
            $updateHotLead = $conn->query("UPDATE personalinformation SET IsHotLead=TRUE,LoanStatus='Processing' WHERE ID='$leadID'");
        } else {
            $updateHotLead = $conn->query("UPDATE personalinformation SET IsHotLead=FALSE,LoanStatus='Not Interested' WHERE ID='$leadID'");
        }

        if ($updateStatus && $updateHotLead) {
            header("Location: lead.php");
            exit(); // Ensure script execution stops after redirection
        } else {
            echo "Error updating lead status or hot lead flag: " . $conn->error;
        }
    } else {
        echo "Incomplete data provided!";
    }
}

$conn->close();
?>
