<?php
session_start(); // Start the session
include 'config.php'; // Include database configuration

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Determine the assignedto value based on the logged-in user
$loggedInUser = $_SESSION['username'];
$username = $_SESSION['username'];

// Default query to fetch personal information details
$query = "SELECT ID, StepReached, FullName, LoanAmount, LoanPurpose FROM personalinformation WHERE (assignedto='$assignedto' OR generatedby = 'Self($loggedInUser)') AND LeadStatus='Cold Lead'";
$result = mysqli_query($conn, $query);

// Fetch search results
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $query = "SELECT ID, StepReached, FullName, LoanAmount, LoanPurpose FROM personalinformation WHERE (assignedto='$assignedto' OR generatedby = 'Self($loggedInUser)') AND LeadStatus='Cold Lead' AND (ID LIKE '%$search%' OR FullName LIKE '%$search%' OR LoanAmount LIKE '%$search%' OR LoanPurpose LIKE '%$search%')";
    $result = mysqli_query($conn, $query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Information Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
            color: #1C84EE;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: auto;
            overflow: hidden;
        }
        .search-bar {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }
        .search-bar input {
            width: 100%;
            max-width: 500px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
            color: #333;
        }
        th {
            background-color: #1C84EE;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .view-btn {
            color: #fff;
            background-color: #1C84EE;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .view-btn:hover {
            background-color: #155a9a;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Personal Information Details</h1>
        <div class="search-bar">
            <input type="text" id="search" placeholder="Search..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        </div>
        <div class="table-container">
            <table id="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>StepReached</th>
                        <th>FullName</th>
                        <th>LoanAmount</th>
                        <th>LoanPurpose</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Display data
                    if ($result) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>{$row['ID']}</td>";
                            echo "<td>{$row['StepReached']}</td>";
                            echo "<td>{$row['FullName']}</td>";
                            echo "<td>{$row['LoanAmount']}</td>";
                            echo "<td>{$row['LoanPurpose']}</td>";
                            echo "<td><a href='view.php?id={$row['ID']}' class='view-btn'>View</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No data found.</td></tr>";
                    }
                    mysqli_close($conn);
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        document.getElementById('search').addEventListener('input', function() {
            var query = this.value;
            window.location.search = 'search=' + encodeURIComponent(query);
        });
    </script>
</body>
</html>
