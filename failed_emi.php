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

// Update overdue days in emi_schedule table
$sqlUpdateOverdue = "UPDATE emi_schedule SET overdue_days = DATEDIFF(NOW(), NextPaymentDate)";
$conn->query($sqlUpdateOverdue);

// Update due_emis based on the number of months since Next Payment Date
$sqlUpdateDueEmis = "
    UPDATE emi_schedule 
    SET due_emis = 
        COALESCE(TIMESTAMPDIFF(MONTH, NextPaymentDate, NOW()) + 
        (CASE 
            WHEN DAY(NOW()) >= DAY(NextPaymentDate) THEN 1 
            ELSE 0 
        END), 0)
    WHERE NextPaymentDate < NOW()";
$conn->query($sqlUpdateDueEmis);

// Query to fetch EMI data with PartialPayment consideration
$sqlFetchFailedEMI = "
    WITH RECURSIVE cte AS (
        SELECT ID, LeadID, sanctionedAmount, LoanPurpose, TotalEMIs, PaidEMIs, 
               LastPaymentDate, NextPaymentDate, EMIAmount, Status, overdue_days, 
               due_emis, PartialPayment, 1 AS emi_instance, overdue_days AS calculated_overdue_days,
               NextPaymentDate AS calculated_payment_date
        FROM emi_schedule
        WHERE NextPaymentDate < NOW() AND due_emis > 0
        
        UNION ALL
        
        SELECT c.ID, c.LeadID, c.sanctionedAmount, c.LoanPurpose, c.TotalEMIs, c.PaidEMIs, 
               c.LastPaymentDate, c.NextPaymentDate, c.EMIAmount, c.Status, c.overdue_days, 
               c.due_emis, c.PartialPayment, c.emi_instance + 1,
               GREATEST(c.calculated_overdue_days - 30, 0) AS calculated_overdue_days,
               DATE_ADD(c.calculated_payment_date, INTERVAL 1 MONTH) AS calculated_payment_date
        FROM cte c
        WHERE c.emi_instance < c.due_emis
    )
    SELECT *
    FROM cte
    ORDER BY LeadID, calculated_payment_date, emi_instance";

$result = $conn->query($sqlFetchFailedEMI);

// Handle partial payment deduction and adjust the EMI amount
$leadPartialPayments = [];  // Track remaining partial payment for each LeadID

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Failed EMI Schedule</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 900px;
            margin-top: 80px !important;
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
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome!</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo $redirecturl ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Failed EMI's</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="mt-4 mb-4" style="font-size:30px;">Failed EMI Schedule</h1>
        <div class="mb-3">
            <input type="text" id="search-input" class="form-control" placeholder="Search by Loan Purpose">
        </div>
        <div class="table-responsive">
            <table class="table table-striped" id="emi-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Lead ID</th>
                        <th>Loan Amount</th>
                        <th>Loan Purpose</th>
                        <th>Total EMIs</th>
                        <th>Paid EMIs</th>
                        <th>Due EMIs</th>
                        <th>Last Payment Date</th>
                        <th>Next Payment Date</th>
                        <th>EMI Amount</th> <!-- The EMI amount will be dynamically adjusted -->
                        <th>Status</th>
                        <th>Overdue Days</th>
                    </tr>
                </thead>
                <tbody id="emi-table-body">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $leadID = $row['LeadID'];
                            $emiAmount = $row['EMIAmount'];
                            $partialPayment = $row['PartialPayment'];

                            // If this is the first EMI instance for the LeadID, initialize the partial payment
                            if (!isset($leadPartialPayments[$leadID])) {
                                $leadPartialPayments[$leadID] = $partialPayment;
                            }

                            // Adjust EMI amount with any remaining partial payment
                            if ($leadPartialPayments[$leadID] > 0) {
                                $adjustedEMIAmount = max(0, $emiAmount - $leadPartialPayments[$leadID]);
                                // Deduct the partial payment used for this EMI
                                $leadPartialPayments[$leadID] = max(0, $leadPartialPayments[$leadID] - $emiAmount);
                            } else {
                                $adjustedEMIAmount = $emiAmount;
                            }

                            echo "<tr>";
                            echo "<td>" . $row['ID'] . "</td>";
                            echo "<td>" . $row['LeadID'] . "</td>";
                            echo "<td>₹" . number_format($row['sanctionedAmount'], 2) . "</td>";
                            echo "<td>" . $row['LoanPurpose'] . "</td>";
                            echo "<td>" . $row['TotalEMIs'] . "</td>";
                            echo "<td>" . $row['PaidEMIs'] . "</td>";
                            echo "<td>1</td>"; // Each row represents 1 overdue EMI
                            echo "<td>" . $row['LastPaymentDate'] . "</td>";
                            echo "<td>" . $row['calculated_payment_date'] . "</td>";
                            echo "<td>₹" . number_format($adjustedEMIAmount, 2) . "</td>"; // Dynamically adjusted EMI amount
                            echo "<td>" . $row['Status'] . "</td>";
                            echo "<td>" . $row['calculated_overdue_days'] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='12'>No records found</td></tr>";
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
    <script>
        document.getElementById('search-input').addEventListener('keyup', function () {
            var query = this.value;

            // Create an AJAX request
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'search_failed_emis.php?query=' + query, true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById('emi-table-body').innerHTML = xhr.responseText;
                } else {
                    console.error('Failed to fetch data');
                }
            };
            xhr.send();
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>
