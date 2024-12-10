<?php
include 'config.php';

$query = isset($_GET['query']) ? $_GET['query'] : '';

// Escape the query to prevent SQL injection
$searchQuery = $conn->real_escape_string($query);

// Modify the SQL query to filter based on the search term
$sqlFetchUsers = "SELECT * FROM users WHERE username LIKE '%$searchQuery%' OR role LIKE '%$searchQuery%'";
$result = $conn->query($sqlFetchUsers);

if ($result->num_rows > 0) {
    while ($user = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "<td>" . htmlspecialchars($user['branchname']) . "</td>";
        echo "<td>" . htmlspecialchars($user['created_by']) . "</td>";
        echo "<td>" . date('F j, Y, g:i a', strtotime($user['creation_time'])) . "</td>";
        echo "<td>";
        echo "<a href='user_management.php?action=delete&id=" . $user['id'] . "' class='btn btn-sm btn-danger delete-btn' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>";
        if ($user['status'] == 'active') {
            echo "<a href='user_management.php?action=disable&id=" . $user['id'] . "' class='btn btn-sm btn-warning disable-btn'>Disable</a>";
        } else {
            echo "<a href='user_management.php?action=enable&id=" . $user['id'] . "' class='btn btn-sm btn-success enable-btn'>Enable</a>";
        }
        echo "<a href='javascript:void(0);' class='btn btn-sm btn-info forgot-btn' data-toggle='modal' data-target='#resetPasswordModal' data-id='" . $user['id'] . "'>Forgot Password</a>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7'>No records found</td></tr>";
}

$conn->close();
?>
