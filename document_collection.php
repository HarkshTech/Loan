<?php
session_start();
$username = $_SESSION['username'];
$role = $_SESSION['role'];
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

$redirecturl='';
if($role=='admin'){
    $redirecturl='dashboard.php';
}
elseif($role=='branchmanager'){
    $redirecturl='branchmanager.php';
}
else{
    $redirecturl='dashboardsales.php';
}

// Function to check if all documents are uploaded successfully
function areAllDocumentsUploaded($statusFields)
{
    foreach ($statusFields as $status) {
        if ($status !== 'Pending') {
            return false;
        }
    }
    return true;
}

// Check if LeadID is provided in the URL
if (isset($_GET['id'])) {
    $leadID = $conn->real_escape_string($_GET['id']);

    // Check if the lead exists and is a hot lead
    $checkHotLead = $conn->query("SELECT * FROM personalinformation WHERE ID = '$leadID' ");

    if ($checkHotLead->num_rows > 0) {
        // Fetch lead information
        $leadInfo = $checkHotLead->fetch_assoc();

        // Define display names for documents
        $documentDisplayNames = [
            'Document1' => 'Aadhar Card (Applicant)',
            'Document2' => 'Pan Card (Applicant)',
            'Document3' => '3 Cheque (Applicant)',
            'Document4' => 'Aadhar Card (Nominee)',
            'Document5' => 'Pan Card (Nominee)',
            'Document6' => '3 Cheque (Nominee)',
            'Document7' => 'Registree',
            'Document8' => 'Fard',
            'Document9' => 'Stamp Paper',
            'Document10' => 'A/C Statement',
            'Document11' => 'Old Registree',
            'Document12' => 'Electricity Bill',
            'Document13' => 'CIBIL Report',
            // Add more display names for additional documents if needed
        ];

        // Get document collection for the lead, if available
        $documentCollection = $conn->query("SELECT * FROM documentcollection WHERE LeadID = '$leadID'");
        $docCollectionData = ($documentCollection->num_rows > 0) ? $documentCollection->fetch_assoc() : [];

        // Process form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $uploadDir = 'uploads/'; // Directory where images will be uploaded
            $allowedTypes = [
                'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'webp', 'svg', 'heic', 'ico', 'jfif',  // Image formats
                'pdf',              // PDF
                'doc', 'docx',       // Word Documents
                'xls', 'xlsx',       // Excel Spreadsheets
                'ppt', 'pptx',       // PowerPoint Presentations
                'txt',               // Text Files
                'csv',               // CSV Files
                'zip'                // ZIP Archives
            ];
            // Allowed image file types
 // Allowed image file types

            // Initialize image names and status as empty arrays
            $imagePaths = [];
            $statusFields = [];

            // Process each document upload field
            for ($i = 1; $i <= 13; $i++) {
                $key = 'Document' . $i;
                $fileName = $_FILES[$key]['name'];
                $fileTmpName = $_FILES[$key]['tmp_name'];
                $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (!empty($fileName) && in_array($fileType, $allowedTypes)) {
                    $newFileName = uniqid('img_') . '.' . $fileType;
                    $uploadPath = $uploadDir . $newFileName;
                    move_uploaded_file($fileTmpName, $uploadPath);
                    $imagePaths[$key] = $uploadPath;
                    $statusFields['Status' . $i] = 'Pending'; // Set status as Pending for uploaded documents
                }
            }

            // Update or insert image paths and status in the database
            $sql = "";
            if (!empty($imagePaths)) {
                if (empty($docCollectionData)) {
                    // Insert new record with 'Pending' status for uploaded documents only
                    $sql = "INSERT INTO documentcollection (LeadID, " . implode(', ', array_keys($imagePaths)) . ", " . implode(', ', array_keys($statusFields)) . ") 
                            VALUES ('$leadID', '" . implode("', '", $imagePaths) . "', '" . implode("', '", $statusFields) . "')";
                } else {
                    // Update existing record with 'Pending' status for uploaded documents only
                    $updateFields = [];
                    foreach ($imagePaths as $key => $path) {
                        if (!empty($path)) {
                            $updateFields[] = $key . " = '" . $path . "'";
                        }
                    }
                    foreach ($statusFields as $key => $status) {
                        if (!empty($status)) {
                            $updateFields[] = $key . " = '" . $status . "'";
                        }
                    }
                    $sql = "UPDATE documentcollection SET " . implode(', ', $updateFields) . " WHERE LeadID = '$leadID'";
                }

                if ($sql != "" && $conn->query($sql) === TRUE) {
                    echo '<div class="alert alert-success" role="alert">Document paths updated successfully!</div>';

                    // Check if all required documents are uploaded
                    $requiredDocuments = ['Document1', 'Document2', 'Document3', 'Document4', 'Document5', 'Document6','Document7','Document8','Document9','Document10','Document11','Document12','Document13',];
                    $allRequiredUploaded = true;
                    foreach ($requiredDocuments as $doc) {
                        if (empty($imagePaths[$doc])) {
                            $allRequiredUploaded = false;
                            break;
                        }
                    }

                    // Update StepReached based on document upload status
                    $stepReached = ($allRequiredUploaded) ? 'Documents  complete but  verification pending' : 'Documents incomplete';
                    $updateStepSQL = "UPDATE personalinformation SET StepReached = '$stepReached' WHERE ID = '$leadID'";
                    
                    
                    // Insert notifications
        $notificationTitle = "Documents Uploaded for ID $leadID,By $username";
        $notifications = [
            ['nfor' => 'admin', 'nby' => 'System'],
            ['nfor' => 'branchmanager', 'nby' => 'System']
        ];

        foreach ($notifications as $notification) {
            $sql = "INSERT INTO notifications (title, message, nfor, nby, status, created_at)
                    VALUES ('$notificationTitle', 'Documents for ID $leadID,By $username.', '{$notification['nfor']}', '{$notification['nby']}', 'unread', NOW())";
            $conn->query($sql);
        }

                    if ($conn->query($updateStepSQL) === TRUE) {
                        echo '<div class="alert alert-success" role="alert">StepReached updated successfully!</div>';
                    } else {
                        echo '<div class="alert alert-danger" role="alert">Error updating StepReached: ' . $conn->error . '</div>';
                    }

                    // Redirect based on role
                    if ($role === 'sales') {
                        header("Location: borrowersales.php");
                    } elseif ($role === 'branchmanager') {
                        header("Location: borrowerbranchmanager.php");
                    } else {
                        header("Location: borrower.php");
                    }
                    exit(); // Ensure script execution stops after redirection
                } else {
                    echo '<div class="alert alert-danger" role="alert">Error updating document paths: ' . $conn->error . '</div>';
                }
            } else {
                echo '<div class="alert alert-warning" role="alert">No valid documents uploaded!</div>';
            }
        }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Upload - <?php echo $leadInfo['FullName']; ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container" style="margin-top:50px;">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome!</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo $redirecturl; ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Image Upload</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="my-4">Image Upload - <?php echo $leadInfo['FullName']; ?></h1>
        <form method="POST" enctype="multipart/form-data">
            <?php
            foreach ($documentDisplayNames as $columnName => $displayName) {
                $inputValue = (!empty($docCollectionData[$columnName])) ? $docCollectionData[$columnName] : '';
                echo '<div class="form-group">';
                echo '<label for="' . $columnName . '">' . $displayName . ':</label>';
                echo '<input type="file" class="form-control-file" id="' . $columnName . '" name="' . $columnName . '" value="' . $inputValue . '">';
                echo '</div>';
            }
            ?>
            <button type="submit" class="btn btn-primary">Upload Documents</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
    } else {
        echo '<div class="alert alert-warning" role="alert">Invalid lead ID or not a hot lead!</div>';
    }
} else {
    echo '<div class="alert alert-danger" role="alert">Lead ID not provided!</div>';
}
$conn->close();
?>
