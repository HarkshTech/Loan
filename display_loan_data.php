<?php
include 'config.php';

// SQL query to fetch data from sanction_approvals table
$sql = "SELECT id, leadId, loanAmount, loanPurpose, tenure, sanctionedAmount,InterestRate,EMIAmount,firstemidate FROM sanction_approvals WHERE bm_approval NOT IN (1, 2)";

$result = $conn->query($sql);

echo "
<style>
  table {
    width: 100%;
    border-collapse: collapse;
  }
  table, th, td {
    border: 1px solid #1C84EE;
  }
  th {
    background-color: #1C84EE;
    color: white;
    padding: 8px;
    text-align: left;
  }
  td {
    padding: 8px;
    text-align: left;
  }
  tr:nth-child(even) {
    background-color: #f2f2f2;
  }
  .btn {
    padding: 5px 10px;
    margin: 2px;
    text-decoration: none;
    color: white;
    border-radius: 3px;
  }
  .btn-accept {
    background-color: #28a745;
  }
  .btn-reject {
    background-color: #dc3545;
  }
</style>
";

echo "<table><tr>
  <th>ID</th>
  <th>Lead ID</th>
  <th>Loan Amount</th>
  <th>Loan Purpose</th>
  <th>Tenure</th>
  <th>Interest Rate</th>
  <th>EMI Amount</th>
  <th>First EMI Date</th>
  <th>Sanctioned Amount</th>
  <th>Action</th>
</tr>";

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    echo "<tr>
      <td>" . htmlspecialchars($row["id"]) . "</td>
      <td>" . htmlspecialchars($row["leadId"]) . "</td>
      <td>" . htmlspecialchars($row["loanAmount"]) . "</td>
      <td>" . htmlspecialchars($row["loanPurpose"]) . "</td>
      <td>" . htmlspecialchars($row["tenure"]) . "</td>
      <td>".htmlspecialchars($row["InterestRate"] * 100) . "%"."</td>
      <td>" . htmlspecialchars($row["EMIAmount"]) . "</td>
      <td>" . htmlspecialchars($row["firstemidate"]) . "</td>
      <td>" . htmlspecialchars($row["sanctionedAmount"]) . "</td>
      <td>
        <a href='accept.php?id=" . htmlspecialchars($row["id"]) . "' class='btn btn-accept'>Accept</a>
        <a href='reject.php?id=".htmlspecialchars($row["id"])."&leadid=".htmlspecialchars($row["leadId"])."' class='btn btn-reject'>Reject</a>

      </td>
    </tr>";
  }
} else {
  echo "<tr><td colspan='9'>No records found</td></tr>";
}

echo "</table>";

$conn->close();
?>
<script>
function openRejectModal(id) {
  var modal = document.getElementById('rejectModal_' + id);
  modal.style.display = 'block';
}

function closeRejectModal(id) {
  var modal = document.getElementById('rejectModal_' + id);
  modal.style.display = 'none';
}
</script>

