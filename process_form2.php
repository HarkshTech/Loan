<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

$username = $_SESSION['username'];
$role = $_SESSION['role'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape user inputs to prevent SQL injection
    $srno = $conn->real_escape_string($_POST['srno']);
    $datepicker = $conn->real_escape_string($_POST['datepicker']);
    $case_of = $conn->real_escape_string($_POST['case_of']);
    $fullName = $conn->real_escape_string($_POST['fullName']);
    $address = $conn->real_escape_string($_POST['address']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    
    $employer1 = $conn->real_escape_string($_POST['employer1']);
    $income1 = $conn->real_escape_string($_POST['income1']);
    $details1 = $conn->real_escape_string($_POST['details1']);//work details for applicant
    
    $incometype = $conn->real_escape_string($_POST['incometype']);//new new new
    $aadharapplicant= $conn->real_escape_string($_POST['aadhar1']);//new new new
    $panapplicant= $conn->real_escape_string($_POST['pan1']);//new new new
    
    
    
    //CO APP DETAILS START HERE
    $loancompany = $conn->real_escape_string($_POST['loancompany']);
    $cs_app = $conn->real_escape_string($_POST['cs_app']);
    $ownership_status = $conn->real_escape_string($_POST['ownership_status']);
    
    $loanAmount = $conn->real_escape_string($_POST['loanAmount']);
    $loanPurpose = $conn->real_escape_string($_POST['loanPurpose']);
    
    
    
    // $ownership_status = $conn->real_escape_string($_POST['ownership_status']);
    $loancompany2 = $conn->real_escape_string($_POST['loancompany2']);
    $co_app = $conn->real_escape_string($_POST['co_app']);
    
    $co_app_name = $conn->real_escape_string($_POST['co_app_name']);
    
    $details2 = $conn->real_escape_string($_POST['details2']);
    $income2 = $conn->real_escape_string($_POST['income2']);
    
    $co_ownership_status = $conn->real_escape_string($_POST['co_ownership_status']);
    
    $aadharcoapplicant= $conn->real_escape_string($_POST['aadhar2']);//new new new
    $pancoapplicant= $conn->real_escape_string($_POST['pan2']);
    
    
    
    $relation = $conn->real_escape_string($_POST['relation']);
    // $can_pay_installment = $conn->real_escape_string($_POST['can_pay_installment']);
    $reason_of_loan = $conn->real_escape_string($_POST['reason_of_loan']);
    // $observation = $conn->real_escape_string($_POST['observation']);
    
    $accountno = $conn->real_escape_string($_POST['accountno']);
    $ifsc = $conn->real_escape_string($_POST['ifsc']);
    $bname = $conn->real_escape_string($_POST['bname']);
    
    
    $cs_app = $conn->real_escape_string($_POST['cs_app']);
    
    //contact details 
    $name1 = $conn->real_escape_string($_POST["name1"]);
    $relation1 = $conn->real_escape_string($_POST["relation1"]);
    $contact1 = $conn->real_escape_string($_POST["contact1"]);
    
    $name2 = $conn->real_escape_string($_POST["name2"]);
    $relation2 = $conn->real_escape_string($_POST["relation2"]);
    $contact2 = $conn->real_escape_string($_POST["contact2"]);
    
    $name3 = $conn->real_escape_string($_POST["name3"]);
    $relation3 = $conn->real_escape_string($_POST["relation3"]);
    $contact3 = $conn->real_escape_string($_POST["contact3"]);
    
    $name4 = $conn->real_escape_string($_POST["name4"]);
    $relation4 = $conn->real_escape_string($_POST["relation4"]);
    $contact4 = $conn->real_escape_string($_POST["contact4"]);
    
    $name5 = $conn->real_escape_string($_POST["name5"]);
    $relation5 = $conn->real_escape_string($_POST["relation5"]);
    $contact5 = $conn->real_escape_string($_POST["contact5"]);
    
    $name6 = $conn->real_escape_string($_POST["name6"]);
    $relation6 = $conn->real_escape_string($_POST["relation6"]);
    $contact6 = $conn->real_escape_string($_POST["contact6"]);
    
    $name7 = $conn->real_escape_string($_POST["name7"]);
    $relation7 = $conn->real_escape_string($_POST["relation7"]);
    $contact7 = $conn->real_escape_string($_POST["contact7"]);
    
    $name8 = $conn->real_escape_string($_POST["name8"]);
    $relation8 = $conn->real_escape_string($_POST["relation8"]);
    $contact8 = $conn->real_escape_string($_POST["contact8"]);
    
    $name9 = $conn->real_escape_string($_POST["name9"]);
    $relation9 = $conn->real_escape_string($_POST["relation9"]);
    $contact9 = $conn->real_escape_string($_POST["contact9"]);
    
    $name10 = $conn->real_escape_string($_POST["name10"]);
    $relation10 = $conn->real_escape_string($_POST["relation10"]);
    $contact10 = $conn->real_escape_string($_POST["contact10"]);
    
    $remarks = $conn->real_escape_string($_POST["remarks"]);
    
    // $generatedby="Branch Manager";
    if($role==='sales'){
        $generatedby="Self(" . $username . ")";
    }
    elseif($username==='admin'){
        $generatedby="Super Admin";
    }
    elseif($role==='branchmanager'){
        $generatedby="Self(" . $username . ")";
    }
    
    
    // $contacts = [];
    // for ($i = 1; $i <= 10; $i++) {
    //     $contacts[] = $conn->real_escape_string($_POST["contact$i"]);
    // }

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
    $sql = "INSERT INTO personalinformation (date, caseof, FullName, Email, PhoneNumber,aadharapplicant, panapplicant ,aadharcoapplicant, pancoapplicant, Address,`loancompanyapp`, `cs-app`,`loancompanycoapp`, `co-app`, `co-app_name`, `details1`,`employer1`, `details2`, `income1`,`incometype`,`income2`, `status1`, `status2`, `relation`, `reason`,`name1`,`relation1`, `contact1`,`name2`,`relation2`, `contact2`, `name3`,`relation3`, `contact3`, `name4`,`relation4`, `contact4`, `name5`,`relation5`, `contact5`, `name6`,`relation6`, `contact6`, `name7`,`relation7`, `contact7`, `name8`,`relation8`, `contact8`, `name9`,`relation9`, `contact9`, `name10`,`relation10`, `contact10`, `bank_account_no`,`monthly_income`, `ifsc`, `bank_name`, `LoanAmount`, `LoanPurpose`,`remarks`,`generatedby`,`StepReached`,`LoanStatus`)
            VALUES ('$datepicker', '$case_of','$fullName', '$email', '$phone','$aadharapplicant','$panapplicant','$aadharcoapplicant ','$pancoapplicant','$address','$loancompany', '$cs_app','$loancompany2', '$co_app', '$co_app_name', '$details1','$employer1', '$details2', '$income1','$incometype', '$income2', '$ownership_status', '$co_ownership_status', '$relation','$reason_of_loan','$name1','$relation1','$contact1','$name2','$relation2','$contact2','$name3','$relation3','$contact3','$name4','$relation4','$contact4','$name5','$relation5','$contact5','$name6','$relation6','$contact6','$name7','$relation7','$contact7','$name8','$relation8','$contact8','$name9','$relation9','$contact9','$name10','$relation10','$contact10', '$accountno','$income1', '$ifsc', '$bname', '$loanAmount', '$loanPurpose','$remarks','$generatedby','Form Submission Done','Processing')";


    if ($conn->query($sql) === TRUE) {
        $last_id = $conn->insert_id;
        
        $updateSql = "UPDATE personalinformation SET sno = $last_id WHERE ID = $last_id";
        $conn->query($updateSql);

        // Update loandetails table with the last inserted ID
        $sql = "INSERT INTO loandetails (LoanAmount, LoanPurpose, ID)
                VALUES ('$loanAmount', '$loanPurpose', '$last_id')";
        $conn->query($sql);

        $sql = "INSERT INTO employmentdetails (ID,Occupation, Employer, MonthlyIncome)
                VALUES ('$last_id','$details1', '$employer1', '$income1')";
        $conn->query($sql);

        // Insert file paths into DocumentUploads table
        foreach ($filePaths as $filePath) {
            $filePath = $conn->real_escape_string($filePath);
            $sql = "INSERT INTO documentuploads (LoanApplicationID, DocumentPath)
                    VALUES ('$last_id', '$filePath')";
            $conn->query($sql);
        }
        
        // Insert notifications
        $username = $_SESSION['username'];
        $notificationTitle = "New Form Submitted by $username";
        $notifications = [
            ['nfor' => 'admin', 'nby' => 'System'],
            ['nfor' => 'branchmanager', 'nby' => 'System']
        ];

        foreach ($notifications as $notification) {
            $sql = "INSERT INTO notifications (title, message, nfor, nby, status, created_at)
                    VALUES ('$notificationTitle', 'A new form has been submitted by $username.', '{$notification['nfor']}', '{$notification['nby']}', 'unread', NOW())";
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
