<?php
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
            'Document13' => 'CIBIL Report',
        ];

        // Get document collection for the lead
        $documentCollection = $conn->query("SELECT * FROM documentcollection WHERE LeadID = '$id'");
        $docCollectionData = ($documentCollection->num_rows > 0) ? $documentCollection->fetch_assoc() : [];

        // Process form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $remarks = $_POST['remarks'] ?? []; // Get remarks from the form data

            // Update remarks for rejected documents
            foreach ($remarks as $columnName => $remark) {
                $statusColumnName = 'Status' . substr($columnName, 8);
                if (isset($docCollectionData[$statusColumnName]) && $docCollectionData[$statusColumnName] === 'Rejected') {
                    $remarkColumnName = 'remarks' . substr($columnName, 8);
                    $sqlUpdateRemarks = "UPDATE documentcollection SET $remarkColumnName = '$remark' WHERE LeadID = '$id'";
                    $conn->query($sqlUpdateRemarks);
                }
            }
            
            $uploadDir = 'uploads/'; // Directory where files will be uploaded
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx', 'xls', 'xlsx']; // Allowed file types

            $imagePaths = [];
            $statusFields = [];

            for ($i = 1; $i <= 13; $i++) {
                $key = 'Document' . $i;

                if (isset($_FILES[$key]) && !empty($_FILES[$key]['name'])) {
                    $fileName = $_FILES[$key]['name'];
                    $fileTmpName = $_FILES[$key]['tmp_name'];
                    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if (in_array($fileType, $allowedTypes)) {
                        $newFileName = uniqid('img_') . '.' . $fileType;
                        $uploadPath = $uploadDir . $newFileName;
                        move_uploaded_file($fileTmpName, $uploadPath);
                        $imagePaths[$key] = $uploadPath;
                        $statusFields['Status' . $i] = 'Pending'; // Set status as Pending for uploaded documents
                    }
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
                echo '<div class="alert alert-success" role="alert">Document paths updated successfully!</div>';
                
                header("Location: hotleadsales.php");
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
    <title>Image Upload - <?php echo $leadInfo['FullName']; ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="my-4">Image Upload - <?php echo $leadInfo['FullName']; ?></h1>
        <form method="POST" enctype="multipart/form-data">
            <?php
            foreach ($documentDisplayNames as $columnName => $displayName) {
                $existingFilePath = isset($docCollectionData[$columnName]) ? $docCollectionData[$columnName] : '';
                $existingStatus = isset($docCollectionData['Status' . substr($columnName, 8)]) ? $docCollectionData['Status' . substr($columnName, 8)] : '';
                $existingRemark = isset($docCollectionData['remarks' . substr($columnName, 8)]) ? $docCollectionData['remarks' . substr($columnName, 8)] : '';

                echo '<div class="form-group">';
                echo '<label for="' . $columnName . '">' . $displayName . ':</label>';
                if (!empty($existingFilePath)) {
                    echo '<p>Current file: <a href="' . $existingFilePath . '" target="_blank">' . basename($existingFilePath) . '</a></p>';

                    if ($existingStatus === 'Rejected') {
                        echo '<textarea class="form-control mt-2" name="remarks[' . $columnName . ']" placeholder="Enter remarks for rejection">' . $existingRemark . '</textarea>';
                        echo '<input type="file" class="form-control-file mt-2" id="' . $columnName . '" name="' . $columnName . '">';
                    } else {
                        echo '<input type="file" class="form-control-file" id="' . $columnName . '" name="' . $columnName . '" disabled>';
                    }

                } else {
                    echo '<input type="file" class="form-control-file" id="' . $columnName . '" name="' . $columnName . '">';
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
