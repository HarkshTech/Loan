<?php
session_start();
include 'config.php';

$query = isset($_POST['query']) ? $_POST['query'] : '';
$loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Escape special characters
$searchQuery = $conn->real_escape_string(trim($query));

// Base SQL query
$sql = "SELECT ID, FullName, Email, PhoneNumber, generatedby 
        FROM personalinformation 
        WHERE (assignedto = '$loggedInUser' 
        OR generatedby = 'Self(" . $loggedInUser . ")') 
        AND LeadStatus='Hot Lead'";

// Modify SQL if search query exists
if (!empty($searchQuery)) {
    if (is_numeric($searchQuery)) {
        // If numeric, search by ID only
        $sql .= " AND ID = '$searchQuery'";
    } else {
        // If non-numeric, search by FullName
        $sql .= " AND FullName LIKE '%$searchQuery%'";
    }
}

$result = $conn->query($sql);

// Output the results as table rows
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["ID"] . "</td>";
        echo "<td>" . $row["FullName"] . "</td>";
        echo "<td>" . $row["Email"] . "</td>";
        echo "<td>" . $row["PhoneNumber"] . "</td>";
        echo "<td>Hot Lead</td>";
        echo "<td>" . $row["generatedby"] . "</td>";
        echo '<td><a href="viewdocumentssales.php?id=' . $row["ID"] . '" class="btn btn-primary btn-sm">View and Upload Documents</a></td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No hot leads found</td></tr>";
}

$conn->close();
?>
