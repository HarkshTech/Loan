<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads Management</title>  
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
    include 'leftsidebranch.php'; // Include left sidebar
    include 'config.php'; // Include database configuration

  

    // Define the assigned salesperson based on the logged-in user
    $loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;

    // Define the assigned salesperson for each user
    // $salespersons = array(
    //     'sales1' => 'Abhi',
    //     'sales2' => 'Shikha',
    //     'sales3' => 'Nav',
    //     'sales4' => 'Manroop'
    // );

    ?>
    <div class="container" style="margin-left:90px;">
        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="branchmanager.php">Dashboard</a></li>
                                            <li class="breadcrumb-item">Welcome !</li>
                                            <li class="breadcrumb-item active">Leads</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                </div>
        <h1 class="mb-4">Leads Management</h1>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Occupation</th>
                        <th>Employer</th>
                        <th>Loan Amount</th>
                        <th>Loan Purpose</th>
                        <th>Lead Status</th>
                        <th>Lead Generated</th>
                        <!--<th>Assigned To</th>-->
                        <th class="action-column">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    // Check if the logged-in user is a salesperson
                    if (isset($_SESSION['username'])) {
                        $assignedSalesperson = isset($_SESSION['username']);

                        // Query the personalinformation table with the assigned salesperson condition
                        $sql = "SELECT * FROM personalinformation WHERE assignedto = '$assignedSalesperson' OR generatedby = 'Self(" . $loggedInUser . ")'";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // Output the data as before
                                echo "<tr>";
                                echo "<td>" . $row["ID"] . "</td>";
                                echo "<td>" . $row["FullName"] . "</td>";
                                echo "<td>" . $row["Email"] . "</td>";
                                echo "<td>" . $row["PhoneNumber"] . "</td>";
                                echo "<td>" . $row["Occupation"] . "</td>";
                                echo "<td>" . $row["Employer"] . "</td>";
                                echo "<td>" . $row["LoanAmount"] . "</td>";
                                echo "<td>" . $row["LoanPurpose"] . "</td>";
                                echo "<td>" . $row["LeadStatus"] . "</td>";
                                echo "<td>" . $row["generatedby"] . "</td>";
                                // echo "<td>" . $row["assignedto"] . "</td>";
                                echo '<td><form action="salesupdate_lead.php" method="POST" class="lead-status">
                                        <input type="hidden" name="leadID" value="' . $row["ID"] . '">
                                        <div class="form-group">
                                            <select class="form-control" name="leadStatus">
                                                <option value="Hot Lead">Hot Lead</option>
                                                <option value="Cold Lead">Cold Lead</option>
                                                <option value="Rejection">Rejection</option>
                                                <option value="New Lead">New Lead</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                    </form></td>';
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10'>No leads found</td></tr>";
                        }
                    } else {
                        // Handle the case where the logged-in user is not a salesperson
                        echo "<tr><td colspan='10'>You are not authorized to view this data</td></tr>";
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
