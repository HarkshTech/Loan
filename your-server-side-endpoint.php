<?php
// Include database connection
include('config.php'); // Adjust the path as needed

// Define the number of records per page
$recordsPerPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 10;

// Get the current page number from the URL, default to page 1 if not provided
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get the search query from the URL
$searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%%'; // Wildcard for LIKE search

// Calculate the starting point (offset) for the query
$offset = ($currentPage - 1) * $recordsPerPage;

// Fetch the total number of records matching the search query
$totalResult = $conn->prepare("SELECT COUNT(*) AS total FROM VerificationForms WHERE column_name LIKE ?");
$totalResult->bind_param("s", $searchTerm);
$totalResult->execute();
$totalRow = $totalResult->get_result()->fetch_assoc();
$totalRecords = $totalRow['total'];

// Calculate the total number of pages
$totalPages = ceil($totalRecords / $recordsPerPage);

// Fetch the filtered, paginated records
$query = $conn->prepare("SELECT * FROM VerificationForms WHERE column_name LIKE ? LIMIT ? OFFSET ?");
$query->bind_param("sii", $searchTerm, $recordsPerPage, $offset);
$query->execute();
$result = $query->get_result();

// Output the results in table row format
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['column_name']) . "</td>";  // Replace 'column_name' with actual column names
    echo "<td>" . htmlspecialchars($row['other_column']) . "</td>"; // Replace 'other_column' with actual column names
    echo "<td>" . htmlspecialchars($row['another_column']) . "</td>"; // Replace as needed
    echo "</tr>";
}

// Return pagination details (could be useful for the frontend to create pagination controls)
echo json_encode([
    'currentPage' => $currentPage,
    'totalPages' => $totalPages,
    'totalRecords' => $totalRecords
]);

?>
