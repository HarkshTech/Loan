<?php
session_start();

// Redirect if the user is not authenticated
if (!isset($_SESSION['username']) || !$_SESSION['role']) {
    header("Location: index.php");
    exit();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kolkata');

// Include database connection
include 'config.php';

// Sanitize input function
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Upload directory
$targetDir = "uploads/";

// Allowed MIME types for uploads
$allowedTypes = [
    'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp',
    'image/tiff', 'image/svg+xml', 'image/x-icon', 'image/avif', 'image/jfif',
    'video/mp4', 'video/avi', 'video/mov', 'video/mpeg', 'video/quicktime',
    'video/x-msvideo', 'video/x-ms-wmv', 'video/webm', 'video/ogg',
    'video/3gpp', 'video/3gpp2', 'video/x-flv'
];

// Initialize file paths and arrays
$electricityBillPathHome = $electricityMeterPathHome = "";
$electricityBillPathBusiness = $electricityMeterPathBusiness = "";
$homeFiles = $businessFiles = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Function to handle file uploads
    function handleFileUpload($file, $targetDir) {
        $filePath = $targetDir . basename($file["name"]);
        if (move_uploaded_file($file["tmp_name"], $filePath)) {
            return $filePath;
        } else {
            throw new Exception("Error moving uploaded file: " . $file["name"]);
        }
    }

    try {
        // Upload individual files
        if (!empty($_FILES["electricity_bill_home"]["name"])) {
            $electricityBillPathHome = handleFileUpload($_FILES["electricity_bill_home"], $targetDir);
        }
        if (!empty($_FILES["electricity_meter_home"]["name"])) {
            $electricityMeterPathHome = handleFileUpload($_FILES["electricity_meter_home"], $targetDir);
        }
        if (!empty($_FILES["electricity_bill_business"]["name"])) {
            $electricityBillPathBusiness = handleFileUpload($_FILES["electricity_bill_business"], $targetDir);
        }
        if (!empty($_FILES["electricity_meter_business"]["name"])) {
            $electricityMeterPathBusiness = handleFileUpload($_FILES["electricity_meter_business"], $targetDir);
        }

        // Handle multiple uploads for home and business files
        foreach (['home_files' => &$homeFiles, 'business_files' => &$businessFiles] as $key => &$filesArray) {
            if (!empty($_FILES[$key]['name'])) {
                foreach ($_FILES[$key]['name'] as $index => $fileName) {
                    $fileType = $_FILES[$key]['type'][$index];
                    $errorCode = $_FILES[$key]['error'][$index];

                    if ($errorCode === UPLOAD_ERR_OK && in_array($fileType, $allowedTypes)) {
                        $filesArray[] = handleFileUpload([
                            "name" => $fileName,
                            "tmp_name" => $_FILES[$key]['tmp_name'][$index]
                        ], $targetDir);
                    } elseif ($errorCode !== UPLOAD_ERR_NO_FILE) {
                        throw new Exception("File upload error for $fileName: Code $errorCode");
                    }
                }
            }
        }

        // Get sanitized form data
        $leadID = sanitize($_POST['leadID']);
        $verifierNameHome = sanitize($_POST['verifierName_Home']);
        $verificationStatusHome = sanitize($_POST['verificationStatus_Home']);
        $verificationNotesHome = sanitize($_POST['verificationNotes_Home']);
        $verifierNameBusiness = sanitize($_POST['verifierName_Business']);
        $verificationStatusBusiness = sanitize($_POST['verificationStatus_Business']);
        $businessVerificationNotes = sanitize($_POST['businessVerificationNotes']);
        $verificationGeolocationHome = sanitize($_POST['verificationGeolocation_Home'] ?? '');
        $verificationGeolocationBusiness = sanitize($_POST['verificationGeolocation_Business'] ?? '');
        $choice1 = $_POST['choice'];
        $choice2 = $_POST['choice2'] ?? null;

        // JSON encode file arrays
        $homeFilesJson = json_encode($homeFiles);
        $businessFilesJson = json_encode($businessFiles);

        // Prepare and execute the database insert
        $insertQuery = $conn->prepare("
            INSERT INTO VerificationForms (
                leadID, verifierName_Home, verificationStatus_Home, homestatus, 
                verificationNotes_Home, verifierName_Business, verificationStatus_Business, 
                businessstatus, businessVerificationNotes, electricity_bill_home, 
                electricity_meter_home, electricity_bill_business, electricity_meter_business, 
                verification_geolocation_home, verification_geolocation_business, image_path_home, 
                business_images, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $insertQuery->bind_param(
            "issssssssssssssss",
            $leadID, $verifierNameHome, $verificationStatusHome, $choice1,
            $verificationNotesHome, $verifierNameBusiness, $verificationStatusBusiness, $choice2,
            $businessVerificationNotes, $electricityBillPathHome, $electricityMeterPathHome,
            $electricityBillPathBusiness, $electricityMeterPathBusiness,
            $verificationGeolocationHome, $verificationGeolocationBusiness, $homeFilesJson, $businessFilesJson
        );

        if ($insertQuery->execute()) {
            // Update personal information
            $conn->query("UPDATE personalinformation SET StepReached='Field Verification Status Updated' WHERE ID=$leadID");

            // Redirect based on user role
            $redirectUrl = match ($_SESSION['role']) {
                'admin', 'verifier' => 'verify.php',
                'branchmanager' => 'digitalverificationsbm.php',
                default => 'index.php',
            };

            echo "<script>
                    alert('Form data saved successfully!');
                    window.location.href = '$redirectUrl';
                  </script>";
            exit();
        } else {
            throw new Exception("Error inserting data: " . $conn->error);
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        exit();
    }
} else {
    echo "Form not submitted.";
}

// Close the database connection
$conn->close();
?>
