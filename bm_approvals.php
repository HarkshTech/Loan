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
?>
<?php
include 'config.php';

// SQL query to fetch data from sanction_approvals table
$sql = "SELECT id, leadId, loanAmount, loanPurpose, tenure, sanctionedAmount,InterestRate,EMIAmount,firstemidate FROM sanction_approvals WHERE bm_approval='1' AND super_approval='0'";

$result = $conn->query($sql);

echo "
<style>
  table {
    width: 50%;
    border-collapse: collapse;
  }
  table, th, td {
    border: 1px solid #1C84EE;
    margin-top:90px;

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
  .btn-disburse {
    background-color: #007bff; /* Blue color */
  }
       tbody{
       height:15vh;
    }
    td{
        font-size:20px
    }
    th{
        font-size:14px;
    }
    @media (min-width:365px) {
   table, th, td{
   margin-left:100px;
  }
}
  



</style>
";

echo "<table><tr>
  <th>ID</th>
  <th>Lead ID</th>
  <th>Loan Amount</th>
  <th>Loan Purpose</th>
  <th>Tenure</th>
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
      <td>" . htmlspecialchars($row["sanctionedAmount"]) . "</td>
      <td>
        <a href='super_approval.php?id=" . htmlspecialchars($row["id"]) . "&leadid=" . htmlspecialchars($row["leadId"]) . "&loanAmount=" . htmlspecialchars($row["loanAmount"]) . "&loanPurpose=" . htmlspecialchars($row["loanPurpose"]) . "&tenure=" . htmlspecialchars($row["tenure"]) . "&sanctionedAmount=" . htmlspecialchars($row["sanctionedAmount"]) . "&interestrate=" . htmlspecialchars($row["InterestRate"]) . "&emi=" . htmlspecialchars($row["EMIAmount"]) . "&emidate=" . htmlspecialchars($row["firstemidate"]) ."' class='btn btn-disburse'>Disburse</a>
      </td>
    </tr>";
  }
} else {
  echo "<tr><td colspan='7'>No records found</td></tr>";
}

echo "</table>";

$conn->close();
?>
