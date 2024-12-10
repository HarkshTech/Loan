<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'branchmanager' && $_SESSION['role'] !== 'accounts')) {
    header("Location: index.php");
    exit();
}

require('fpdf/fpdf.php');
$user = $_SESSION['role'];
switch ($user) {
    case 'admin':
        $redirecturl = 'dashboard.php';
        break;
    case 'branchmanager':
        $redirecturl = 'branchmanager.php';
        break;
    case 'accounts':
        $redirecturl = 'dashboardapproved_loans.php';
        break;
}

include 'config.php';

// Fetch all users
$users = [];
$result = $conn->query("SELECT es.*, pi.FullName FROM emi_schedule es JOIN personalinformation pi ON es.LeadID = pi.ID");
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Fetch details of a specific loan if user is selected
$loanDetails = null;
$payments = [];
if (isset($_GET['lead_id'])) {
    $leadId = $_GET['lead_id'];
    $stmt = $conn->prepare("SELECT es.*, pi.FullName, es.datestarted FROM emi_schedule es JOIN personalinformation pi ON es.LeadID = pi.ID WHERE es.LeadID = ?");
    $stmt->bind_param("i", $leadId);
    $stmt->execute();
    $loanDetails = $stmt->get_result()->fetch_assoc();

    $paymentStmt = $conn->prepare("SELECT * FROM emi_payments WHERE LeadID = ? AND bmapproval='1' AND superapproval='1'");
    $paymentStmt->bind_param("i", $leadId);
    $paymentStmt->execute();
    $payments = $paymentStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $dateStarted = strtotime($loanDetails['datestarted']); // Ensure $dateStarted is a timestamp
$dayStarted = date('d', $dateStarted); // Extract day of the month from $dateStarted
$emiDueDates = [];
$totalEMIs = $loanDetails['TotalEMIs']; // Total number of EMIs

// Add the first EMI due date
$firstEmiDate = date('Y-m-d', $dateStarted);
$emiDueDates[] = $firstEmiDate;

// Generate EMI due dates
for ($i = 1; $i < $totalEMIs; $i++) {
    // Calculate the next due date by adding $i months to $dateStarted
    $nextDueDate = strtotime("+$i month", $dateStarted);

    // Adjust to the same day of the month or the month's last day if necessary
    $adjustedDueDate = date('Y-m-d', mktime(
        0, 0, 0,
        date('m', $nextDueDate),
        min($dayStarted, date('t', $nextDueDate)), // Handle months with fewer days
        date('Y', $nextDueDate)
    ));

    $emiDueDates[] = $adjustedDueDate;
}
}

function calculateTotalInterestComponent($principal, $annualInterestRate, $tenureInYears) {
    return $principal * $annualInterestRate * $tenureInYears / 100;
}

function generatePDF($loanDetails, $emiDueDates, $payments, $action = 'download') {
    $principal = $loanDetails['sanctionedAmount'];
    $annualInterestRate = $loanDetails['interestrate'];
    $tenureInMonths = $loanDetails['TotalEMIs'];
    $emi = $loanDetails['EMIAmount'];
    $tenureInYears = $tenureInMonths / 12;
    $paidEMICount = $loanDetails['PaidEMIs'];
    $leadId = $loanDetails['LeadID'];

    $totalInterest = calculateTotalInterestComponent($principal, $annualInterestRate, $tenureInYears);
    $interestComponentPerMonth = $totalInterest / $tenureInMonths;
    $currentDate = date('Y-m-d');

    // Initialize PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Header and Loan Details
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Generated on: ' . $currentDate, 0, 1, 'R');
    $pdf->Cell(40, 10, 'Payment Schedule');
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 10);

    // Loan Details
    $pdf->Cell(40, 10, 'Name: ' . $loanDetails['FullName']);
    $pdf->Ln();
    $pdf->Cell(40, 10, 'Lead ID: ' . $leadId);
    $pdf->Ln();
    $pdf->Cell(40, 10, 'Loan Amount (Sanctioned Principal): ' . $principal);
    $pdf->Ln();
    $pdf->Cell(40, 10, 'Annual Interest Rate: ' . $annualInterestRate . '%');
    $pdf->Ln();
    $pdf->Cell(40, 10, 'Tenure: ' . $tenureInYears . ' years');
    $pdf->Ln();
    $pdf->Cell(40, 10, 'EMI: ' . $emi);
    $pdf->Ln();
    $pdf->Cell(40, 10, 'Total Interest: ' . $totalInterest);
    $pdf->Ln();
    $pdf->Cell(40, 10, 'Total EMIs: ' . $tenureInMonths);
    $pdf->Ln();
    $pdf->Cell(40, 10, 'Paid EMIs: ' . $paidEMICount);
    $pdf->Ln();

    // First Table (EMI Schedule)
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 10, 'Month', 1);
    $pdf->Cell(25, 10, 'EMI', 1);
    $pdf->Cell(35, 10, 'Principal Component', 1);
    $pdf->Cell(35, 10, 'Interest Component', 1);
    $pdf->Cell(45, 10, 'Outstanding Principal', 1);
    $pdf->Cell(30, 10, 'Due Date', 1);
    $pdf->Ln();

    // EMI Schedule Rows
    $pdf->SetFont('Arial', '', 10);
    $outstandingPrincipal = $principal;
    $index = 0;
    for ($i = 1; $i <= $tenureInMonths; $i++) {
        $interestComponent = $interestComponentPerMonth;

        if ($i == $tenureInMonths) {
            $principalComponent = $outstandingPrincipal;
            $emi = $principalComponent + $interestComponent;
            $outstandingPrincipal = 0;
        } else {
            $principalComponent = round($emi - $interestComponent, 2);
            $outstandingPrincipal = round($outstandingPrincipal - $principalComponent, 2);
        }

        $pdf->Cell(20, 10, $i, 1);
        $pdf->Cell(25, 10, round($emi, 2), 1);
        $pdf->Cell(35, 10, $principalComponent, 1);
        $pdf->Cell(35, 10, $interestComponent, 1);
        $pdf->Cell(45, 10, $outstandingPrincipal > 0 ? $outstandingPrincipal : 0, 1);
        $pdf->Cell(30, 10, isset($emiDueDates[$index]) ? $emiDueDates[$index++] : '', 1);
        $pdf->Ln();
    }

    // Totals Row
    $totalPrincipalPaid = $principal - $outstandingPrincipal;
    $totalInterestPaid = $totalInterest;
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 10, 'Total', 1);
    $pdf->Cell(25, 10, '', 1);
    $pdf->Cell(35, 10, $totalPrincipalPaid, 1);
    $pdf->Cell(35, 10, $totalInterestPaid, 1);
    $pdf->Cell(45, 10, '', 1);
    $pdf->Cell(30, 10, '', 1);
    $pdf->Ln();

     // Payments Made Table
     $pdf->AddPage();
     $pdf->SetFont('Arial', 'B', 12);
     $pdf->Cell(40, 10, 'Payments Made');
     $pdf->Ln();
     $pdf->SetFont('Arial', '', 10);
 
     // Headers for Payments Table
     $pdf->SetFont('Arial', 'B', 10);
     $pdf->Cell(15, 10, 'PID', 1); // Renamed PaymentID to PID
     $pdf->Cell(30, 10, 'PaymentDate', 1);
     $pdf->Cell(30, 10, 'EMIAmount', 1);
     $pdf->Cell(45, 10, 'PaymentType', 1); // Allocate enough space for PaymentType
     $pdf->Cell(20, 10, 'OverdueDays', 1);
     $pdf->Cell(15, 10, 'Status', 1);
     $pdf->Cell(25, 10, 'Receiver', 1);
     $pdf->Ln();
 
     // Payments Table Rows
     foreach ($payments as $payment) {
         // Determine the maximum number of lines required for the PaymentType
         $maxLines = max(
             $pdf->GetStringWidth($payment['PaymentType']) / 45,
             1
         );
         $rowHeight = max(5 * ceil($maxLines), 10); // Adjust the row height based on content
 
         // Payment details for each column
         $pdf->Cell(15, $rowHeight, $payment['PaymentID'], 1);
         $pdf->Cell(30, $rowHeight, $payment['PaymentDate'], 1);
         $pdf->Cell(30, $rowHeight, $payment['EMIAmount'], 1);
         
         // Output PaymentType with MultiCell for wrapping
         $xPos = $pdf->GetX();
         $yPos = $pdf->GetY();
         $pdf->MultiCell(45, 5, $payment['PaymentType'], 1);
         $pdf->SetXY($xPos + 45, $yPos);
 
         // Add the remaining cells for OverdueDays, Status, and Receiver
         $pdf->Cell(20, $rowHeight, $payment['OverdueDays'], 1);
         $pdf->Cell(15, $rowHeight, $payment['Status'], 1);
         $pdf->Cell(25, $rowHeight, $payment['PaymentReceiver'], 1);
         $pdf->Ln();
     }
 
     // Output the PDF based on the specified action
     if ($action === 'download') {
         $pdf->Output('D', 'PaymentSchedule.pdf');
     } elseif ($action === 'view') {
         $pdf->Output('I', 'PaymentSchedule.pdf');
     }
}





// Handle PDF generation based on query parameters
if (isset($_GET['download']) && $loanDetails) {
    generatePDF($loanDetails, $emiDueDates, $payments, 'download');
    exit();
}

if (isset($_GET['view']) && $loanDetails) {
    generatePDF($loanDetails, $emiDueDates, $payments, 'view');
    exit();
}
?>
<?php 
switch ($user) {
    case 'admin':
        include "leftside.php";
        break;
    case 'branchmanager':
        include "leftsidebranch.php";
        break;
    case 'accounts':
        include "leftbaraccounts.php";
        break;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Loan EMI Schedule</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #0066cc;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .button:hover {
            background: #005bb5;
        }
        .loan-details {
            margin-top: 20px;
        }
        .modal-body p {
            margin: 5px 0;
        }
    </style>
    <link rel="shortcut icon" href="assets/images/logo.png">
    <link href="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="assets/css/preloader.min.css" type="text/css" />
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="container" style="margin-top:80px;">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Welcome !</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?php echo $redirecturl; ?>">Dashboard</a></li>
                        <li class="breadcrumb-item">Payment Schedules</li>
                        <li class="breadcrumb-item active">Welcome !</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <h1>Loan EMI Schedule</h1>
    <h2>Users</h2>
    
    <input type="text" id="search-input" class="form-control" placeholder="Search by Full Name or Lead ID">
    <table id="users-table">
        <thead>
            <tr>
                <th>Lead ID</th>
                <th>Full Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['LeadID']); ?></td>
                    <td><?php echo htmlspecialchars($user['FullName']); ?></td>
                    <td>
                        <button class="btn btn-info" data-toggle="modal" data-target="#loanDetailsModal" 
                                data-lead-id="<?php echo $user['LeadID']; ?>"
                                data-name="<?php echo htmlspecialchars($user['FullName']); ?>"
                                data-amount="<?php echo htmlspecialchars($user['sanctionedAmount']); ?>"
                                data-purposed="<?php echo htmlspecialchars($user['LoanPurpose']); ?>"
                                data-emis="<?php echo htmlspecialchars($user['TotalEMIs']); ?>"
                                data-paid="<?php echo htmlspecialchars($user['PaidEMIs']); ?>"
                                data-last-payment="<?php echo htmlspecialchars($user['LastPaymentDate']); ?>"
                                data-next-payment="<?php echo htmlspecialchars($user['NextPaymentDate']); ?>"
                                data-emi="<?php echo htmlspecialchars($user['EMIAmount']); ?>"
                                data-status="<?php echo htmlspecialchars($user['Status']); ?>"
                                data-overdue="<?php echo htmlspecialchars($user['overdue_days']); ?>"
                                data-partial="<?php echo htmlspecialchars($user['PartialPayment']); ?>"
                                data-penalty="<?php echo htmlspecialchars($user['PenaltyAmount']); ?>"
                                data-interest="<?php echo htmlspecialchars($user['interestrate']); ?>">
                            View Loan Details
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Modal -->
    <div class="modal fade" id="loanDetailsModal" tabindex="-1" role="dialog" aria-labelledby="loanDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loanDetailsModalLabel">Loan Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Name:</strong> <span id="modal-name"></span></p>
                    <p><strong>Lead ID:</strong> <span id="modal-lead-id"></span></p>
                    <p><strong>Loan Amount:</strong> <span id="modal-amount"></span></p>
                    <p><strong>Loan Purpose:</strong> <span id="modal-purpose"></span></p>
                    <p><strong>Total EMIs:</strong> <span id="modal-emis"></span></p>
                    <p><strong>Paid EMIs:</strong> <span id="modal-paid"></span></p>
                    <p><strong>Last Payment Date:</strong> <span id="modal-last-payment"></span></p>
                    <p><strong>Next Payment Date:</strong> <span id="modal-next-payment"></span></p>
                    <p><strong>EMI Amount:</strong> <span id="modal-emi"></span></p>
                    <p><strong>Status:</strong> <span id="modal-status"></span></p>
                    <p><strong>Overdue Days:</strong> <span id="modal-overdue"></span></p>
                    <p><strong>Partial Payment:</strong> <span id="modal-partial"></span></p>
                    <p><strong>Penalty Amount:</strong> <span id="modal-penalty"></span></p>
                    <p><strong>Interest Rate:</strong> <span id="modal-interest"></span>%</p>
                    <a class="btn btn-primary" id="pdf-download" href="#">Download EMI Schedule as PDF</a>
                    <button class="btn btn-secondary" id="pdf-preview">Preview EMI Schedule as PDF</button>
                </div>
            </div>
        </div>
    </div>
    <!-- PDF Preview Modal -->
<div class="modal fade" id="pdfPreviewModal" tabindex="-1" role="dialog" aria-labelledby="pdfPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">PDF Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <iframe id="pdf-preview-iframe" src="" width="100%" height="600px"></iframe>
            </div>
        </div>
    </div>
</div>


</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Search functionality
        $('#search-input').on('keyup', function() {
            var query = $(this).val().toLowerCase();
            $('#users-table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(query) > -1);
            });
        });

        // When the loan details modal is shown
        $('#loanDetailsModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); 
            var leadId = button.data('lead-id');
            var name = button.data('name');
            var amount = button.data('amount');
            var purpose = button.data('purposed');
            var emis = button.data('emis');
            var paid = button.data('paid');
            var lastPayment = button.data('last-payment');
            var nextPayment = button.data('next-payment');
            var emi = button.data('emi');
            var status = button.data('status');
            var overdue = button.data('overdue');
            var partial = button.data('partial');
            var penalty = button.data('penalty');
            var interest = button.data('interest');

            var modal = $(this);
            modal.find('#modal-name').text(name);
            modal.find('#modal-lead-id').text(leadId);
            modal.find('#modal-amount').text(amount);
            modal.find('#modal-purpose').text(purpose);
            modal.find('#modal-emis').text(emis);
            modal.find('#modal-paid').text(paid);
            modal.find('#modal-last-payment').text(lastPayment);
            modal.find('#modal-next-payment').text(nextPayment);
            modal.find('#modal-emi').text(emi);
            modal.find('#modal-status').text(status);
            modal.find('#modal-overdue').text(overdue);
            modal.find('#modal-partial').text(partial);
            modal.find('#modal-penalty').text(penalty);
            modal.find('#modal-interest').text(interest);

            // Update the PDF download link and store the view link
            var pdfDownloadLink = "paymentschedule.php?lead_id=" + leadId + "&download=1";
            var pdfViewLink = "paymentschedule.php?lead_id=" + leadId + "&view=1";
            modal.find('#pdf-download').attr('href', pdfDownloadLink);
            modal.find('#pdf-preview').data('pdf-link', pdfViewLink);
        });

        // Custom function to close any modal by its ID
        function closeModal(modalId) {
            $(modalId).modal('hide'); // Hide the modal programmatically
        }

        // Handle the PDF preview button click
        $('#pdf-preview').on('click', function() {
            var pdfLink = $(this).data('pdf-link');

            // Close the loan details modal
            closeModal('#loanDetailsModal');

            // Open the PDF preview in a new window (or you can handle it differently as needed)
            window.open(pdfLink, '_blank'); // Open the PDF in a new tab/window
        });
    });
</script>


</body>
</html>
