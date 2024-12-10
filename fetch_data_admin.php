<?php
// fetch_data.php

// Include the database connection file
include 'config.php';

// Get the search query from the AJAX request
$query = isset($_POST['query']) ? $_POST['query'] : '';

// Prepare the SQL query with filtering
$sql = "SELECT ID, FullName, StepReached, LoanAmount, LoanPurpose 
        FROM personalinformation 
        WHERE ID LIKE ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%$query%";
$stmt->bind_param('s', $searchTerm);

// Execute the query
$stmt->execute();
$result = $stmt->get_result();

// Fetch the results and generate the table rows
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['ID'] . '</td>';
        echo '<td>' . $row['FullName'] . '</td>';
        echo '<td>' . $row['StepReached'] . '</td>';
        echo '<td>' . $row['LoanAmount'] . '</td>';
        echo '<td>' . $row['LoanPurpose'] . '</td>';
        echo '<td><a href="view.php?id=' . $row['ID'] . '">View</a></td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="6">No data found.</td></tr>';
}

// Close the database connection
$stmt->close();
$conn->close();
?>
