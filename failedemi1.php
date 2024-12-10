<?php
include 'config.php';

// Update overdue days in emi_schedule table
$sqlUpdateOverdue = "UPDATE emi_schedule SET overdue_days = DATEDIFF(NOW(), NextPaymentDate)";
$conn->query($sqlUpdateOverdue);

// Fetch data from emi_schedule table where overdue days > 1
$sqlFetchFailedEMI = "SELECT * FROM emi_schedule WHERE overdue_days > 5";
$result = $conn->query($sqlFetchFailedEMI);
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
            margin-top:8%;
            margin-left:20% !important;
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

<body >
    <?php include 'leftbsidesales.php' ?>
    <div class="container">
        <h1 class="mt-4 mb-4">Failed EMI Schedule</h1>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Lead ID</th>
                        <th>Loan Amount</th>
                        <th>Loan Purpose</th>
                        <th>Total EMIs</th>
                        <th>Paid EMIs</th>
                        <th>Last Payment Date</th>
                        <th>Next Payment Date</th>
                        <th>EMI Amount</th>
                        <th>Status</th>
                        <th>Overdue Days</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['ID'] . "</td>";
                            echo "<td>" . $row['LeadID'] . "</td>";
                            echo "<td>₹" . number_format($row['LoanAmount'], 2) . "</td>";
                            echo "<td>" . $row['LoanPurpose'] . "</td>";
                            echo "<td>" . $row['TotalEMIs'] . "</td>";
                            echo "<td>" . $row['PaidEMIs'] . "</td>";
                            echo "<td>" . $row['LastPaymentDate'] . "</td>";
                            echo "<td>" . $row['NextPaymentDate'] . "</td>";
                            echo "<td>₹" . number_format($row['EMIAmount'], 2) . "</td>";
                            echo "<td>" . $row['Status'] . "</td>";
                            echo "<td>" . $row['overdue_days'] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11'>No records found</td></tr>";
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
