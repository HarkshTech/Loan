<?php
session_start(); // Start the session

include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Determine the assignedto value based on the logged-in user
$loggedInUser = $_SESSION['username'];
$assignedto = $_SESSION['username'];

// Fetch personal information details based on assignedto
$query = "SELECT ID, StepReached, FullName, LoanAmount, LoanPurpose FROM personalinformation 
          WHERE (assignedto='$assignedto' OR generatedby = 'Self($loggedInUser)') 
          AND LeadStatus='Rejection'";
$result = mysqli_query($conn, $query);
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
        .search-bar {
            margin: 20px;
            text-align: center;
        }
        .search-bar input {
            padding: 10px;
            width: 80%;
            max-width: 600px;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            th, td {
                display: table-cell;
                padding: 8px;
                text-align: left;
            }
            tr {
                display: table-row;
            }
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#searchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#dataTable tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</head>
<body>
    <h1>Personal Information Details</h1>
    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search...">
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Step Reached</th>
                <th>Full Name</th>
                <th>Loan Amount</th>
                <th>Loan Purpose</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="dataTable">
            <?php
            // Check if query execution was successful
            if ($result) {
                // Fetch all rows from the result set
                while ($row = mysqli_fetch_assoc($result)) {
                    // Display table rows
                    ?>

                    <tr>
                        <td><?php echo $row['ID']; ?></td>
                        <td><?php echo $row['StepReached']; ?></td>
                        <td><?php echo $row['FullName']; ?></td>
                        <td><?php echo $row['LoanAmount']; ?></td>
                        <td><?php echo $row['LoanPurpose']; ?></td>
                        <td><a href="view.php?id=<?php echo $row['ID']; ?>">View</a></td>
                    </tr>

                    <?php
                }
            } else {
                // Handle error if query execution failed
                echo "<tr><td colspan='6'>No data found.</td></tr>";
            }

            // Close the database connection
            mysqli_close($conn);
            ?>
        </tbody>
    </table>
</body>
</html>
