<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Loan Details</title>
<style>
    .details-form {
        margin-top: 20px;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    .details-form label {
        display: block;
        margin-bottom: 10px;
    }
    .details-form input[type="text"] {
        width: 100%;
        padding: 5px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }
</style>
</head>
<body>

<?php
// Retrieve the details from the URL parameters
$id = $_GET['id'] ?? '';
$leadID = $_GET['leadID'] ?? '';
$isApproved = $_GET['isApproved'] ?? '';
$approvalTimestamp = $_GET['approvalTimestamp'] ?? '';
$approvedBy = $_GET['approvedBy'] ?? '';
$loanAmount = $_GET['loanAmount'] ?? '';
$loanPurpose = $_GET['loanPurpose'] ?? '';
?>

<div class="details-form">
    <h2>ID Information</h2>
    <label for="id">ID:</label>
    <input type="text" id="id" value="<?php echo htmlspecialchars($id); ?>" readonly><br>
    <label for="leadID">LeadID:</label>
    <input type="text" id="leadID" value="<?php echo htmlspecialchars($leadID); ?>" readonly><br>
    <label for="isApproved">IsApproved:</label>
    <input type="text" id="isApproved" value="<?php echo htmlspecialchars($isApproved); ?>" readonly><br>
    <label for="approvalTimestamp">ApprovalTimestamp:</label>
    <input type="text" id="approvalTimestamp" value="<?php echo htmlspecialchars($approvalTimestamp); ?>" readonly><br>
    <label for="approvedBy">ApprovedBy:</label>
    <input type="text" id="approvedBy" value="<?php echo htmlspecialchars($approvedBy); ?>" readonly><br>
</div>

<div class="details-form">
    <h2>Loan Details</h2>
    <label for="loanAmount">LoanAmount:</label>
    <input type="text" id="loanAmount" value="<?php echo htmlspecialchars($loanAmount); ?>" readonly><br>
    <label for="loanPurpose">LoanPurpose:</label>
    <input type="text" id="loanPurpose" value="<?php echo htmlspecialchars($loanPurpose); ?>" readonly><br>
</div>

<!-- Add document collection details here if needed -->

</body>
</html>
