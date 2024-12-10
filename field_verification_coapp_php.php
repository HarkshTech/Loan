<?php
session_start();

if (!isset($_SESSION['username']) || !$_SESSION['role']) {
    // Redirect to the login page or another appropriate page
    header("Location: index.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kolkata');

// Include database connection
include 'config.php';

// Function to sanitize input data
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Directory where you want to save the uploaded files
    $targetDir = "uploads/";

    // Initialize file paths
    $electricityBillPathHome = "";
    $electricityMeterPathHome = "";
    $electricityBillPathBusiness = "";
    $electricityMeterPathBusiness = "";
    
    // Initialize arrays to store paths
    $homeImages = [];
    $homeVideos = [];
    $businessImages = [];
    $businessVideos = [];

    // Check and handle file uploads for Home verification
    if (!empty($_FILES["electricity_bill_home_COAPP"]["name"]) && $_FILES["electricity_bill_home_COAPP"]["error"] == 0) {
        $electricityBillPathHome = $targetDir . basename($_FILES["electricity_bill_home_COAPP"]["name"]);
        if (!move_uploaded_file($_FILES["electricity_bill_home_COAPP"]["tmp_name"], $electricityBillPathHome)) {
            echo "Error moving uploaded file: electricity_bill_home_COAPP";
            exit();
        }
    }

    if (!empty($_FILES["electricity_meter_home_COAPP"]["name"]) && $_FILES["electricity_meter_home_COAPP"]["error"] == 0) {
        $electricityMeterPathHome = $targetDir . basename($_FILES["electricity_meter_home_COAPP"]["name"]);
        if (!move_uploaded_file($_FILES["electricity_meter_home_COAPP"]["tmp_name"], $electricityMeterPathHome)) {
            echo "Error moving uploaded file: electricity_meter_home_COAPP";
            exit();
        }
    }

    // Handle multiple image uploads for Home verification
if (!empty($_FILES['home_images_COAPP']['name'])) {
    foreach ($_FILES['home_images_COAPP']['name'] as $key => $val) {
        if ($_FILES['home_images_COAPP']['error'][$key] == 0) {
            $targetFilePath = $targetDir . basename($_FILES['home_images_COAPP']['name'][$key]);
            if (move_uploaded_file($_FILES['home_images_COAPP']['tmp_name'][$key], $targetFilePath)) {
                $homeImages[] = $targetFilePath;
            } else {
                echo "Error moving uploaded file: " . $_FILES['home_images_COAPP']['name'][$key];
                exit();
            }
        }
    }
}

// Handle multiple video uploads for Home verification
if (!empty($_FILES['home_videos_COAPP']['name'])) {
    foreach ($_FILES['home_videos_COAPP']['name'] as $key => $val) {
        if ($_FILES['home_videos_COAPP']['error'][$key] == 0) {
            $targetFilePath = $targetDir . basename($_FILES['home_videos_COAPP']['name'][$key]);
            if (move_uploaded_file($_FILES['home_videos_COAPP']['tmp_name'][$key], $targetFilePath)) {
                $homeVideos[] = $targetFilePath;
            } else {
                echo "Error moving uploaded file: " . $_FILES['home_videos_COAPP']['name'][$key];
                exit();
            }
        }
    }
}


    // Check and handle file uploads for Business verification
    if (!empty($_FILES["electricity_bill_business_COAPP"]["name"]) && $_FILES["electricity_bill_business_COAPP"]["error"] == 0) {
        $electricityBillPathBusiness = $targetDir . basename($_FILES["electricity_bill_business_COAPP"]["name"]);
        if (!move_uploaded_file($_FILES["electricity_bill_business_COAPP"]["tmp_name"], $electricityBillPathBusiness)) {
            echo "Error moving uploaded file: electricity_bill_business_COAPP";
            exit();
        }
    }

    if (!empty($_FILES["electricity_meter_business_COAPP"]["name"]) && $_FILES["electricity_meter_business_COAPP"]["error"] == 0) {
        $electricityMeterPathBusiness = $targetDir . basename($_FILES["electricity_meter_business_COAPP"]["name"]);
        if (!move_uploaded_file($_FILES["electricity_meter_business_COAPP"]["tmp_name"], $electricityMeterPathBusiness)) {
            echo "Error moving uploaded file: electricity_meter_business_COAPP";
            exit();
        }
    }

    // Handle multiple image uploads for Business verification
if (!empty($_FILES['business_images_COAPP']['name'])) {
    foreach ($_FILES['business_images_COAPP']['name'] as $key => $val) {
        if ($_FILES['business_images_COAPP']['error'][$key] == 0) {
            $targetFilePath = $targetDir . basename($_FILES['business_images_COAPP']['name'][$key]);
            if (move_uploaded_file($_FILES['business_images_COAPP']['tmp_name'][$key], $targetFilePath)) {
                $businessImages[] = $targetFilePath;
            } else {
                echo "Error moving uploaded file: " . $_FILES['business_images_COAPP']['name'][$key];
                exit();
            }
        }
    }
}

// Handle multiple video uploads for Business verification
if (!empty($_FILES['business_videos_COAPP']['name'])) {
    foreach ($_FILES['business_videos_COAPP']['name'] as $key => $val) {
        if ($_FILES['business_videos_COAPP']['error'][$key] == 0) {
            $targetFilePath = $targetDir . basename($_FILES['business_videos_COAPP']['name'][$key]);
            if (move_uploaded_file($_FILES['business_videos_COAPP']['tmp_name'][$key], $targetFilePath)) {
                $businessVideos[] = $targetFilePath;
            } else {
                echo "Error moving uploaded file: " . $_FILES['business_videos_COAPP']['name'][$key];
                exit();
            }
        }
    }
}

    // Fetch geolocation for Home verification
    $verificationGeolocationHome = sanitize($_POST['verificationGeolocation_Home_COAPP'] ?? '');

    // Fetch geolocation for Business verification
    $verificationGeolocationBusiness = sanitize($_POST['verificationGeolocation_Business_COAPP'] ?? '');

    // Check if all required fields are set
    if (isset($_POST['leadID'], $_POST['verifierName_Home_COAPP'], $_POST['verificationStatus_Home_COAPP'], $_POST['verifierName_Business_COAPP'], $_POST['verificationStatus_Business_COAPP'])) {
        // Extract and sanitize form data
        $leadID = sanitize($_POST['leadID']);
        $verifierNameHome = sanitize($_POST['verifierName_Home_COAPP']);
        $verificationStatusHome = sanitize($_POST['verificationStatus_Home_COAPP']);
        $verificationNotesHome = sanitize($_POST['verificationNotes_Home_COAPP']);
        $verifierNameBusiness = sanitize($_POST['verifierName_Business_COAPP']);
        $verificationStatusBusiness = sanitize($_POST['verificationStatus_Business_COAPP']);
        $businessVerificationNotes = sanitize($_POST['businessVerificationNotes_COAPP']);
        
        $choice1 = sanitize($_POST['choice_COAPP']);
        $choice2 = isset($_POST['choice2_COAPP']) && !empty($_POST['choice2_COAPP']) ? sanitize($_POST['choice2_COAPP']) : null;

        // Convert the homeImages and businessImages arrays to JSON format
        // $homeImagesJson = !empty($homeImages) ? json_encode($homeImages) : null;
        // $businessImagesJson = !empty($businessImages) ? json_encode($businessImages) : null;

        // Combine images and videos for Home and Business
        $homeImagesJson = json_encode(array_merge($homeImages, $homeVideos), JSON_UNESCAPED_SLASHES);
        $businessImagesJson = json_encode(array_merge($businessImages, $businessVideos), JSON_UNESCAPED_SLASHES);

        // Insert data into database using prepared statement
        $updateQuery = $conn->prepare("UPDATE VerificationForms 
            SET verifierName_Home_COAPP = ?, 
                verificationStatus_Home_COAPP = ?, 
                homestatus_COAPP = ?, 
                verificationNotes_Home_COAPP = ?, 
                verifierName_Business_COAPP = ?, 
                verificationStatus_Business_COAPP = ?, 
                businessstatus_COAPP = ?, 
                businessVerificationNotes_COAPP = ?, 
                electricity_bill_home_COAPP = ?, 
                electricity_meter_home_COAPP = ?, 
                electricity_bill_business_COAPP = ?, 
                electricity_meter_business_COAPP = ?, 
                verification_geolocation_home_COAPP = ?, 
                verification_geolocation_business_COAPP = ?, 
                image_path_home_COAPP = ?, 
                business_images_COAPP = ?, 
                created_at_COAPP = NOW() 
            WHERE leadID = ?");

        $updateQuery->bind_param("ssssssssssssssssi", $verifierNameHome, $verificationStatusHome, $choice1, $verificationNotesHome, $verifierNameBusiness, $verificationStatusBusiness, $choice2, $businessVerificationNotes, $electricityBillPathHome, $electricityMeterPathHome, $electricityBillPathBusiness, $electricityMeterPathBusiness, $verificationGeolocationHome, $verificationGeolocationBusiness, $homeImagesJson, $businessImagesJson, $leadID);

        $userRole = isset($_SESSION['role']) ? $_SESSION['role'] : '';
        
        if ($updateQuery->execute()) {
            // Redirect based on user role
            $redirectUrl = '';
            switch ($userRole) {
                case 'admin':
                    $redirectUrl = 'verify.php';
                    break;
                case 'branchmanager':
                    $redirectUrl = 'digitalverificationsbm.php';
                    break;
                case 'verifier':
                    $redirectUrl = 'verify.php';
                    break;
            }

            if ($redirectUrl) {
                echo "<script>
                        alert('CO-APPLICANT Verification Documents Successfully Saved!');
                        window.location.href = '$redirectUrl';
                      </script>";

                $update = "UPDATE personalinformation SET StepReached='CO-APP. Field Verification Status Updated' WHERE ID=$leadID";
                $conn->query($update);
                exit();
            }
        } else {
            echo "Error inserting data: " . $conn->error;
        }
    } else {
        echo "Required fields are missing.";
    }
} else {
    echo "Form not submitted.";
}

// Close database connection
$conn->close();
?>
