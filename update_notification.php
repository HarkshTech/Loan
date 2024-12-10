<?php
// Include your database connection file
include 'config.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $update_query = "UPDATE notifications SET status = 'read' WHERE id = $id";
    if (mysqli_query($conn, $update_query)) {
        echo 'success'; // Send success response back to AJAX call
    } else {
        echo 'error'; // Send error response back to AJAX call
    }
} else {
    echo 'invalid'; // Send invalid request response back to AJAX call
}
?>
