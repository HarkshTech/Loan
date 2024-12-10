<?php
// Include config.php to establish database connection
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['leadID']) && isset($_POST['action'])) {
    $leadID = $_POST['leadID'];
    $action = $_POST['action'];

    if ($action === 'accept') {
        // Logic to update database for acceptance
        $sqlAccept = "UPDATE approval_information SET IsApproved = '1' WHERE LeadID = '$leadID'";
        if (mysqli_query($conn, $sqlAccept)) {
            echo "Loan accepted successfully";
        } else {
            echo "Error accepting loan: " . mysqli_error($conn);
        }
    } elseif ($action === 'reject') {
        // Logic to update database for rejection
        $sqlReject = "UPDATE approval_information SET IsApproved = '0' WHERE LeadID = '$leadID'";
        if (mysqli_query($conn, $sqlReject)) {
            echo "Loan rejected successfully";
        } else {
            echo "Error rejecting loan: " . mysqli_error($conn);
        }
    } else {
        echo "Invalid action";
    }
} else {
    echo "Invalid request";
}

// Close the database connection
mysqli_close($conn);
?>
