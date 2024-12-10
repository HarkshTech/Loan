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
$username = $_SESSION['username'];
$assignedto = $_SESSION['username'];
$loggedInUser = $_SESSION['username'];

// Function to fetch data based on search query
function fetch_data($conn, $assignedto, $loggedInUser, $search_query = '') {
    $search_condition = !empty($search_query) ? "AND (ID LIKE '%$search_query%' OR FullName LIKE '%$search_query%' OR LoanAmount LIKE '%$search_query%' OR LoanPurpose LIKE '%$search_query%')" : '';
    $query = "SELECT ID, StepReached, FullName, LoanAmount, LoanPurpose FROM personalinformation 
              WHERE (assignedto='$assignedto' OR generatedby = 'Self($loggedInUser)') 
              AND LeadStatus='New Lead' $search_condition";
    return mysqli_query($conn, $query);
}

// Handle AJAX request
if (isset($_POST['ajax']) && $_POST['ajax'] == 1) {
    $search_query = $_POST['search_query'];
    $result = fetch_data($conn, $assignedto, $loggedInUser, $search_query);
    $response = '';

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $response .= "<tr>
                            <td>{$row['ID']}</td>
                            <td>{$row['StepReached']}</td>
                            <td>{$row['FullName']}</td>
                            <td>{$row['LoanAmount']}</td>
                            <td>{$row['LoanPurpose']}</td>
                            <td><a href='view.php?id={$row['ID']}'>View</a></td>
                          </tr>";
        }
    } else {
        $response .= "<tr><td colspan='6'>No data found.</td></tr>";
    }
    echo $response;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Information Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            color: #333;
            font-size: 24px;
            font-weight: 700;
        }

        .search-container {
            margin: 20px;
            width: 90%;
            max-width: 600px;
            display: flex;
            justify-content: center;
        }

        .search-container input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .table-container {
            width: 90%;
            max-width: 1000px;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow-x: auto; /* Ensure horizontal scrolling */
        }

        table {
            width: 100%;
            border-collapse: collapse;
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

        button {
            background-color: #1C84EE;
            color: white;
            border: none;
            padding: 8px 16px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #155a9b;
        }

        @media screen and (max-width: 768px) {
            th, td {
                font-size: 14px;
                padding: 10px;
            }

            button {
                font-size: 14px;
                padding: 6px 12px;
            }
        }

        @media screen and (max-width: 480px) {
            th, td {
                font-size: 12px;
                padding: 8px;
            }

            button {
                font-size: 12px;
                padding: 4px 8px;
            }

            .search-container input {
                padding: 8px;
                font-size: 14px;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function searchTable() {
            var search_query = $("#search").val();
            $.post("<?php echo $_SERVER['PHP_SELF']; ?>", {
                ajax: 1,
                search_query: search_query
            }, function(data) {
                $("#table-body").html(data);
            });
        }

        function viewDetails(id) {
            // Implement the view functionality as needed
            alert("View details for ID: " + id);
        }

        $(document).ready(function() {
            $("#search").on("input", function() {
                searchTable();
            });
        });
    </script>
</head>
<body>
    <h1>Personal Information Details</h1>
    <div class="search-container">
        <input type="text" id="search" placeholder="Search...">
    </div>
    <div class="table-container">
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
            <tbody id="table-body">
                <?php
                $result = fetch_data($conn, $assignedto, $loggedInUser);
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                                <td>{$row['ID']}</td>
                                <td>{$row['StepReached']}</td>
                                <td>{$row['FullName']}</td>
                                <td>{$row['LoanAmount']}</td>
                                <td>{$row['LoanPurpose']}</td>
                                <td><a href='view.php?id={$row['ID']}'>View</a></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No data found.</td></tr>";
                }

                // Close the database connection
                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
