<?php
// validate.php

// Include your database connection script
include 'config.php';

// Assuming your form fields are named 'aadhar', 'pan', and 'phone'
$aadhar = $_POST['aadhar'];
$pan = $_POST['pan'];
$phone = $_POST['phone'];

// Prepare and execute queries to check if the Aadhar, PAN, or phone already exist in the database
$aadhar_query = "SELECT COUNT(*) as count FROM personalinformation WHERE aadharapplicant = $aadhar AND LoanStatus NOT IN ('Closed', 'ForeClosed', 'Not Interested','Processing','Not Disbursed')";
$pan_query = "SELECT COUNT(*) as count FROM personalinformation WHERE panapplicant = '$pan' AND LoanStatus NOT IN ('Closed', 'ForeClosed', 'Not Interested','Processing','Not Disbursed')";
$phone_query = "SELECT COUNT(*) as count FROM personalinformation WHERE PhoneNumber = '$phone' AND LoanStatus NOT IN ('Closed', 'ForeClosed', 'Not Interested','Processing','Not Disbursed')";

$aadhar_result = mysqli_query($conn, $aadhar_query);
$pan_result = mysqli_query($conn, $pan_query);
$phone_result = mysqli_query($conn, $phone_query);

$aadhar_exists = mysqli_fetch_assoc($aadhar_result)['count'] > 0;
$pan_exists = mysqli_fetch_assoc($pan_result)['count'] > 0;
$phone_exists = mysqli_fetch_assoc($phone_result)['count'] > 0;

// Return the result as JSON
echo json_encode(array(
    "exists" => $aadhar_exists || $pan_exists || $phone_exists,
    "aadhar_exists" => $aadhar_exists,
    "pan_exists" => $pan_exists,
    "phone_exists" => $phone_exists
));

// Close the database connection
mysqli_close($conn);
?>
