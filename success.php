<?php
session_start();
if (!isset($_SESSION['username'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

$username=$_SESSION['username'];

$role = $_SESSION['role'];

include 'config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['leadID']) && isset($_POST['status'])) {
        $leadID = $_POST['leadID'];
        $statusData = $_POST['status'];
        $remarksData = isset($_POST['remarks']) ? $_POST['remarks'] : [];

        // Prepare the update query for documentcollection table
        $updateQuery = "UPDATE documentcollection SET ";
        $params = [];

        foreach ($statusData as $columnName => $status) {
            if (strpos($columnName, 'Document') !== false) {
                // Extract the document number from the column name (e.g., Document1 to 1)
                $documentNumber = substr($columnName, 8); // Assuming the column names are like Document1, Document2, etc.
                $statusColumn = 'Status' . $documentNumber;
                $remarksColumn = 'remarks' . $documentNumber;

                // Escape input to prevent SQL injection
                $escapedStatus = $conn->real_escape_string($status);
                $params[] = "`$statusColumn` = '$escapedStatus'";

                // Add remarks to the query if the status is "Rejected" and remarks are provided
                if ($status === 'Rejected' && isset($remarksData[$columnName])) {
                    $remarks = $remarksData[$columnName];
                    $escapedRemarks = $conn->real_escape_string($remarks);
                    $params[] = "`$remarksColumn` = '$escapedRemarks'";
                } else {
                    $params[] = "`$remarksColumn` = NULL";
                }
            }
        }

        if (!empty($params)) {
            $updateQuery .= implode(", ", $params) . " WHERE LeadID = $leadID";

            if ($conn->query($updateQuery) === TRUE) {
                // Check if all statuses are updated to something other than 'Pending'
                $allDone = true;
                foreach ($statusData as $columnName => $status) {
                    if (strpos($columnName, 'Document') !== false && $status === 'Pending') {
                        $allDone = false;
                        break;
                    }
                }

                // Update personalinformation table if all statuses are updated
                if ($allDone) {
                $updatePersonQuery = "UPDATE personalinformation SET StepReached = 'Document Verification Done' WHERE ID = $leadID";
                if ($conn->query($updatePersonQuery) === TRUE) {
                    $username = $_SESSION['username'];
                    $notificationTitle = "Document Verification Status updated by $username for ID $leadID";
                    $notifications = [
                        ['nfor' => 'sales', 'nby' => 'System'],
                    ];
            
                    foreach ($notifications as $notification) {
                        $sql = "INSERT INTO notifications (title, message, nfor, nby, status, created_at)
                                VALUES ('$notificationTitle', 'Document verification status has been updated for ID $leadID, By $username, please check once.', '{$notification['nfor']}', '{$notification['nby']}', 'unread', NOW())";
                        $conn->query($sql);
                    }
            
                    if ($role === 'branchmanager') {
                        echo "<script>alert('Status updated successfully.'); window.location.href='digitalverificationsbm.php';</script>";
                    } elseif ($role === 'admin') {
                        echo "<script>alert('Status updated successfully.'); window.location.href='verify.php';</script>";
                    }
                } else {
                    echo "<script>alert('Error updating personal information: " . $conn->error . "'); window.location.href='verify_documents.php?id=$leadID';</script>";
                }
            } else {
                if ($role === 'branchmanager') {
                    echo "<script>alert('Status updated successfully.'); window.location.href='digitalverificationsbm.php';</script>";
                } elseif ($role === 'admin') {
                    echo "<script>alert('Status updated successfully.'); window.location.href='verify.php';</script>";
                }
            
                $username = $_SESSION['username'];
                $notificationTitle = "Document Verification Status updated by $username for ID $leadID";
                $notifications = [
                    ['nfor' => 'sales', 'nby' => 'System'],
                ];
            
                foreach ($notifications as $notification) {
                    $sql = "INSERT INTO notifications (title, message, nfor, nby, status, created_at)
                            VALUES ('$notificationTitle', 'Document verification status has been updated for ID $leadID, By $username, please check once.', '{$notification['nfor']}', '{$notification['nby']}', 'unread', NOW())";
                    $conn->query($sql);
                }
            }

            } else {
                echo "<script>alert('Error updating status: " . $conn->error . "'); window.location.href='verify_documents.php?id=$leadID';</script>";
            }
        } else {
            echo "<div class='alert alert-danger'>No status data provided.</div>";
            header("refresh:3;url=verify_documents.php?id=$leadID");
        }
        
        

        $conn->close();
    } else {
        echo "<div class='alert alert-danger'>Error: Missing POST data.</div>";
        header("refresh:3;url=verify_documents.php");
    }
} else {
    echo "<div class='alert alert-danger'>Error: Invalid request method.</div>";
    header("refresh:3;url=verify_documents.php");
}
?>
