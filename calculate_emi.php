<?php
// Fetch loan details from POST request
$loanAmount = $_POST['loanAmount'];
$loanTenure = $_POST['loanTenure'];
$loanType = $_POST['loanType'];

// Define interest rates based on loan type
switch ($loanType) {
    case 'homeLoan':
        $interestRate = 0.10; // 10% interest rate for Home Loan
        break;
    case 'personalLoan':
        $interestRate = 0.15; // 15% interest rate for Personal Loan
        break;
    case 'goldLoan':
        $interestRate = 0.10; // 10% interest rate for Gold Loan
        break;
    case 'loanAgainstProperty':
        $interestRate = 0.12; // 12% interest rate for Loan Against Property
        break;
    default:
        $interestRate = 0; // Default to 0 if loan type is not selected
}

// Calculate EMI
$monthlyInterestRate = $interestRate / 12; // Monthly interest rate
$emi = ($loanAmount * $monthlyInterestRate) / (1 - pow(1 + $monthlyInterestRate, -$loanTenure));
$formattedEMI = number_format((float)$emi, 2, '.', ''); // Format EMI to two decimal places

echo $formattedEMI; // Send EMI result back to client
?>
