<?php
// Include your database connection file or define connection parameters here
include 'config.php'; // Adjust the path if necessary

// Check if the leadID is provided in the POST request
if(isset($_POST['leadID'])) {
    // Sanitize the input
    $leadID = mysqli_real_escape_string($conn, $_POST['leadID']);

    // Query to fetch the EMI amount for the given LeadID
    $sql = "SELECT EMIAmount FROM emi_schedule WHERE LeadID = '$leadID'";

    // Execute the query
    $result = mysqli_query($conn, $sql);

    // Check if query executed successfully
    if($result) {
        // Check if any rows are returned
        if(mysqli_num_rows($result) > 0) {
            // Fetch the EMI amount from the result set
            $row = mysqli_fetch_assoc($result);
            $emiAmount = $row['EMIAmount'];
            // Output the EMI amount
            echo $emiAmount;
        } else {
            // No matching lead found
            echo "Lead not found or EMI amount not available.";
        }
    } else {
        // Query execution failed
        echo "Error: " . mysqli_error($conn);
    }
} else {
    // LeadID not provided in the POST request
    echo "LeadID is missing in the request.";
}

// Close the database connection
mysqli_close($conn);
?>
