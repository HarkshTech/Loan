<?php
include 'config.php';

if(isset($_GET['id'])) {
    $id = $_GET['id'];

    // Check if the form is submitted
    if(isset($_POST['submit'])) {
        // Sanitize remarks input to prevent SQL injection
        $remarks = $conn->real_escape_string($_POST['remarks']);
        $leadid= $_GET['leadid'];

        // Update bm_approval and reject_remarks for the given ID
        $sql = "UPDATE sanction_approvals SET bm_approval = 2, reject_remarks = '$remarks' WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            // Redirect to display_loan_data.php after updating
            $sql2="UPDATE personalinformation SET LoanStatus='Not Disbursed',StepReached='Disbursal Rejected By Customer' WHERE ID=$leadid";
            $conn->query($sql2);
            echo "<script>
                    alert('Record rejected successfully');
                    window.location.href = 'display_loan_data.php';
                  </script>";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
} else {
    echo "ID parameter is not set";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reject Loan</title>
</head>
<body>
    <h2>Reject Loan</h2>
    <form method="post">
        <label for="remarks">Remarks:</label><br>
        <textarea id="remarks" name="remarks" rows="4" cols="50" required></textarea><br><br>
        <input type="submit" name="submit" value="Submit">
    </form>
</body>
</html>
