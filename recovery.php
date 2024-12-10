<?php
session_start();
$username = $_SESSION['username'];
$role = $_SESSION['role'];

if ($role === 'admin') {
    include 'leftside.php';
} elseif ($role === 'accounts') {
    include 'leftbaraccounts.php';
} elseif ($role === 'branchmanager') {
    include 'leftsidebranch.php';
}

switch ($role) {
    case 'admin':
        $redirecturl = 'dashboard.php';
        break;
    case 'branchmanager':
        $redirecturl = 'branchmanager.php';
        break;
    case 'accounts':
        $redirecturl = 'dashboardapproved_loans.php';
        break;
}

include 'config.php';

date_default_timezone_set('Asia/Kolkata');

// Set the time zone to Asia/Kolkata in MySQL
$conn->query("SET time_zone = '+05:30'");

// Update overdue days in emi_schedule table
// $sqlUpdateOverdue = "UPDATE emi_schedule SET overdue_days = DATEDIFF(NOW(), NextPaymentDate)";
// $conn->query($sqlUpdateOverdue);

// Fetch data from emi_schedule table where overdue days > 5
$sqlFetchFailedEMI = "SELECT * FROM emi_schedule WHERE overdue_days > 5 AND RecoveryAssigning != 'yes';
";
$result = $conn->query($sqlFetchFailedEMI);

// Fetch recovery officers from users table
$sqlFetchRecoveryOfficers = "SELECT username FROM users WHERE role = 'recovery'";
$recoveryOfficers = $conn->query($sqlFetchRecoveryOfficers);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recoveries</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 60px;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .table th {
            background-color: #343a40;
            color: white;
        }

        .breadcrumb {
            background-color: #f8f9fa;
        }

        .breadcrumb a {
            color: #007bff;
        }

        .action-column {
            min-width: 200px;
        }

        .btn-assign {
            background-color: #007bff;
            color: white;
        }

        .page-title-box {
            /*background-color: #343a40;*/
            /*color: white;*/
            padding: 15px;
            border-radius: 5px;
        }

        .page-title-box h4 {
            margin: 0;
        }

        .page-title-right {
            float: right;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 font-size-18">Welcome!</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo $redirecturl ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Recoveries</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="mt-4 mb-4 text-center">Recovery EMI Data</h1>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Lead ID</th>
                        <th>Total EMIs</th>
                        <th>Paid EMIs</th>
                        <th>Last Payment Date</th>
                        <th>Next Payment Date</th>
                        <th>EMI Amount</th>
                        <th>Overdue Days</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['LeadID'] . "</td>";
                            echo "<td>" . $row['TotalEMIs'] . "</td>";
                            echo "<td>" . $row['PaidEMIs'] . "</td>";
                            echo "<td>" . $row['LastPaymentDate'] . "</td>";
                            echo "<td>" . $row['NextPaymentDate'] . "</td>";
                            echo "<td>₹" . number_format($row['EMIAmount'], 2) . "</td>";
                            echo "<td>" . $row['overdue_days'] . "</td>";
                            echo "<td class='action-column text-center'>";
                            echo "<form method='post' action='assign_recovery.php'>";
                            echo "<input type='hidden' name='emi_id' value='" . $row['ID'] . "'>";
                            echo "<div class='input-group'>";
                            echo "<select class='form-control' name='recovery_officer'>";
                            if ($recoveryOfficers->num_rows > 0) {
                                while ($officer = $recoveryOfficers->fetch_assoc()) {
                                    echo "<option value='" . $officer['username'] . "'>" . $officer['username'] . "</option>";
                                }
                            }
                            echo "</select>";
                            echo "<div class='input-group-append'>";
                            echo "<button type='submit' class='btn btn-assign btn-sm'>Assign</button>";
                            echo "</div>";
                            echo "</div>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center'>No records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>