
<?php
include 'config.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Escape user inputs for security
$sr_no = $conn->real_escape_string($_POST['srno']);
$date = $conn->real_escape_string($_POST['datepicker']);
$photo = $conn->real_escape_string($_POST['photo']);
$amount = $conn->real_escape_string($_POST['amount']);
$type = $conn->real_escape_string($_POST['type']);
$name = $conn->real_escape_string($_POST['name']);
$refby = $conn->real_escape_string($_POST['refby']);
$address = $conn->real_escape_string($_POST['address']);
$cs_app = $conn->real_escape_string($_POST['cs_app']);
$co_app = $conn->real_escape_string($_POST['co_app']);
$app_name = $conn->real_escape_string($_POST['app_name']);

$work_details = $conn->real_escape_string($_POST['work_details']);
$co_work_details = $conn->real_escape_string($_POST['co_work_details']);
$ownership_status = $conn->real_escape_string($_POST['ownership_status']);
$co_ownership_status = $conn->real_escape_string($_POST['co_ownership_status']);
$relation = $conn->real_escape_string($_POST['relation']);
$monthly_income = $conn->real_escape_string($_POST['monthly_income']);
$can_pay_installment = $conn->real_escape_string($_POST['can_pay_installment']);

$reason_of_loan = $conn->real_escape_string($_POST['reason_of_loan']);
$observation = $conn->real_escape_string($_POST['observation']);
$contact1 = $conn->real_escape_string($_POST['contact1']);
$contact2 = $conn->real_escape_string($_POST['contact2']);
$contact3 = $conn->real_escape_string($_POST['contact3']);
$contact4 = $conn->real_escape_string($_POST['contact4']);
$contact5 = $conn->real_escape_string($_POST['contact5']);
$contact6 = $conn->real_escape_string($_POST['contact6']);
$risk_assessment = $conn->real_escape_string($_POST['risk_assessment']);
$adhar_a = $conn->real_escape_string($_POST['adhar_a']);
$pan_a = $conn->real_escape_string($_POST['pan_a']);
$cheque_a = $conn->real_escape_string($_POST['cheque_a']);
$adhar_n = $conn->real_escape_string($_POST['adhar_n']);
$pan_n = $conn->real_escape_string($_POST['pan_n']);
$cheque_n = $conn->real_escape_string($_POST['cheque_n']);
$registree = $conn->real_escape_string($_POST['registree']);
$fard = $conn->real_escape_string($_POST['fard']);
$stamp_paper = $conn->real_escape_string($_POST['stamp_paper']);
$ac_statement = $conn->real_escape_string($_POST['ac_statement']);
$old_registree = $conn->real_escape_string($_POST['old_registree']);
$electricity_bill = $conn->real_escape_string($_POST['electricity_bill']);
$home_verification_date = $conn->real_escape_string($_POST['home_verification_date']);
$business_verification_date = $conn->real_escape_string($_POST['business_verification_date']);
$changes_verification_date = $conn->real_escape_string($_POST['changes_verification_date']);
$home_verification_officer1 = $conn->real_escape_string($_POST['home_verification_officer1']);
$business_verification_officer1 = $conn->real_escape_string($_POST['business_verification_officer1']);
$changes_verification_officer1 = $conn->real_escape_string($_POST['changes_verification_officer1']);

$home_verification_date2 = $conn->real_escape_string($_POST['home_verification_date2']);
$business_verification_date2 = $conn->real_escape_string($_POST['business_verification_date2']);
$changes_verification_date2 = $conn->real_escape_string($_POST['changes_verification_date2']);
$home_verification_officer2 = $conn->real_escape_string($_POST['home_verification_officer2']);
$business_verification_officer2 = $conn->real_escape_string($_POST['business_verification_officer2']);
$changes_verification_officer2 = $conn->real_escape_string($_POST['changes_verification_officer2']);



$home_verification_date3 = $conn->real_escape_string($_POST['home_verification_date3']);
$business_verification_date3 = $conn->real_escape_string($_POST['business_verification_date3']);
$changes_verification_date3 = $conn->real_escape_string($_POST['changes_verification_date3']);
$home_verification_officer3 = $conn->real_escape_string($_POST['home_verification_officer3']);
$business_verification_officer3 = $conn->real_escape_string($_POST['business_verification_officer3']);
$changes_verification_officer3 = $conn->real_escape_string($_POST['changes_verification_officer3']);

$remarks = $conn->real_escape_string($_POST['remarks']);

$remarks2 = $conn->real_escape_string($_POST['remarks2']);


$installment_amount = $conn->real_escape_string($_POST['installment_amount']);
$tenure_finalized = $conn->real_escape_string($_POST['tenure_finalized']);




// Attempt insert query execution
$sql = "INSERT INTO loan_applications (srno, datepicker,photo,amount,type,name,refby,address,cs_app,co_app, app_name  work_details,co_work_details,ownership_status,co_ownership_status,relation,monthly_income,can_pay_installment,reason_of_loan,observation,contact1,contact2,contact3,contact4,contact5,contact6,risk_assessment,adhar_a,pan_a,cheque_a,adhar_n,pan_n,cheque_n,registree,fard,stamp_paper,ac_statement,old_registree,electricity_bill,home_verification_date1,business_verification_date1,changes_verification_date1,home_verification_officer1,business_verification_officer1,changes_verification_officer1,home_verification_date2,business_verification_date2,changes_verification_date2,home_verification_officer2,business_verification_officer2,changes_verification_officer2,home_verification_date3,business_verification_date3,changes_verification_date3,home_verification_officer3,business_verification_officer3,changes_verification_officer3,remarks,additional_details,installment_amount,tenure_finalized) VALUES ( ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss", $sr_no, $date, $photo, $amount, $type,$name,$refby,$address,$cs_app,$co_app , $work_details,$co_work_details,$ownership_status,$co_ownership_status,$relation,$monthly_income,$can_pay_installment,$reason_of_loan,$observation,$contact1,$contact2,$contact3 ,$contact4,$contact5,$contact6,$risk_assessment,$adhar_a,$pan_a,$cheque_a,$adhar_n,$pan_n,$cheque_n,$registree,$fard,$stamp_paper,$ac_statement, $old_registree,$electricity_bill,$home_verification_date,$business_verification_date,$changes_verification_date,$home_verification_officer1,$business_verification_officer1,$changes_verification_officer1,$home_verification_date2,$business_verification_date2,$changes_verification_date2,$home_verification_officer2,$business_verification_officer2,$changes_verification_officer2,$home_verification_date3,$business_verification_date3,$changes_verification_date3,$home_verification_officer3,$business_verification_officer3,$changes_verification_officer3,$remarks,$remarks2,$installment_amount,$tenure_finalized);

if ($stmt->execute()) {
    echo "Records inserted successfully.";
} else {
    echo "Failed to submit the form. Please try again later.";
    // Optionally, you can also log the error for debugging purposes
    error_log("Error: " . $sql . "\n" . $conn->error);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>




