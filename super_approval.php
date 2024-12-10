<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'config.php';

// Handle the approval process
if (isset($_GET['id'])) {
    // Get the ID from the URL parameter and validate them
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $leadid = isset($_GET['leadid']) ? intval($_GET['leadid']) : 0;
    $loanpurpose = isset($_GET['loanPurpose']) ? $conn->real_escape_string($_GET['loanPurpose']) : '';
    $sanctionamount = isset($_GET['sanctionedAmount']) ? floatval($_GET['sanctionedAmount']) : 0.0;
    $loanamount = isset($_GET['loanAmount']) ? floatval($_GET['loanAmount']) : 0.0;
    $tenure = isset($_GET['tenure']) ? intval($_GET['tenure']) : 0;
    
    $interest=isset($_GET['interestrate']) ? floatval($_GET['interestrate'])*100 : 0;
    $emi=isset($_GET['emi']) ? ($_GET['emi']) : 0;
    
    $emidate= isset($_GET['emidate']) ? $_GET['emidate'] : null;

    // Debugging output to see the values of parameters
    echo "Debugging: id=$id, leadid=$leadid, loanPurpose=$loanpurpose, sanctionedAmount=$sanctionamount, loanAmount=$loanamount, tenure=$tenure";

    // Check if required parameters are provided
    if ($id > 0 && $leadid > 0 && !empty($loanpurpose) && $sanctionamount > 0 && $loanamount > 0 && $tenure > 0) {
        // Update the super_approval column to 1 for the specified ID
        $sql = "UPDATE sanction_approvals SET super_approval = 1 WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            // Record updated successfully
            echo "<script>
            alert('Record updated successfully');
            window.location.href = 'EMI-new.php?leadId=$leadid&loanAmount=$loanamount&loanpurpose=$loanpurpose&sanctionamount=$sanctionamount&tenure=$tenure&interest=$interest&emi=$emi&date=$emidate';
            </script>";
        } else {
            // Error updating record
            echo "<script>
            alert('Error updating record: " . $conn->error . "');
            window.location.href = 'bm_approvals.php';
            </script>";
        }
    } else {
        // Missing required parameters
        echo "<script>
        alert('Missing required parameters');
        window.location.href = 'bm_approvals.php';
        </script>";
    }
} else {
    // Display the table of approvals

    // SQL query to fetch data from sanction_approvals table
    $sql = "SELECT id, leadId, loanAmount, loanPurpose, tenure, sanctionedAmount FROM sanction_approvals";

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
      .btn-disburse {
        background-color: #007bff; /* Blue color */
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
            <a href='?id=" . htmlspecialchars($row["id"]) . "&leadid=" . htmlspecialchars($row["leadId"]) . "&loanAmount=" . htmlspecialchars($row["loanAmount"]) . "&loanPurpose=" . htmlspecialchars($row["loanPurpose"]) . "&tenure=" . htmlspecialchars($row["tenure"]) . "&sanctionedAmount=" . htmlspecialchars($row["sanctionedAmount"]) . "' class='btn btn-disburse'>Disburse</a>
          </td>
        </tr>";
      }
    } else {
      echo "<tr><td colspan='7'>No records found</td></tr>";
    }

    echo "</table>";
}

$conn->close();
?>
