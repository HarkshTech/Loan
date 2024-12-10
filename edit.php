<?php
session_start(); // Start the session
$loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
include 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    
    // Fetch the personal information to check if the lead exists
    $checkHotLead = $conn->query("SELECT * FROM personalinformation WHERE ID = '$id'");
    
    if ($checkHotLead->num_rows > 0) {
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
            'Document13' => 'CIBIL Report'
        ];

        // Get document collection for the lead
        $documentCollection = $conn->query("SELECT * FROM documentcollection WHERE LeadID = '$id'");
        $docCollectionData = ($documentCollection->num_rows > 0) ? $documentCollection->fetch_assoc() : [];

        // Process form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $uploadDir = 'uploads/'; // Directory where files will be uploaded
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


            $imagePaths = [];
            $statusFields = [];

            for ($i = 1; $i <= 13; $i++) {
                $key = 'Document' . $i;
                $fileName = $_FILES[$key]['name'] ?? null;
                $fileTmpName = $_FILES[$key]['tmp_name'] ?? null;
                $fileType = $fileName ? strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) : '';

                if (!empty($fileName) && in_array($fileType, $allowedTypes)) {
                    $newFileName = uniqid('img_') . '.' . $fileType;
                    $uploadPath = $uploadDir . $newFileName;
                    move_uploaded_file($fileTmpName, $uploadPath);
if (move_uploaded_file($fileTmpName, $uploadPath)) {
    error_log("File uploaded successfully: " . $uploadPath);
} else {
    error_log("Failed to upload file: " . $fileTmpName . " to " . $uploadPath);
}

                    $imagePaths[$key] = $uploadPath;
                    $statusFields['Status' . $i] = 'Pending'; // Set status as Pending for uploaded documents
                }
            }

            $sql = "";
            if (empty($docCollectionData)) {
                $sql = "INSERT INTO documentcollection (LeadID, " . implode(', ', array_keys($imagePaths)) . ", " . implode(', ', array_keys($statusFields)) . ") 
                        VALUES ('$id', '" . implode("', '", $imagePaths) . "', '" . implode("', '", $statusFields) . "')";
            } else {
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
                if (!empty($updateFields)) {
                    $sql = "UPDATE documentcollection SET " . implode(', ', $updateFields) . " WHERE LeadID = '$id'";
                }
            }

            if (!empty($sql) && $conn->query($sql) === TRUE) {
                $_SESSION['success_message'] = 'Document paths updated successfully!';
                if ($role === 'admin') {
                    header("Location: verify.php");
                } elseif ($role === 'branchmanager') {
                    header("Location: hot_leads.php");
                }
                exit();
            } else {
                echo '<div class="alert alert-danger" role="alert">Error updating document paths: ' . $conn->error . '</div>';
            }
        }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Upload - <?php echo htmlspecialchars($leadInfo['FullName']); ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
        if ($_SESSION['role'] === 'branchmanager') {
            include 'leftsidebranch.php';
        } elseif ($_SESSION['role'] === 'admin') {
            include 'leftside.php';
        }
    ?>
    <div class="container" style="margin-top: 8%;">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="branchmanager.php">Dashboard</a></li>
                            <li class="breadcrumb-item">Welcome !</li>
                            <li class="breadcrumb-item active">Edit Documents</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="my-4">Image Upload - <?php echo htmlspecialchars($leadInfo['FullName']); ?></h1>
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<script>alert("' . $_SESSION['success_message'] . '");</script>';
            unset($_SESSION['success_message']);
        }
        ?>
        <form method="POST" enctype="multipart/form-data">
            <?php
            foreach ($documentDisplayNames as $columnName => $displayName) {
                $existingFilePath = $docCollectionData[$columnName] ?? '';
                $existingStatus = $docCollectionData['Status' . substr($columnName, 8)] ?? '';
                $existingRemark = $docCollectionData['remarks' . substr($columnName, 8)] ?? '';

                echo '<div class="form-group">';
                echo '<label for="' . htmlspecialchars($columnName) . '">' . htmlspecialchars($displayName) . ':</label>';
                if (!empty($existingFilePath)) {
                    echo '<p>Current file: <a href="uploads/' . htmlspecialchars(basename($existingFilePath)) . '" target="_blank">' . htmlspecialchars(basename($existingFilePath)) . '</a></p>';

                    if ($existingStatus === 'Rejected') {
                        echo '<textarea class="form-control mt-2" name="remarks[' . htmlspecialchars($columnName) . ']" placeholder="Enter remarks for rejection">' . htmlspecialchars($existingRemark) . '</textarea>';
                        echo '<input type="file" class="form-control-file mt-2" id="' . htmlspecialchars($columnName) . '" name="' . htmlspecialchars($columnName) . '">';
                    } else {
                        echo '<input type="file" class="form-control-file" id="' . htmlspecialchars($columnName) . '" name="' . htmlspecialchars($columnName) . '" disabled>';
                    }
                } else {
                    echo '<input type="file" class="form-control-file" id="' . htmlspecialchars($columnName) . '" name="' . htmlspecialchars($columnName) . '">';
                }
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
