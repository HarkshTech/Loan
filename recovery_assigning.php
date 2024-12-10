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
$sqlUpdateOverdue = "UPDATE emi_schedule SET overdue_days = DATEDIFF(NOW(), NextPaymentDate)";
$conn->query($sqlUpdateOverdue);

// Handle search filters
$searchLeadID = $_POST['search_lead_id'] ?? '';
$searchRecoveryOfficer = $_POST['search_recovery_officer'] ?? '';
$searchAssignmentStatus = $_POST['search_assignment_status'] ?? '';

// Prepare WHERE conditions
$whereClauses = [];
if (!empty($searchLeadID)) {
    $whereClauses[] = "es.LeadID LIKE '%" . $conn->real_escape_string($searchLeadID) . "%'";
}
if (!empty($searchRecoveryOfficer)) {
    $whereClauses[] = "rd.AssignedTo = '" . $conn->real_escape_string($searchRecoveryOfficer) . "'";
}
if ($searchAssignmentStatus === 'assigned') {
    $whereClauses[] = "rd.AssignedTo IS NOT NULL";
} elseif ($searchAssignmentStatus === 'not_assigned') {
    $whereClauses[] = "rd.AssignedTo IS NULL";
}
$whereClause = !empty($whereClauses) ? "WHERE " . implode(' AND ', $whereClauses) : "";

// Fetch data from emi_schedule table with applied filters
$sqlFetchFailedEMI = "
    SELECT es.ID, es.LeadID, es.TotalEMIs, es.PaidEMIs, es.LastPaymentDate, es.NextPaymentDate, es.EMIAmount, es.overdue_days, rd.AssignedTo
    FROM emi_schedule es
    LEFT JOIN recovery_data rd ON rd.LeadID = es.LeadID
    $whereClause
    AND es.overdue_days > 5";
$result = $conn->query($sqlFetchFailedEMI);

// Fetch recovery officers from users table
$sqlFetchRecoveryOfficers = "SELECT username FROM users WHERE role = 'recovery'";
$recoveryOfficersResult = $conn->query($sqlFetchRecoveryOfficers);

$recoveryOfficers = [];
if ($recoveryOfficersResult->num_rows > 0) {
    while ($officer = $recoveryOfficersResult->fetch_assoc()) {
        $recoveryOfficers[] = $officer['username'];
    }
}
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

        .btn-assign, .btn-reassign {
            color: white;
        }

        .btn-assign {
            background-color: #007bff;
        }

        .btn-reassign {
            background-color: #6c757d;
        }

        .page-title-box {
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

        <!-- Search Form -->
        <form method="post" class="mb-4">
            <div class="form-row">
                <div class="col-md-3">
                    <input type="text" name="search_lead_id" class="form-control" placeholder="Search by Lead ID" value="<?php echo htmlspecialchars($searchLeadID); ?>">
                </div>
                <div class="col-md-3">
                    <select name="search_recovery_officer" class="form-control">
                        <option value="">Select Recovery Officer</option>
                        <?php foreach ($recoveryOfficers as $officer) : ?>
                            <option value="<?php echo htmlspecialchars($officer); ?>" <?php echo ($searchRecoveryOfficer === $officer) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($officer); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="search_assignment_status" class="form-control">
                        <option value="">Select Assignment Status</option>
                        <option value="assigned" <?php echo ($searchAssignmentStatus === 'assigned') ? 'selected' : ''; ?>>Assigned</option>
                        <option value="not_assigned" <?php echo ($searchAssignmentStatus === 'not_assigned') ? 'selected' : ''; ?>>Not Assigned</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-block">Search</button>
                </div>
            </div>
        </form>

        <!-- EMI Table -->
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
                        <th>Assigned Recovery Officer</th>
                        <th class="text-center">Assign</th>
                        <th class="text-center">Reassign</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $assignedOfficer = $row['AssignedTo'] ?? 'Unassigned';

                            echo "<tr>";
                            echo "<td>" . $row['LeadID'] . "</td>";
                            echo "<td>" . $row['TotalEMIs'] . "</td>";
                            echo "<td>" . $row['PaidEMIs'] . "</td>";
                            echo "<td>" . $row['LastPaymentDate'] . "</td>";
                            echo "<td>" . $row['NextPaymentDate'] . "</td>";
                            echo "<td>₹" . number_format($row['EMIAmount'], 2) . "</td>";
                            echo "<td>" . $row['overdue_days'] . "</td>";
                            echo "<td>" . htmlspecialchars($assignedOfficer) . "</td>";

                            if ($assignedOfficer === 'Unassigned') {
                                // Assign button
                                echo "<td class='text-center'>";
                                echo "<form method='post' action='assign_recovery.php'>";
                                echo "<input type='hidden' name='emi_id' value='" . $row['ID'] . "'>";
                                echo "<div class='input-group'>";
                                echo "<select class='form-control' name='recovery_officer'>";
                                foreach ($recoveryOfficers as $officer) {
                                    echo "<option value='" . htmlspecialchars($officer) . "'>" . htmlspecialchars($officer) . "</option>";
                                }
                                echo "</select>";
                                echo "<div class='input-group-append'>";
                                echo "<button type='submit' class='btn btn-assign btn-sm'>Assign</button>";
                                echo "</div>";
                                echo "</div>";
                                echo "</form>";
                                echo "</td>";

                                // Empty Reassign column
                                echo "<td class='text-center'></td>";
                            } else {
                                // Empty Assign column
                                echo "<td class='text-center'></td>";

                                // Reassign button
                                echo "<td class='text-center'>";
                                echo "<form method='post' action='assign_recovery.php'>";
                                echo "<input type='hidden' name='emi_id' value='" . $row['ID'] . "'>";
                                echo "<div class='input-group'>";
                                echo "<select class='form-control' name='recovery_officer'>";
                                foreach ($recoveryOfficers as $officer) {
                                    echo "<option value='" . htmlspecialchars($officer) . "'>" . htmlspecialchars($officer) . "</option>";
                                }
                                echo "</select>";
                                echo "<div class='input-group-append'>";
                                echo "<button type='submit' class='btn btn-reassign btn-sm'>Reassign</button>";
                                echo "</div>";
                                echo "</div>";
                                echo "</form>";
                                echo "</td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10' class='text-center'>No records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
