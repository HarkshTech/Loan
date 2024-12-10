<?php
include 'config.php';

// Start session and check user role
session_start();
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

if ($userRole !== 'branchmanager') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve POST data and trim any whitespace
    $leadId = isset($_POST['leadId']) ? trim($_POST['leadId']) : '';
    $loanAmount = isset($_POST['loanAmount']) ? trim($_POST['loanAmount']) : '';
    $sanctionedAmount = isset($_POST['sanctionedAmount']) ? trim($_POST['sanctionedAmount']) : '';
    $tenure = isset($_POST['tenure']) ? trim($_POST['tenure']) : '';
    $interestRate = isset($_POST['interestRate']) ? trim($_POST['interestRate']) : '';
    $firstEmiDate = isset($_POST['emidate']) ? trim($_POST['emidate']) : '';
    $emiAmount = isset($_POST['emiAmount']) ? trim($_POST['emiAmount']) : '';

    $dateFormat = 'Y-m-d';
    $dateTime = DateTime::createFromFormat($dateFormat, $firstEmiDate);
    if ($dateTime === false || $dateTime->format($dateFormat) !== $firstEmiDate) {
        $errors[] = 'Invalid date format. Please use YYYY-MM-DD format.';
    }
    $formattedDate = date('Y-m-d', strtotime($firstEmiDate));

    // Initialize an array to hold error messages
    $errors = [];

    // Check which fields are empty and add corresponding messages
    if (empty($leadId)) $errors[] = 'Lead ID is required';
    if (empty($loanAmount)) $errors[] = 'Loan amount is required';
    if (empty($sanctionedAmount)) $errors[] = 'Sanctioned amount is required';
    if (empty($tenure)) $errors[] = 'Tenure is required';
    if (empty($interestRate)) $errors[] = 'Interest rate is required';
    if (empty($firstEmiDate)) $errors[] = 'EMI date is required';
    if (empty($emiAmount)) $errors[] = 'EMI amount is required';

    // If there are errors, return them as a JSON response
    if (!empty($errors)) {
        echo json_encode(['error' => implode(', ', $errors)]);
        exit;
    }

    // Round off EMI amount
    $emiAmount = round($emiAmount); // Rounds to nearest integer, >=0.50 ceils, <0.50 floors

    // Prepare SQL statement
    $sql = "INSERT INTO loan_approvals (leadid, loan_amount, sanctioned_amount, tenure, interest_rate, first_emi_date, emi_amount) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("ssddsss", $leadId, $loanAmount, $sanctionedAmount, $tenure, $interestRate, $formattedDate, $emiAmount);
        
        // Execute statement and handle errors
        if ($stmt->execute()) {
            // Insert notification for admin
            $notificationTitle = 'Branch Manager Submitted Disbursal Request';
            $notificationMessage = "Branch Manager ($username) submitted disbursal approval request for ID ($leadId).";
            
            $notificationSql = "INSERT INTO notifications (title, message, nfor, nby, status, created_at) 
                                VALUES (?, ?, 'admin', 'System', 'unread', NOW())";
            $notificationStmt = $conn->prepare($notificationSql);

            if ($notificationStmt) {
                $notificationStmt->bind_param("ss", $notificationTitle, $notificationMessage);
                
                if (!$notificationStmt->execute()) {
                    echo json_encode(['error' => 'Failed to insert notification: ' . $notificationStmt->error]);
                    exit;
                }

                $notificationStmt->close();
            } else {
                echo json_encode(['error' => 'Failed to prepare notification SQL statement']);
                exit;
            }

            echo json_encode(['success' => 'Approval request submitted successfully']);
        } else {
            echo json_encode(['error' => 'Error executing query: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['error' => 'Failed to prepare SQL statement']);
    }

    $conn->close();
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
