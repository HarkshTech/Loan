<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// Determine if the query is an ID or name
if (is_numeric($query)) {
    // Search by exact ID
    $sql = "SELECT ID, FullName, Email, PhoneNumber FROM personalinformation WHERE LeadStatus='Hot Lead' AND ID = '$query' AND StepReached<>'Disbursed'";
} else {
    // Search by FullName
    $sql = "SELECT ID, FullName, Email, PhoneNumber FROM personalinformation WHERE LeadStatus='Hot Lead' AND FullName LIKE '%$query%' AND StepReached<>'Disbursed'";
}

// Apply role-based filters
// if ($role == 'admin') {
//     // No additional filtering needed for admin
// } elseif ($role == 'branchmanager') {
//     $sql .= " AND (assignedto = '$loggedInUser' OR generatedby = 'Self($loggedInUser)')";
// }

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["ID"] . "</td>";
        echo "<td>" . $row["FullName"] . "</td>";
        echo "<td>" . $row["Email"] . "</td>";
        echo "<td>" . $row["PhoneNumber"] . "</td>";
        echo "<td>Hot Lead</td>";
        echo '<td><a href="document_collection.php?id=' . $row["ID"] . '" class="btn btn-primary btn-sm">Document Collection</a></td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No hot leads found</td></tr>";
}

$conn->close();
?>
