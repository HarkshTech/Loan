<?php
session_start();
include 'config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$username = $_SESSION['username'];
$role = $_SESSION['role'];
$loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;

if ($role === 'admin') {
    $redirect = 'dashboard.php';
} elseif ($role === 'branchmanager') {
    $redirect = 'branchmanager.php';
} elseif ($role === 'recovery') {
    $redirect = 'dashboardrecovery.php';
}


// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploadFiles']) && !empty($_FILES['uploadFiles']['name'][0])) {
    // Define upload directory
    $uploadDir = 'uploads/visits/';

    // Get the files data
    $files = $_FILES['uploadFiles'];
    $uploadedFilePaths = [];

    // Loop through each file uploaded
    for ($i = 0; $i < count($files['name']); $i++) {
        $fileName = $files['name'][$i];
        $fileTmpName = $files['tmp_name'][$i];
        $fileSize = $files['size'][$i];
        $fileError = $files['error'][$i];
        $fileType = $files['type'][$i];

        // Generate a unique file name to avoid conflicts
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $uniqueFileName = uniqid('visit_', true) . '.' . $fileExtension;

        // Define the path where the file will be stored
        $fileDestination = $uploadDir . $uniqueFileName;

        // Validate file type (optional)
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov','avif','jfif','mp4']; // Add your allowed extensions here
        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            echo "Error: Invalid file type for file $fileName.";
            continue;
        }

        // Check if the file size exceeds the limit (e.g., 100MB)
        if ($fileSize > 104857600) {
            echo "Error: File size exceeds the maximum allowed size for file $fileName.";
            continue;
        }

        // Try to move the uploaded file to the server
        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            // Save the path of the uploaded file to the array
            $uploadedFilePaths[] = $fileDestination;
        } else {
            echo "Error: Failed to upload file $fileName.";
        }
    }

    if (!empty($uploadedFilePaths)) {
        // Get the visit ID from the form or URL (if it's coming from a modal)
        $visitId = $_POST['id']; // Assuming 'visit_id' is passed from the form

        // Convert the array of file paths into a string (comma-separated)
        $filesString = implode(',', $uploadedFilePaths);

        // Update the database with the file paths in the 'visit_files' column for the specific ID
        $stmt = $conn->prepare("UPDATE recovery_data SET visit_files = ? WHERE id = ?");
        $stmt->bind_param("si", $filesString, $visitId);

        if ($stmt->execute()) {
            echo "Files uploaded successfully, and database updated.";
        } else {
            echo "Error updating the database.";
        }
    } else {
        echo "No files uploaded.";
    }
}

// Check if remarks update is requested via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['remarks']) && isset($_POST['caseStatus']) && isset($_POST['visitStatus'])) {
    $id = $_POST['id'];
    $remarks = $_POST['remarks'];
    $caseStatus = $_POST['caseStatus'];
    $visitStatus = $_POST['visitStatus'];
    $visitGeoLocation = $_POST['visitGeoLocation'];

    // Append today's date to the remarks
    $currentDate = date('Y-m-d');
    $remarks = "$currentDate: $remarks";

    // Get existing remarks
    $sqlGetRemarks = "SELECT Remarks FROM recovery_data WHERE ID = ?";
    $stmt = $conn->prepare($sqlGetRemarks);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($existingRemarks);
    $stmt->fetch();
    $stmt->close();

    // Append the new remarks to the existing remarks
    $updatedRemarks = $existingRemarks . "\n" . $remarks;

    if ($visitStatus === 'Completed, More Visit Needed') {
        $visitAgain = 'YES';
        $visitAgainDate = $_POST['visitAgainDate'];

        // Update remarks, visit status, case status, visit again, visit again date, and visit geolocation in recovery_data table
        $sqlUpdateRemarks = "UPDATE recovery_data SET Remarks = ?, visitstatus = ?, CaseStatus = ?, visitagain = ?, visitagaindate = ?, visitgeolocation = ? WHERE ID = ?";
        $stmt = $conn->prepare($sqlUpdateRemarks);
        $stmt->bind_param("ssssssi", $updatedRemarks, $visitStatus, $caseStatus, $visitAgain, $visitAgainDate, $visitGeoLocation, $id);
    } else {
        $visitAgain = 'NO';
        $visitAgainDate = NULL;

        // Update remarks, visit status, case status, and visit again in recovery_data table
        $sqlUpdateRemarks = "UPDATE recovery_data SET Remarks = ?, visitstatus = ?, CaseStatus = ?, visitagain = ?, visitgeolocation = ? WHERE ID = ?";
        $stmt = $conn->prepare($sqlUpdateRemarks);
        $stmt->bind_param("sssssi", $updatedRemarks, $visitStatus, $caseStatus, $visitAgain, $visitGeoLocation, $id);
    }

    if ($stmt->execute()) {
        echo "Remarks updated successfully.";
    } else {
        echo "Failed to update remarks.";
    }

    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Needed Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1100px;
            margin: 100px auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .page-title-box {
            margin-bottom: 30px;
        }
        .table th {
            background-color: #343a40;
            color: white;
        }
        .table td {
            vertical-align: middle;
        }
        .action-column {
            width: 180px;
        }
        .upload-preview {
            position: relative;
            display: inline-block;
            margin: 5px;
        }
        .remove-image {
            position: absolute;
            top: 0;
            right: 0;
            cursor: pointer;
            background: red;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        @media (max-width: 768px) {
            .container {
                margin-top: 20px;
            }
            .container table {
                font-size: 14px;
            }
            .container th,
            .container td {
                padding: 8px;
            }
            .container .action-column {
                width: 150px;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome!</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo $redirect; ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Visit Needed</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="mb-4" style="font-size:23px; line-height:27px;">Visit Needed Management</h1>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Lead ID</th>
                        <th>Full Name</th>
                        <th>Address</th>
                        <th>Overdue Days</th>
                        <th>Visit Scheduled</th>
                        <th>Visit Status</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($loggedInUser) {
                        if ($_SESSION['role'] === 'recovery') {
                            $sql = "SELECT rd.ID, rd.LeadID, pi.FullName, pi.Address, rd.Overdue_days, rd.visitscheduled, rd.visitstatus, rd.remarks
                                    FROM recovery_data rd
                                    JOIN personalinformation pi ON rd.LeadID = pi.ID
                                    WHERE rd.VisitNeeded = 'YES' AND rd.AssignedTo = '$loggedInUser' AND rd.CaseStatus <> 'Recovery Done'";
                        } else {
                            $sql = "SELECT rd.ID, rd.LeadID, pi.FullName, pi.Address, rd.Overdue_days, rd.visitscheduled, rd.visitstatus, rd.remarks
                                    FROM recovery_data rd
                                    JOIN personalinformation pi ON rd.LeadID = pi.ID
                                    WHERE rd.VisitNeeded = 'YES' AND rd.CaseStatus <> 'Recovery Done'";
                        }
                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr class='main-row'>";
                                echo "<td>" . $row["ID"] . "</td>";
                                echo "<td>" . $row["LeadID"] . "</td>";
                                echo "<td>" . $row["FullName"] . "</td>";
                                echo "<td>" . $row["Address"] . "</td>";
                                echo "<td>" . $row["Overdue_days"] . "</td>";
                                echo "<td>" . $row["visitscheduled"] . "</td>";
                                echo "<td>" . $row["visitstatus"] . "</td>";
                                echo "<td>" . $row["remarks"] . "</td>";
                                echo "<td><button class='btn btn-primary btn-update-remarks' data-id='" . $row["ID"] . "'>Update Remarks</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center'>No leads found</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center'>You are not authorized to view this data</td></tr>";
                    }

                    $stmt->close();
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for entering remarks -->
    <div class="modal fade" id="remarksModal" tabindex="-1" role="dialog" aria-labelledby="remarksModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="remarksModalLabel">Update Remarks</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="remarksForm">
                        <input type="hidden" id="visitId" name="id">
                        <div class="form-group">
                            <label for="remarks">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="visitStatus">Visit Status</label>
                            <select class="form-control" id="visitStatus" name="visitStatus" required>
                                <option value="Completed">Completed</option>
                                <option value="Pending">Pending</option>
                                <option value="Completed, More Visit Needed">Completed, More Visit Needed</option>
                            </select>
                        </div>
                        <div class="form-group" id="visitAgainGroup" style="display:none;">
                            <label for="visitAgainDate">Visit Again Date</label>
                            <input type="date" class="form-control" id="visitAgainDate" name="visitAgainDate">
                        </div>
                        <div class="form-group">
                            <label for="caseStatus">Case Status</label>
                            <select class="form-control" id="caseStatus" name="caseStatus" required>
                                <option value="Recovery Done">Recovery Done</option>
                                <option value="Will Pay in next visit">Will Pay in next visit</option>
                                <option value="Legal Case">Legal Case</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="visitGeoLocation">Geolocation</label>
                            <input type="text" class="form-control" id="visitGeoLocation" name="visitGeoLocation" readonly>
                        </div>
                        <div class="form-group">
                            <label for="uploadFiles">Upload Images/Videos</label>
                            <input type="file" class="form-control-file" id="uploadFiles" name="uploadFiles[]" multiple>
                            <div id="filePreviews"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveRemarks">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Open modal and populate fields
            $('.btn-update-remarks').on('click', function() {
                const id = $(this).data('id');
                $('#visitId').val(id);
                $('#filePreviews').empty();
                $('#remarksModal').modal('show');

                // Populate geolocation
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        $('#visitGeoLocation').val(position.coords.latitude + ', ' + position.coords.longitude);
                    });
                }
            });

            // Handle visit status change
            $('#visitStatus').on('change', function() {
                if ($(this).val() === 'Completed, More Visit Needed') {
                    $('#visitAgainGroup').show();
                } else {
                    $('#visitAgainGroup').hide();
                }
            });

            // Handle file upload preview
            $('#uploadFiles').on('change', function() {
                $('#filePreviews').empty();
                const files = this.files;
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = $('<div class="upload-preview"></div>');
                        if (file.type.startsWith('image/')) {
                            preview.append(`<img src="${e.target.result}" width="100" height="100">`);
                        } else if (file.type.startsWith('video/')) {
                            preview.append(`<video width="100" height="100" controls><source src="${e.target.result}" type="${file.type}"></video>`);
                        }
                        preview.append('<div class="remove-image">✖</div>');
                        $('#filePreviews').append(preview);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Remove uploaded file preview
            $(document).on('click', '.remove-image', function() {
                $(this).parent('.upload-preview').remove();
            });

            // Save remarks and other info
            $('#saveRemarks').on('click', function() {
                const formData = new FormData($('#remarksForm')[0]);
                $.ajax({
                    type: 'POST',
                    url: 'visitneeded.php',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        alert(response);
                        $('#remarksModal').modal('hide');
                        location.reload();
                    },
                    error: function() {
                        alert('Error updating remarks.');
                    }
                });
            });
        });
    </script>
</body>
</html>
