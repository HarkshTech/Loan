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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Recieved EMI's</title>
    <style>
        h1 {
            text-align: center;
            margin-top: 20px;
            color: #1C84EE;
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
    </style>
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- plugin css -->
    <link href="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet"
        type="text/css" />

    <!-- preloader css -->
    <link rel="stylesheet" href="assets/css/preloader.min.css" type="text/css" />

    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="dashboardapproved_loans.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Total EMI's Recieved Today</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1>EMI's Recieved Today</h1>
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Lead ID</th>
                    <th>Payment Date</th>
                    <th>EMI Amount</th>
                    <th>Payment Type</th>
                    <th>Payment Mode</th>
                    <th>Payment Receiver</th>
                    <th>Receiver Details</th>
                    <th>Approval Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all columns from the emi_payments table for today's payments
                $query_all_details = "SELECT * FROM emi_payments WHERE PaymentDate = CURDATE();";
                $result_all_details = mysqli_query($conn, $query_all_details);

                // Check if query execution was successful
                if ($result_all_details) {
                    // Fetch all rows from the result set
                    while ($row = mysqli_fetch_assoc($result_all_details)) {
                        // Determine approval status
                        if ($row['bmapproval'] == '1' && $row['superapproval'] == '1') {
                                $approval_status = 'Accepted';
                            } elseif ($row['bmapproval'] == '2' || $row['superapproval'] == '2') {
                                $approval_status = 'Rejected';
                            } else {
                                $approval_status = 'Pending';
                        }
                        // Display table rows
                        ?>
                        <tr>
                            <td><?php echo $row['PaymentID']; ?></td>
                            <td><?php echo $row['LeadID']; ?></td>
                            <td><?php echo $row['PaymentDate']; ?></td>
                            <td><?php echo $row['EMIAmount']; ?></td>
                            <td><?php echo $row['PaymentType']; ?></td>
                            <td><?php echo $row['PaymentMode']; ?></td>
                            <td><?php echo $row['PaymentReceiver']; ?></td>
                            <td><?php echo $row['ReceiverDetails']; ?></td>
                            <td><?php echo $approval_status; ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    // Handle error if query execution failed
                    echo "<tr><td colspan='8'>No data found.</td></tr>";
                }

                // Close the database connection
                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
