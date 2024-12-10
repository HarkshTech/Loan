<?php
session_start();
include 'config.php'; // Include database configuration

$loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$assignedSalesperson = $loggedInUser; // Assuming the salesperson is the logged-in user

$query = isset($_POST['query']) ? $_POST['query'] : '';

// Escape special characters in the search query
$searchQuery = $conn->real_escape_string($query);

// Construct SQL query based on the search input (numeric ID for exact match or alphabetic FullName)
if (is_numeric($searchQuery)) {
    // If the query is numeric, search by ID with an exact match
    $sql = "SELECT * FROM personalinformation 
            WHERE ID = '$searchQuery' 
            AND (assignedto = '$assignedSalesperson' OR generatedby = 'Self($loggedInUser)')";
} else {
    // If the query is alphabetic, search by FullName using a partial match
    $sql = "SELECT * FROM personalinformation 
            WHERE FullName LIKE '%$searchQuery%' 
            AND (assignedto = '$assignedSalesperson' OR generatedby = 'Self($loggedInUser)')";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["ID"] . "</td>";
        echo "<td>" . $row["FullName"] . "</td>";
        echo "<td>" . $row["Email"] . "</td>";
        echo "<td>" . $row["PhoneNumber"] . "</td>";
        echo "<td>" . $row["details1"] . "</td>";
        echo "<td>" . $row["employer1"] . "</td>";
        echo "<td>" . $row["LoanAmount"] . "</td>";
        echo "<td>" . $row["LoanPurpose"] . "</td>";
        echo "<td>" . $row["LeadStatus"] . "</td>";
        echo "<td>" . $row["generatedby"] . "</td>";
        echo '<td>
                <form action="salesupdate_lead.php" method="POST" class="lead-status">
                    <input type="hidden" name="leadID" value="' . $row["ID"] . '">
                    <div class="form-group">
                        <select class="form-control" name="leadStatus">
                            <option value="Hot Lead"' . ($row["LeadStatus"] == "Hot Lead" ? ' selected' : '') . '>Hot Lead</option>
                            <option value="Cold Lead"' . ($row["LeadStatus"] == "Cold Lead" ? ' selected' : '') . '>Cold Lead</option>
                            <option value="Rejection"' . ($row["LeadStatus"] == "Rejection" ? ' selected' : '') . '>Rejection</option>
                            <option value="New Lead"' . ($row["LeadStatus"] == "New Lead" ? ' selected' : '') . '>New Lead</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                </form>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='11'>No leads found</td></tr>";
}

$conn->close();
?>
