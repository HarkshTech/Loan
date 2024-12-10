<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape user inputs to prevent SQL injection
    $srno = $conn->real_escape_string($_POST['srno']);
    $datepicker = $conn->real_escape_string($_POST['datepicker']);
    $case_of = $conn->real_escape_string($_POST['case_of']);
    $loanAmount = $conn->real_escape_string($_POST['loanAmount']);
    $loanPurpose = $conn->real_escape_string($_POST['loanPurpose']);
    
    
    $fullName = $conn->real_escape_string($_POST['fullName']);
    $refby = $conn->real_escape_string($_POST['refby']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $ownership_status = $conn->real_escape_string($_POST['ownership_status']);
    
    
    $co_app = $conn->real_escape_string($_POST['co_app']);
    
    $co_app_name = $conn->real_escape_string($_POST['co_app_name']);
    
    $details2 = $conn->real_escape_string($_POST['details2']);
    $income2 = $conn->real_escape_string($_POST['income2']);
    
    $co_ownership_status = $conn->real_escape_string($_POST['co_ownership_status']);
    $relation = $conn->real_escape_string($_POST['relation']);
    $can_pay_installment = $conn->real_escape_string($_POST['can_pay_installment']);
    $reason_of_loan = $conn->real_escape_string($_POST['reason_of_loan']);
    $observation = $conn->real_escape_string($_POST['observation']);
    
    $accountno = $conn->real_escape_string($_POST['accountno']);
    $ifsc = $conn->real_escape_string($_POST['ifsc']);
    $bname = $conn->real_escape_string($_POST['bname']);
    
    $employer1 = $conn->real_escape_string($_POST['employer1']);
    $income1 = $conn->real_escape_string($_POST['income1']);
    $cs_app = $conn->real_escape_string($_POST['cs_app']);
    $details1 = $conn->real_escape_string($_POST['details1']);//work details for applicant
    
    $contacts = [];
    for ($i = 1; $i <= 10; $i++) {
        $contacts[] = $conn->real_escape_string($_POST["contact$i"]);
    }

    // File upload handling
    $targetDir = "uploads/";
    $filePaths = [];

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    foreach ($_FILES['documents']['tmp_name'] as $key => $tmp_name) {
        $fileName = basename($_FILES['documents']['name'][$key]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        if (move_uploaded_file($_FILES['documents']['tmp_name'][$key], $targetFilePath)) {
            $filePaths[] = $targetFilePath;
        } else {
            echo "Error uploading file: $fileName<br>";
        }
    }

    // Insert data into database
    $sql = "INSERT INTO personalinformation (ID,sno, date, caseof, FullName, refby, Email, PhoneNumber, Address, `cs-app`, `co-app`, `co-app_name`, `details1`,`employer1`, `details2`, `income1`, `income2`, `status1`, `status2`, `relation`, `installment`, `reason`, `observation`, `contact1`, `contact2`, `contact3`, `contact4`, `contact5`, `contact6`, `contact7`, `contact8`, `contact9`, `contact10`, `bank_account_no`,`monthly_income`, `ifsc`, `bank_name`, `loanAmount`, `loanPurpose`)
            VALUES ('','$srno', '$datepicker', '$case_of','$fullName', '$refby', '$email', '$phone', '$address', '$cs_app', '$co_app', '$co_app_name', '$details1','$employer1', '$details2', '$income1', '$income2', '$ownership_status', '$co_ownership_status', '$relation', '$can_pay_installment', '$reason_of_loan', '$observation', '".$contacts[0]."', '".$contacts[1]."', '".$contacts[2]."', '".$contacts[3]."', '".$contacts[4]."', '".$contacts[5]."', '".$contacts[6]."', '".$contacts[7]."', '".$contacts[8]."', '".$contacts[9]."', '$accountno','$income1', '$ifsc', '$bname', '$loanAmount', '$loanPurpose')";
            echo "$sql";

    if ($conn->query($sql) === TRUE) {
        $last_id = $conn->insert_id;

        // Update loandetails table with the last inserted ID
        $sql = "INSERT INTO loandetails (LoanAmount, LoanPurpose, ID)
                VALUES ('$loanAmount', '$loanPurpose', '$last_id')";
        $conn->query($sql);

        $sql = "INSERT INTO employmentdetails (Occupation, Employer, MonthlyIncome)
                VALUES ('$details1', '$employer1', '$income1')";
        $conn->query($sql);

        // Insert file paths into DocumentUploads table
        foreach ($filePaths as $filePath) {
            $filePath = $conn->real_escape_string($filePath);
            $sql = "INSERT INTO documentuploads (LoanApplicationID, DocumentPath)
                    VALUES ('$last_id', '$filePath')";
            $conn->query($sql);
        }

        echo "<script>
                alert('Form submitted successfully!');
                window.location.href = 'borrower.php';
              </script>";
    } else {
        echo "<script>
                alert('Error: " . $conn->error . "');
                window.location.href = 'borrower.php';
              </script>";
    }
}

$conn->close();
?>
