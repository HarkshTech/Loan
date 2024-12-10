<?php
session_start(); // Start the session

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database configuration file
include 'config.php'; // Ensure this file sets up $conn properly

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Determine the assignedto value based on the logged-in user
$assignedto = $_SESSION['username'];

// Fetch all columns from the emi_schedule table
$query_all_details = "
    SELECT 
    emi_schedule.LeadID,
    emi_schedule.sanctionedAmount,
    emi_schedule.TotalEMIs,
    COUNT(emi_payments.paymentID) AS PaidEMIs
FROM 
    emi_schedule
LEFT JOIN 
    emi_payments ON emi_schedule.LeadID = emi_payments.LeadID 
    AND emi_payments.bmapproval = '1' 
    AND emi_payments.superapproval = '1'
GROUP BY 
    emi_schedule.LeadID, emi_schedule.sanctionedAmount, emi_schedule.TotalEMIs
HAVING 
    emi_schedule.sanctionedAmount <> 0
    OR emi_schedule.TotalEMIs <> 0
    OR COUNT(emi_payments.paymentID) <> 0
";

$result_all_details = mysqli_query($conn, $query_all_details);

// Handle query error
if (!$result_all_details) {
    die('Query Error: ' . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Information Details</title>
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
    <link href="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="assets/css/preloader.min.css" type="text/css" />
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="container" style="margin-top: 100px; width: 80%;">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="dashboardapproved_loans.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Total Outstanding and Sanctions</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1>Total Loan Amount Details</h1>
        <table>
            <thead>
                <tr>
                    <th>LeadID</th>
                    <th>Sanctioned Amount</th>
                    <th>Outstanding Amount</th>
                    <th>Total EMI's</th>
                    <th>Paid EMI's</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all rows from the result set
                while ($row = mysqli_fetch_assoc($result_all_details)) {
                    // Calculate the outstanding amount
                    $sanctionedAmount = $row['sanctionedAmount'];
                    $totalEMIs = $row['TotalEMIs'];
                    $paidEMIs = $row['PaidEMIs'];

                    // Check to prevent division by zero
                    if ($totalEMIs > 0) {
                        $outstandingAmount = $sanctionedAmount - (($sanctionedAmount / $totalEMIs) * $paidEMIs);
                    } else {
                        $outstandingAmount = $sanctionedAmount; // If no EMIs, the outstanding amount is the sanctioned amount
                    }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['LeadID']); ?></td>
                        <td><?php echo htmlspecialchars($sanctionedAmount); ?></td>
                        <td><?php echo htmlspecialchars($outstandingAmount); ?></td>
                        <td><?php echo htmlspecialchars($totalEMIs); ?></td>
                        <td><?php echo htmlspecialchars($paidEMIs); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
