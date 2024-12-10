<?php
include 'config.php';

$query = isset($_GET['query']) ? $_GET['query'] : '';

// Escape the query to prevent SQL injection
$searchQuery = $conn->real_escape_string($query);

// Initialize total EMI count and total amount due
$totalEMICount = 0;
$totalDueAmount = 0;

// Track partial payments for each LeadID
$leadPartialPayments = [];

// Build the SQL query based on search criteria
if (!empty($searchQuery)) {
    // Search query is provided: fetch filtered results
    $sqlFetchFailedEMI = "
        WITH RECURSIVE cte AS (
            SELECT ID, LeadID, sanctionedAmount, LoanPurpose, TotalEMIs, PaidEMIs, 
                   LastPaymentDate, NextPaymentDate, EMIAmount, Status, overdue_days, 
                   due_emis, PartialPayment, 1 AS emi_instance, overdue_days AS calculated_overdue_days,
                   NextPaymentDate AS calculated_payment_date
            FROM emi_schedule
            WHERE NextPaymentDate < NOW() 
              AND due_emis > 0
              AND overdue_days > 5
              AND (LoanPurpose LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
              OR LeadID = " . intval($searchQuery) . ")
            
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
} else {
    // No search query: fetch all records with overdue_days > 5
    $sqlFetchFailedEMI = "
        WITH RECURSIVE cte AS (
            SELECT ID, LeadID, sanctionedAmount, LoanPurpose, TotalEMIs, PaidEMIs, 
                   LastPaymentDate, NextPaymentDate, EMIAmount, Status, overdue_days, 
                   due_emis, PartialPayment, 1 AS emi_instance, overdue_days AS calculated_overdue_days,
                   NextPaymentDate AS calculated_payment_date
            FROM emi_schedule
            WHERE NextPaymentDate < NOW() 
              AND due_emis > 0
              AND overdue_days > 5
            
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
}

// Execute the query
$result = $conn->query($sqlFetchFailedEMI);

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
        echo "<td>" . $row['calculated_payment_date'] . "</td>"; // Display dynamically calculated EMI payment date
        echo "<td>₹" . number_format($adjustedEMIAmount, 2) . "</td>"; // Dynamically adjusted EMI amount
        echo "<td>" . $row['Status'] . "</td>";
        echo "<td>" . $row['calculated_overdue_days'] . "</td>";
        echo "</tr>";

        // Update total EMI count and total due amount
        $totalEMICount += 1;  // Each row represents one overdue EMI
        $totalDueAmount += $adjustedEMIAmount; // Account for adjusted EMI amount
    }

    // Display totals
    echo "<tr><td colspan='12'><strong>Total EMIs Due: </strong> " . $totalEMICount . "</td></tr>";
    echo "<tr><td colspan='12'><strong>Total Amount Due: </strong> ₹" . number_format($totalDueAmount, 2) . "</td></tr>";
} else {
    echo "<tr><td colspan='12'>No records found</td></tr>";
}

$conn->close();
?>
