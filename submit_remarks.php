<?php
// Include database connection
include 'config.php';

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if leadID and remark are set
    if (isset($_POST['leadID']) && isset($_POST['remark'])) {
        // Sanitize input data
        $leadID = mysqli_real_escape_string($conn, $_POST['leadID']);
        $remark = mysqli_real_escape_string($conn, $_POST['remark']);
        $recoveryDate = date('Y-m-d'); // Get current date as recovery date
        
        // Insert data into Recovery_Details table
        $sql = "INSERT INTO Recovery_Details (LeadID, RecoveryDate, Remarks) VALUES ('$leadID', '$recoveryDate', '$remark')";
        if ($conn->query($sql) === TRUE) {
            echo "Remarks inserted successfully!";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Invalid data!";
    }
} else {
    echo "Access denied!";
}

// Close database connection
$conn->close();
?>
