
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leadId = $_POST['leadId'];
    $loanAmount = $_POST['sanctionedAmount'];
    $loanPurpose = $_POST['loanPurpose'];
    $tenure = $_POST['tenure'];

    // Apply interest based on loan type
    $interestRate = 0; // Default interest rate
    switch ($loanPurpose) {
        case 'Personal Loan':
            $interestRate = 0.15; // 15%
            break;
        case 'Gold Loan':
            $interestRate = 0.12; // 12%
            break;
        case 'Loan Against Property (LAP)':
            $interestRate = 0.13; // 13%
            break;
        case 'Home Loan':
            $interestRate = 0.09; // 9%
            break;
        default:
            $interestRate = 0.1; // Default to 10% for other types
    }

    // Calculate monthly installment
    $monthlyInterest = $loanAmount * $interestRate / 12; // Monthly interest
    $totalEMIs = $tenure; // Total number of EMIs (months)
    $emiAmount = ($loanAmount / $tenure) + $monthlyInterest; // EMI amount
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Loan Data</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="margin-left: 60px;">
    <div class="container mt-4">
        <h2>EMI Schedule for Loan ID: <?php echo htmlspecialchars($leadId); ?></h2>
        <p>Loan Amount: ₹<?php echo number_format($loanAmount, 2); ?></p>
        <p>Loan Purpose: <?php echo htmlspecialchars($loanPurpose); ?></p>
        <p>Tenure: <?php echo htmlspecialchars($tenure); ?> months</p>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>LeadID</th>
                        <th>LoanAmount</th>
                        <th>LoanPurpose</th>
                        <th>Tenure</th>
                        <th>EMIAmount</th>
                        <th>InterestRate</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($leadId); ?></td>
                        <td><?php echo htmlspecialchars($leadId); ?></td>
                        <td>₹<?php echo number_format($loanAmount, 2); ?></td>
                        <td><?php echo htmlspecialchars($loanPurpose); ?></td>
                        <td><?php echo htmlspecialchars($tenure); ?> months</td>
                        <td>₹<?php echo number_format($emiAmount, 2); ?></td>
                        <td><?php echo ($interestRate * 100); ?>%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
