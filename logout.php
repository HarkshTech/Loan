<?php
// Start the session
session_start();

// Include the database configuration file
include 'config.php';
date_default_timezone_set('Asia/Kolkata');

// Set the time zone to Asia/Kolkata in MySQL
$conn->query("SET time_zone = '+05:30'");

// Check if the user is logged in
if (isset($_SESSION['username'])) {
    // Get the username from the session
    $username = $_SESSION['username'];

    // Update the logout time in the database for the current session
    $stmt = $conn->prepare("UPDATE user_session_logs SET logout_time = NOW() WHERE username = ? AND logout_time IS NULL ORDER BY login_time DESC LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Close the database connection
$conn->close();

// Redirect to the login page or any other appropriate page
header("Location: index.php");
exit();
?>
