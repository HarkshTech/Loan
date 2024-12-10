<?php
include 'config.php';

if(isset($_GET['id'])) {
    $id = $_GET['id'];

    // Update bm_approval to 1 for the given ID
    $sql = "UPDATE sanction_approvals SET bm_approval = 1 WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        // Redirect to display_loan_data.php after updating
        echo "<script>
                alert('Proceed for admin disbursal');
                window.location.href = 'display_loan_data.php';
              </script>";
    } else {
        echo "Error updating record: " . $conn->error;
    }
} else {
    echo "ID parameter is not set";
}

$conn->close();
?>
