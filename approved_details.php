<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Information Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
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
</head>
<body>
    <h1>Approval Information Details</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Approval Date</th>
                <th>Approved By</th>
                <th>Loan Amount</th>
                <th>Sanctioned Amount</th>
                <th>Loan Purpose</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include 'config.php';

            // Fetch data from the database (selecting LeadID, ApprovalTimestamp, ApprovedBy columns)
            $query_all_details = "SELECT ai.LeadID, pi.FullName, ai.ApprovalTimestamp, ai.ApprovedBy, ld.LoanAmount,es.sanctionedAmount, ld.LoanPurpose 
                                  FROM approval_information ai
                                  INNER JOIN personalinformation pi ON ai.LeadID = pi.ID
                                  INNER JOIN loandetails ld ON ai.LeadID = ld.ID
                                  INNER JOIN emi_schedule es ON ai.LeadID = es.LeadID
                                  WHERE ai.isDisbursed=1";
            $result_all_details = mysqli_query($conn, $query_all_details);

            // Check if query execution was successful
            if ($result_all_details) {
                // Fetch all rows from the result set
                $all_details_data = mysqli_fetch_all($result_all_details, MYSQLI_ASSOC);
                foreach ($all_details_data as $row): ?>
            <tr>
                <td><?php echo $row['LeadID']; ?></td>
                <td><?php echo $row['FullName']; ?></td>
                <td><?php echo $row['ApprovalTimestamp']; ?></td>
                <td><?php echo $row['ApprovedBy']; ?></td>
                <td><?php echo $row['LoanAmount']; ?></td>
                <td><?php echo $row['sanctionedAmount']; ?></td>
                <td><?php echo $row['LoanPurpose']; ?></td>
            </tr>
            <?php endforeach;
            } else {
                // Handle error if query execution failed
                echo "<tr><td colspan='6'>No data found.</td></tr>";
            }

            // Close the database connection
            mysqli_close($conn);
            ?>
        </tbody>
    </table>
</body>
</html>
