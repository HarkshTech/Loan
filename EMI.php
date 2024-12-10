<?php
session_start();
include 'config.php';

// Retrieve data from URL parameters
$leadId = isset($_GET['leadId']) ? $_GET['leadId'] : '';

// Initialize variables
$loanAmount = '';
$loanPurpose = '';
$sanctionedAmount = '';
$tenure = '';
$interestRate = '';
$emiDate = '';
$emiAmount = '';
$dataFetched = false; // Variable to check if data was fetched from loan_approvals

// Fetch loan details from loandetails table
if (!empty($leadId)) {
    $sql = "SELECT LoanAmount, LoanPurpose FROM loandetails WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $leadId);
    $stmt->execute();
    $stmt->bind_result($loanAmount, $loanPurpose);
    $stmt->fetch();
    $stmt->close();

    // Check if data exists in loan_approvals table
    $sql = "SELECT loan_amount, sanctioned_amount, tenure, interest_rate, first_emi_date, emi_amount FROM loan_approvals WHERE leadid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $leadId);
    $stmt->execute();
    $stmt->bind_result($loanAmount, $sanctionedAmount, $tenure, $interestRate, $emiDate, $emiAmount);
    if ($stmt->fetch()) {
        $dataFetched = true; // Data exists in loan_approvals
    }
    $stmt->close();
}

// Assume user role is retrieved from session or similar mechanism
$userRole = $_SESSION['role']; // Example role, adjust according to your actual implementation

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
            <!-- Input fields for leadId, loanAmount, loanPurpose, sanctionedAmount, tenure, and interestRate -->
            <div class="form-group" style="display: none;">
                <label for="leadId">Lead ID:</label>
                <input type="text" id="leadId" name="leadId" value="<?php echo htmlspecialchars($leadId); ?>" required>
            </div>
            <div class="form-group">
                <label for="loanAmount">Loan Amount (in ₹):</label>
                <input type="number" id="loanAmount" name="loanAmount" value="<?php echo isset($_GET['loanAmount']) ? htmlspecialchars($_GET['loanAmount']) : htmlspecialchars($loanAmount); ?>" min="1" required>
            </div>
            <div class="form-group">
                <label for="loanPurpose">Loan Purpose:</label>
                <input type="hidden" id="loanPurpose" name="loanPurpose" value="<?php echo isset($_GET['loanPurpose']) ? htmlspecialchars($_GET['loanPurpose']) : htmlspecialchars($loanPurpose); ?>">
                <select id="loanPurposeDisplay" disabled>
                    <option value="Personal Loan" <?php echo (isset($_GET['loanPurpose']) ? $_GET['loanPurpose'] : $loanPurpose) === 'Personal Loan' ? 'selected' : ''; ?>>Personal Loan</option>
                    <option value="Gold Loan" <?php echo (isset($_GET['loanPurpose']) ? $_GET['loanPurpose'] : $loanPurpose) === 'Gold Loan' ? 'selected' : ''; ?>>Gold Loan</option>
                    <option value="Loan Against Property (LAP)" <?php echo (isset($_GET['loanPurpose']) ? $_GET['loanPurpose'] : $loanPurpose) === 'LAP' ? 'selected' : ''; ?>>Loan Against Property (LAP)</option>
                    <option value="Home Loan" <?php echo (isset($_GET['loanPurpose']) ? $_GET['loanPurpose'] : $loanPurpose) === 'Home Loan' ? 'selected' : ''; ?>>Home Loan</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sanctionedamount">Sanctioned Amount (in ₹):</label>
                <input type="number" id="sanctionedamount" name="sanctionedAmount" value="<?php echo isset($_GET['sanctionedAmount']) ? htmlspecialchars($_GET['sanctionedAmount']) : htmlspecialchars($sanctionedAmount); ?>" min="1" required>
            </div>
            <div class="form-group">
                <label for="tenure">Loan Tenure (in months):</label>
                <input type="number" id="tenure" name="tenure" value="<?php echo isset($_GET['tenure']) ? htmlspecialchars($_GET['tenure']) : htmlspecialchars($tenure); ?>" min="1" required>
            </div>
            <div class="form-group">
                <label for="interestRate">Interest Rate (in % per annum):</label>
                <input type="number" id="interestRate" name="interestRate" value="<?php echo isset($_GET['interestRate']) ? htmlspecialchars($_GET['interestRate']) : htmlspecialchars($interestRate); ?>" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label for="emidate">First EMI Date:</label>
                <input type="date" id="emidate" name="emidate" value="<?php echo isset($_GET['emidate']) ? htmlspecialchars($_GET['emidate']) : htmlspecialchars($emiDate); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Generate EMI Schedule</button>
            <!-- Add update button -->
            <button type="button" id="proceedDisbursalBtn" class="btn btn-success" style="display: none;">Proceed To Disbursal</button>
            <button type="button" id="proceedBranchManagerBtn" class="btn btn-warning" style="display: none;">Proceed To Branch Manager</button>
            <button type="button" id="proceedAdminApprovalBtn" class="btn btn-info" style="display: none;">Proceed For ADMIN Approval</button>
        </form>
        <hr>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['leadId'])) {
            // Retrieve data from form inputs
            $leadId = $_GET['leadId'];
            $loanAmount = $_GET['loanAmount'];
            $sanctionedAmount = $_GET['sanctionedAmount'];
            $loanPurpose = $_GET['loanPurpose'];
            $tenure = $_GET['tenure'];
            $interestRate = $_GET['interestRate'] / 100; // Convert percentage to decimal
            $emiDate = $_GET['emidate'];

            // Your EMI calculation and table generation logic here
            echo '<h2>EMI Schedule for Loan ID: ' . htmlspecialchars($leadId) . '</h2>';
            echo '<p>Loan Amount: ₹' . htmlspecialchars($loanAmount) . '</p>';
            echo '<p>Sanctioned Amount: ₹' . htmlspecialchars($sanctionedAmount) . '</p>';
            echo '<p>Loan Purpose: ' . htmlspecialchars($loanPurpose) . '</p>';
            echo '<p>Tenure: ' . htmlspecialchars($tenure) . ' months</p>';
            echo '<p>Interest Rate: ' . ($interestRate * 100) . '% per annum</p>';
            echo '<p>First EMI Date: ' . htmlspecialchars($emiDate) . '</p>';

            // Calculate monthly installment
            $monthlyInterest = $sanctionedAmount * $interestRate / 12; // Monthly interest
            $emiAmount = ($sanctionedAmount / $tenure) + $monthlyInterest; // EMI amount

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
            echo '<td>' . htmlspecialchars($leadId) . '</td>';
            echo '<td>' . htmlspecialchars($leadId) . '</td>';
            echo '<td>₹' . number_format($sanctionedAmount, 2) . '</td>'; // Format sanctioned amount
            echo '<td>' . htmlspecialchars($loanPurpose) . '</td>';
            echo '<td>' . htmlspecialchars($tenure) . ' months</td>';
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
        $(document).ready(function() {
            // Check if data was fetched from loan_approvals
            var dataFetched = <?php echo json_encode($dataFetched); ?>;
            var userRole = '<?php echo addslashes($userRole); ?>';

            // Show Proceed to Disbursal button if data was fetched
            if (dataFetched) {
                $('#proceedDisbursalBtn').show();
            }

            // Handle button clicks
            $('#proceedDisbursalBtn').click(function() {
                $.ajax({
                    type: 'POST',
                    url: 'update_emi_schedule.php' , // Path to your PHP script for updating emi_schedule data
                    data: $('#emiForm').serialize()+ '&source=EMI.php', // Serialize form data
                    success: function(response) {
                        alert('Loan Disbursed successfully!');
                        window.location.href = 'approved_loans.php';
                    },
                    error: function(xhr, status, error) {
                        alert('Error updating EMI data: ' + error);
                    }
                });
            });

            $('#proceedAdminApprovalBtn').click(function() {
                var tableData = {
                    leadId: $('#leadId').val(),
                    loanAmount: $('#loanAmount').val(),
                    sanctionedAmount: $('#sanctionedamount').val(),
                    tenure: $('#tenure').val(),
                    interestRate: $('#interestRate').val(),
                    emiAmount: <?php echo $emiAmount; ?>, // Ensure this value is set correctly
                    emidate: $('#emidate').val(),
                };

                $.ajax({
                    type: 'POST',
                    url: 'disbursal_approvals.php',
                    data: tableData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.success);
                            window.location.href = 'bm_approvals.php'; // Redirect on success
                        } else if (response.error) {
                            alert('Error: ' + response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred while sending data:', error);
                    }
                });
            });

            $('#proceedBranchManagerBtn').click(function() {
                // Prepare data to be sent to the server
                var tableData = {
                    leadId: $('#leadId').val(),
                    loanAmount: $('#loanAmount').val(),
                    loanPurpose: $('#loanPurpose').val(),
                    tenure: $('#tenure').val(),
                    sanctionedAmount: $('#sanctionedamount').val(),
                    emiAmount: '<?php echo $emiAmount; ?>', // Add EMI amount
                    interestRate: '<?php echo $interestRate; ?>', // Add interest 
                    emidate: $('#emidate').val(), // Add interest rate
                };

                // Send AJAX request to save_loan_data.php
                $.ajax({
                    type: 'POST',
                    url: 'save_loan_data.php', // PHP file to handle the request
                    data: tableData,
                    success: function(response) {
                        alert('Branch Approval Initiated Successfully!');
                        window.location.href = 'bm_approvals.php';
                    },
                    error: function(xhr, status, error) {
                        alert('Error saving data to database: ' + error);
                    }
                });
            });

            // Show buttons when any field is edited
            $('#emiForm input').on('input', function() {
                if (userRole === 'admin') {
                    $('#proceedDisbursalBtn').show();
                    $('#proceedBranchManagerBtn').show();
                } else if (userRole === 'branchmanager') {
                    $('#proceedAdminApprovalBtn').show();
                    $('#proceedDisbursalBtn, #proceedBranchManagerBtn').hide();
                }
            });
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
