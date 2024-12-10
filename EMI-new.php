<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';
// Retrieve data from URL parameters
$leadId = isset($_GET['leadId']) ? $_GET['leadId'] : '';

// Fetch loan details from the database
$loanAmount = '';
$loanPurpose = '';

$interestRate='';
$sanctioned='';
$emi='';

if (!empty($leadId)) {
    $sql = "SELECT LoanAmount, LoanPurpose FROM loandetails WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $leadId);
    $stmt->execute();
    $stmt->bind_result($loanAmount, $loanPurpose);
    $stmt->fetch();
    $stmt->close(); // Close the statement to avoid "Commands out of sync" error

    // Fetch interest details from sanction_approvals table
    $fetchinterest = "SELECT sanctionedAmount, InterestRate, EMIAmount FROM sanction_approvals WHERE leadId = ?";
    $stmt2 = $conn->prepare($fetchinterest);
    $stmt2->bind_param("s", $leadId);
    $stmt2->execute();
    $stmt2->bind_result($sanctioned, $interestRate, $emi);
    $stmt2->fetch();
    $stmt2->close(); // Close the statement to avoid "Commands out of sync" error
}

// Close the connection
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMI Schedule</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="margin-left: 60px;">
    <div class="container mt-4">
        <form id="emiForm" method="get">
            <!-- Input fields for leadId, loanAmount, loanPurpose, and tenure -->
            <div class="form-group" style="display: none;">
                <label for="leadId">Lead ID:</label>
                <input type="text" id="leadId" name="leadId" value="<?php echo isset($_GET['leadId']) ? htmlspecialchars($_GET['leadId']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="loanAmount">Loan Amount (in ₹):</label>
                <input type="number" id="loanAmount" name="loanAmount" value="<?php echo isset($loanAmount) ? htmlspecialchars($loanAmount) : ''; ?>" min="1" required>
            </div>

            <div class="form-group">
                <label for="loanPurpose">Loan Purpose:</label>
               <input type="hidden" id="loanPurpose" name="loanPurpose" value="<?php echo isset($_GET['loanpurpose']) ? htmlspecialchars($_GET['loanpurpose']) : ''; ?>">
                <select id="loanPurposeDisplay" disabled>
                    <option value="Personal Loan" <?php echo isset($_GET['loanpurpose']) && $_GET['loanpurpose'] === 'Personal Loan' ? 'selected' : ''; ?>>Personal Loan</option>
                    <option value="Gold Loan" <?php echo isset($_GET['loanpurpose']) && $_GET['loanpurpose'] === 'Gold Loan' ? 'selected' : ''; ?>>Gold Loan</option>
                    <option value="Loan Against Property (LAP)" <?php echo isset($_GET['loanpurpose']) && $_GET['loanpurpose'] === 'LAP' ? 'selected' : ''; ?>>Loan Against Property (LAP)</option>
                    <option value="Home Loan" <?php echo isset($_GET['loanpurpose']) && $_GET['loanpurpose'] === 'Home Loan' ? 'selected' : ''; ?>>Home Loan</option>
                </select>

            </div>
              <div class="form-group">
                <label for="sanctionedamount">Sanctioned Amount (in ₹):</label>
                <input type="number" id="sanctionedamount" name="sanctionedAmount" value="<?php echo isset($_GET['sanctionamount']) ? htmlspecialchars($_GET['sanctionamount']) : ''; ?>" min="1" required>
            </div>
            <div class="form-group">
                <label for="tenure">Loan Tenure (in months):</label>
                <input type="number" id="tenure" name="tenure" value="<?php echo isset($_GET['tenure']) ? htmlspecialchars($_GET['tenure']) : ''; ?>" min="1" required>
            </div>
            <!--added new hidden input for interest rate-->
            <div class="form-group">
                <input type="hidden" id="interestRate" name="interestRate" value="<?php echo isset($_GET['interest']) ? htmlspecialchars($_GET['interest']) : ''; ?>" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <input type="hidden" id="emiAmount" name="emiAmount" value="<?php echo isset($_GET['emi']) ? htmlspecialchars($_GET['emi']) : ''; ?>" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="emidate">First EMI Date:</label>
                <input type="date" id="emidate" name="emidate" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>" srequired>
            </div>
            
            <!---->
            <button type="submit" class="btn btn-primary">Generate EMI Schedule</button>
            <!-- Add update button -->
            <button type="button" id="proceedDisbursalBtn" class="btn btn-success">Proceed To Disbursal</button>

        </form>
        <hr>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            // Retrieve data from form inputs
            $leadId = $_GET['leadId'];
            $loanAmount = $_GET['sanctionamount'];
            $loanPurpose = $_GET['loanpurpose'];
            $tenure = $_GET['tenure'];

            // Your EMI calculation and table generation logic here
            echo '<h2>EMI Schedule for Loan ID: ' . $leadId . '</h2>';
            echo '<p>Loan Amount: ₹' . $loanAmount . '</p>';
            echo '<p>Loan Purpose: ' . $loanPurpose . '</p>';
            echo '<p>Tenure: ' . $tenure . ' months</p>';

            $totalEMIs = $tenure; // Total number of EMIs (months)
            $emiAmount = $emi; // EMI amount

            // Generate EMI table or content
            echo '<div class="table-responsive">';
            echo '<table class="table table-striped">';
            echo '<thead class="thead-dark">';
            echo '<tr>';
            echo '<th>ID</th>';
            echo '<th>LeadID</th>';
            echo '<th>SanctionedAmount</th>';
            echo '<th>LoanPurpose</th>';
            echo '<th>Tenure</th>';
            echo '<th>EMIAmount</th>';
            echo '<th>InterestRate</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            // Display loan details in table rows
            echo '<tr>';
            echo '<td>' . $leadId . '</td>';
            echo '<td>' . $leadId . '</td>';
            echo '<td>₹' . number_format($loanAmount, 2) . '</td>'; // Format loan amount
            echo '<td>' . $loanPurpose . '</td>';
            echo '<td>' . $tenure . ' months</td>';
            echo '<td>₹' . number_format($emiAmount, 2) . '</td>'; // Format EMI amount
            echo '<td>' . ($interestRate * 100) . '%</td>'; // Display interest rate
            echo '</tr>';
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        }
        ?>
    </div>

    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('loanPurpose').readOnly = true;
            });
            $(document).ready(function() {
                // Hide Lead ID field but keep it available for use
                $('#leadId').parent().hide();

                // Make Loan Purpose field readonly
                $('#loanPurpose').attr('readonly', 'readonly');
            });
        </script>
   
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#loanPurpose').prop('readonly', true);
            });
        </script>

<script>
$(document).ready(function() {
            $('#proceedDisbursalBtn').click(function() {
                $.ajax({
                    type: 'POST',
                    url: 'update_emi_schedule.php', // Path to your PHP script for updating emi_schedule data
                    data: $('#emiForm').serialize()+ '&source=EMI-new.php', // Serialize form data
                    success: function(response) {
                        alert('Loan Disbursed successfully!');
                        window.location.href = 'approved_loans.php';
                    },
                    error: function(xhr, status, error) {
                        alert('Error updating EMI data: ' + error);
                    }
                });
            });
        });
    $(document).ready(function() {
        var loanAmount = parseInt($('#loanAmount').val());
        var sanctionedAmount = parseInt($('#sanctionedamount').val());

        // if (loanAmount === sanctionedAmount) {
        //     $('#proceedDisbursalBtn').show();
        // } else if (loanAmount > sanctionedAmount) {
        //     $('#proceedBranchManagerBtn').show();
        // }

        $('#proceedBranchManagerBtn').click(function() {
            // Prepare data to be sent to the server
            var tableData = {
                leadId: $('#leadId').val(),
                loanAmount: $('#loanAmount').val(),
                loanPurpose: $('#loanPurpose').val(),
                tenure: $('#tenure').val(),
                sanctionedAmount: $('#sanctionedamount').val()
            };

            // Send AJAX request to save_loan_data.php
            $.ajax({
                type: 'POST',
                url: 'save_loan_data.php', // PHP file to handle the request
                data: tableData,
                success: function(response) {
                    alert('Branch Approval Initiated Successfully!');
                    window.location.href = 'approved_loans.php';
                },
                error: function(xhr, status, error) {
                    alert('Error saving data to database: ' + error);
                }
            });
        });
    });
</script>

</body>
</html>

<!-- 
1. editing loan purpose should edit the loandetails table too to edit loan purpose type.
2. update_emi_schedule.php
 
-->
