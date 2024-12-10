<?php
include 'config.php'; // Include your database configuration file

// Get the current date
$current_date = date('Y-m-d');

// Calculate the date two days ago
$date_threshold = date('Y-m-d', strtotime($current_date . ' -2 days'));

// Query to get leads with LeadStatus="New Lead" and date field older than 2 days
$query = "SELECT ID FROM personalinformation WHERE LeadStatus='New Lead' AND DATE(date) < '$date_threshold'";
$result = $conn->query($query);

// Check if there are any leads that match the criteria
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Prepare the data for the notifications table
        $lead_id = $row['ID'];
        $title = 'Lead Pending for Working: ' . $lead_id;
        $nfor = 'branchmanager';
        $message = 'Form Submission Done, No Further Working done!!';
        $nby = 'System';
        $status = 'unread';
        $created_at = date('Y-m-d H:i:s');

        // Check if a similar notification already exists
        $check_query = "SELECT * FROM notifications WHERE title='$title' AND nfor='$nfor' AND created_at >= '$date_threshold' AND status='unread'";
        $check_result = $conn->query($check_query);

        if ($check_result->num_rows == 0) {
            // Insert into notifications table if no similar notification exists
            $insert_query = "INSERT INTO notifications (title, nfor, nby, message, status, created_at) VALUES ('$title', '$nfor', '$nby', '$message', '$status', '$created_at')";
            if ($conn->query($insert_query) === TRUE) {
                // Optionally log success to a file
                error_log("Notification created successfully for lead ID: " . $lead_id . "\n", 3, "notification_log.txt");
            } else {
                // Log errors to a file
                error_log("Error: " . $insert_query . "\n" . $conn->error . "\n", 3, "notification_errors.txt");
            }
        } else {
            // Optionally log when a duplicate notification is detected
            error_log("Duplicate notification skipped for lead ID: " . $lead_id . "\n", 3, "notification_log.txt");
        }
    }
} else {
    // Optionally log when no pending leads are found
    error_log("No pending leads found that need notifications.\n", 3, "notification_log.txt");
}

// $conn->close();
?>
