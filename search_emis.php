<?php
include 'config.php';

$searchQuery = isset($_POST['searchQuery']) ? $_POST['searchQuery'] : '';

$sql = "SELECT e.ID, e.LeadID, e.sanctionedAmount, e.EMIAmount,e.PartialPayment,e.PenaltyAmount, p.FullName, e.NextPaymentDate
        FROM emi_schedule e
        JOIN personalinformation p ON e.LeadID = p.ID
        WHERE e.PaidEMIs < e.TotalEMIs";

if (!empty($searchQuery)) {
    $sql .= " AND (p.FullName LIKE '%$searchQuery%' OR e.LeadID LIKE '%$searchQuery%')";
}

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo "<table class='table table-striped'>
            <thead class='thead-dark'>
                <tr>
                    <th>ID</th>
                    <th>Lead ID</th>
                    <th>Loan Amount</th>
                    <th>EMI Amount</th>
                    <th>Partial Payment</th>
                    <th>Penalty</th>
                    <th>Customer Name</th>
                    <th>Collection Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['ID'] . "</td>";
        echo "<td>" . $row['LeadID'] . "</td>";
        echo "<td>₹" . number_format($row['sanctionedAmount'], 2) . "</td>";
        echo "<td>₹" . number_format($row['EMIAmount'], 2) . "</td>";
        echo "<td>₹" . $row['PartialPayment'] . "</td>";
        echo "<td>₹" . $row['PenaltyAmount'] . "</td>";
        echo "<td>" . $row['FullName'] . "</td>";
        echo "<td>" . $row['NextPaymentDate'] . "</td>";
        echo "<td class='action-column'>
                <button type='button' class='btn btn-primary' data-toggle='modal' data-target='#actionModal' data-lead-id='" . $row['LeadID'] . "'>Select Action</button>
              </td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p>No pending payments found.</p>";
}

mysqli_close($conn);
?>
