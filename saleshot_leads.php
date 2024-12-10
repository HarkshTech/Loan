<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hot Leads and Document Collection</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 900px;
            margin: 50px auto;
            margin-top: 8%;
        }

        div#sidebar-menu {
            position: fixed;
        }

        .table .thead-dark th {
            color: #fff;
            background-color: #000000;
            border-color: #454d55;
        }

        .lead-status {
            max-width: 150px;
        }

        .action-column {
            width: 180px;
        }

        /* Adjust width of the select element */
        .action-column select {
            width: 130px;
        }

        @media (max-width: 768px) {
            .container {
                margin-top: 20px;
            }

            .container table {
                font-size: 14px;
            }

            .container th,
            .container td {
                padding: 8px;
            }

            .container .action-column {
                width: 150px;
            }

            .container .action-column select {
                width: 110px;
            }
        }
    </style>
</head>

<body>
    <?php
    session_start(); // Start the session
    include 'leftbarsales.php'; // Include left sidebar
    include 'config.php'; // Include database configuration

    // Define the assigned salesperson based on the logged-in user
    $loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;

    // Define the assigned salesperson for each user
    $salespersons = array(
        'sales1' => 'Abhi',
        'sales2' => 'Shikha',
        'sales3' => 'Nav',
        'sales4' => 'Manroop'
    );

    ?>
    <div class="container" style="margin-left:90px;">
        <h1 class="mb-4">Hot Leads and Document Collection</h1>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Lead Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    // Check if the logged-in user is a salesperson
                    if ($loggedInUser && array_key_exists($loggedInUser, $salespersons)) {
                        $assignedSalesperson = $salespersons[$loggedInUser];

                        // Query the personalinformation table with the assigned salesperson condition
                        $sql = "SELECT ID, FullName, Email, PhoneNumber FROM personalinformation WHERE assignedto = '$assignedSalesperson'";
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
                    } else {
                        // Handle the case where the logged-in user is not a salesperson
                        echo "<tr><td colspan='6'>You are not authorized to view this data</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
