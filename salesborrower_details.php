<?php
session_start(); // Start the session
$loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;

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

// Function to fetch data based on search query
function fetch_data($conn, $assignedto, $loggedInUser, $search_query = '') {
    $search_condition = !empty($search_query) ? "AND (ID LIKE '%$search_query%' OR FullName LIKE '%$search_query%' OR LoanAmount LIKE '%$search_query%' OR LoanPurpose LIKE '%$search_query%')" : '';
    $query = "SELECT ID, FullName, LoanAmount, LoanPurpose, StepReached 
              FROM personalinformation 
              WHERE (assignedto='$assignedto' OR generatedby = 'Self($loggedInUser)') $search_condition";
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
            display: flex;
            justify-content: center;
        }

        .search-container input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table-container {
            width: 90%;
            margin: 20px 0;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow-x: auto; /* Enable horizontal scrolling */
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 16px;
            text-align: left;
            color: #333;
            white-space: nowrap; /* Prevent text wrapping */
        }

        th {
            background-color: #1C84EE;
            color: #fff;
            font-weight: 500;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e1f5fe;
        }

        td a {
            text-decoration: none;
            color: #1C84EE;
            font-weight: 500;
        }

        td a:hover {
            text-decoration: underline;
        }

        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .button-container a {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #1C84EE;
            color: #fff;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease;
        }

        .button-container a:hover {
            background-color: #155bb5;
        }

        @media (max-width: 768px) {
            .table-container {
                width: 100%;
                margin: 10px;
            }

            th, td {
                padding: 12px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            th, td {
                font-size: 12px;
                padding: 10px;
            }

            .search-container input {
                font-size: 14px;
                padding: 8px;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#search-input').on('keyup', function() {
                var search_query = $(this).val();
                $.ajax({
                    type: 'POST',
                    url: 'salesborrower_details.php',
                    data: {ajax: 1, search_query: search_query},
                    success: function(response) {
                        $('tbody').html(response);
                    }
                });
            });
        });
    </script>
</head>
<body>
    <h1>Personal Information Details</h1>
    <div class="search-container">
        <input type="text" id="search-input" placeholder="Search by name, amount, purpose...">
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Lead Stage</th>
                    <th>Full Name</th>
                    <th>Loan Amount</th>
                    <th>Loan Purpose</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch initial data to display
                $result_all_details = fetch_data($conn, $assignedto, $loggedInUser);

                if ($result_all_details) {
                    while ($row = mysqli_fetch_assoc($result_all_details)) {
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

                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
