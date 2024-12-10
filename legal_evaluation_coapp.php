<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'config.php';
date_default_timezone_set('Asia/Kolkata');
session_start();
$role = $_SESSION['role'];

// Function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags($data));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $currentTime = date('Y-m-d H:i:s');
    
    $leadID = $_POST['lead_id'];
    $verifierName = $_POST['verifier_name_COAPP'];
    $geolocation = sanitize($_POST['geolocation_COAPP'] ?? '');
    $evaluationNeeded = isset($_POST['evaluation_needed_COAPP']) ? 1 : 0;

    // Debugging: Log the leadID and verifierName
    error_log("Lead ID: " . $leadID);
    error_log("Verifier Name: " . $verifierName);

    // Upload files
    $uploadDir = 'uploads/';
    $registreeCopy = '';
    $oldRegistreeCopy = '';
    $fardCopy = '';
    $nocCopy = '';
    $videos = [];
    
    if (isset($_FILES['registree_copy_COAPP']) && $_FILES['registree_copy_COAPP']['error'] == UPLOAD_ERR_OK) {
        $registreeCopy = $uploadDir . basename($_FILES['registree_copy_COAPP']['name']);
        move_uploaded_file($_FILES['registree_copy_COAPP']['tmp_name'], $registreeCopy);
    }

    if (isset($_FILES['old_registree_copy_COAPP']) && $_FILES['old_registree_copy_COAPP']['error'] == UPLOAD_ERR_OK) {
        $oldRegistreeCopy = $uploadDir . basename($_FILES['old_registree_copy_COAPP']['name']);
        move_uploaded_file($_FILES['old_registree_copy_COAPP']['tmp_name'], $oldRegistreeCopy);
    }

    if (isset($_FILES['fard_copy_COAPP']) && $_FILES['fard_copy_COAPP']['error'] == UPLOAD_ERR_OK) {
        $fardCopy = $uploadDir . basename($_FILES['fard_copy_COAPP']['name']);
        move_uploaded_file($_FILES['fard_copy_COAPP']['tmp_name'], $fardCopy);
    }

    if (isset($_FILES['noc_copy_COAPP']) && $_FILES['noc_copy_COAPP']['error'] == UPLOAD_ERR_OK) {
        $nocCopy = $uploadDir . basename($_FILES['noc_copy_COAPP']['name']);
        move_uploaded_file($_FILES['noc_copy_COAPP']['tmp_name'], $nocCopy);
    }

    if (isset($_FILES['videos_COAPP'])) {
        foreach ($_FILES['videos_COAPP']['name'] as $key => $name) {
            if ($_FILES['videos_COAPP']['error'][$key] == UPLOAD_ERR_OK) {
                $video = $uploadDir . basename($name);
                move_uploaded_file($_FILES['videos_COAPP']['tmp_name'][$key], $video);
                $videos[] = $video;
            }
        }
    }
    $videosJson = json_encode($videos);

    // Upload multiple images
    $propertyImages = [];
    if (isset($_FILES['property_images_COAPP'])) {
        foreach ($_FILES['property_images_COAPP']['name'] as $key => $name) {
            if ($_FILES['property_images_COAPP']['error'][$key] == UPLOAD_ERR_OK) {
                $propertyImage = $uploadDir . basename($name);
                move_uploaded_file($_FILES['property_images_COAPP']['tmp_name'][$key], $propertyImage);
                $propertyImages[] = $propertyImage;
            }
        }
    }
    $propertyImagesJson = json_encode($propertyImages);
    
    $registreestatus = 'Pending';
    $fardstatus = 'Pending';
    $NOCstatus = 'Pending';
    $videostatus = 'Pending';
    $oldregstatus = 'Pending';

    $stmt = $conn->prepare("UPDATE legal_evaluations 
    SET verifier_name_COAPP = ?, 
        registree_copy_COAPP = ?, 
        old_registree_copy_COAPP = ?, 
        fard_copy_COAPP = ?, 
        noc_copy_COAPP = ?, 
        property_images_COAPP = ?, 
        videos_COAPP = ?, 
        geolocation_COAPP = ?, 
        evaluation_needed_COAPP = ?, 
        updated_at_COAPP = ?, 
        registree_status_COAPP = ?, 
        fard_status_COAPP = ?, 
        noc_status_COAPP = ?, 
        video_status_COAPP = ?, 
        old_registree_status_COAPP = ? 
    WHERE lead_id = ?");
    $stmt->bind_param("sssssssssssssssi",$verifierName, $registreeCopy, $oldRegistreeCopy, $fardCopy, $nocCopy, $propertyImagesJson, $videosJson, $geolocation, $evaluationNeeded, $currentTime, $registreestatus, $fardstatus, $NOCstatus, $videostatus, $oldregstatus,$leadID);
    $stmt->execute();
    $stmt->close();

    echo '<script>alert("Legal evaluation submitted successfully!"); window.location.replace("verify.php");</script>';
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Evaluation</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 20px;
        }
        .container {
            margin-top: 100px;
            max-width: 800px;
        }
        .btn-file {
            position: relative;
            overflow: hidden;
        }
        .btn-file input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;
            background: white;
            cursor: inherit;
            display: block;
        }
        .video-preview {
            display: flex;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .video-preview video {
            display: block;
            margin-right: 10px;
            margin-bottom: 10px;
            width: 200px;
        }
        .video-preview button {
            display: block;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <?php 
        if($role === 'admin'){
            include 'leftside.php';
            $redirect = 'dashboard.php';
        } elseif($role === 'verifier'){
            include 'leftsideverifier.php';
            $redirect = 'dashboardverifier.php';
        }
        elseif($role === 'branchmanager'){
            include 'leftsidebranch.php';
            $redirect = 'branchmanager.php';
        }
    ?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome!</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo $redirect; ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="verify.php">Verifications</a></li>
                            <li class="breadcrumb-item active">Legal Verifications</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="my-4">Legal Evaluation</h1>
        <form action="legal_evaluation_coapp.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars($_GET['id']); ?>" required>
            
            <div class="form-group">
                <label for="verifier_name_COAPP">Verifier Name</label>
                <input type="text" class="form-control" id="verifier_name_COAPP" name="verifier_name_COAPP" required>
            </div>

            <div class="form-group">
                <label for="registree_copy_COAPP">Registree Copy</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="registree_copy_COAPP" name="registree_copy_COAPP" required>
                    <label class="custom-file-label" for="registree_copy_COAPP">Choose file</label>
                </div>
            </div>

            <div class="form-group">
                <label for="old_registree_copy_COAPP">Old Registree Copy</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="old_registree_copy_COAPP" name="old_registree_copy_COAPP" required>
                    <label class="custom-file-label" for="old_registree_copy_COAPP">Choose file</label>
                </div>
            </div>

            <div class="form-group">
                <label for="fard_copy_COAPP">Fard Copy</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="fard_copy_COAPP" name="fard_copy_COAPP" required>
                    <label class="custom-file-label" for="fard_copy_COAPP">Choose file</label>
                </div>
            </div>

            <div class="form-group">
                <label for="noc_copy_COAPP">NOC Copy</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="noc_copy_COAPP" name="noc_copy_COAPP" required>
                    <label class="custom-file-label" for="noc_copy_COAPP">Choose file</label>
                </div>
            </div>

            <div class="form-group">
                <label for="videos_COAPP">Videos</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="videos_COAPP" name="videos_COAPP[]" multiple>
                    <label class="custom-file-label" for="videos_COAPP">Choose files</label>
                </div>
            </div>

            <div class="form-group">
                <label for="property_images_COAPP">Property Images</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="property_images_COAPP" name="property_images_COAPP[]" multiple required>
                    <label class="custom-file-label" for="property_images_COAPP">Choose files</label>
                </div>
            </div>

            <div class="form-group">
                <label for="geolocation_COAPP">Geolocation</label>
                <input type="text" class="form-control" id="geolocation_COAPP" name="geolocation_COAPP" readonly>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="evaluation_needed_COAPP" name="evaluation_needed_COAPP">
                <label class="form-check-label" for="evaluation_needed_COAPP">Is Legal Evaluation Needed?</label>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Submit</button>
        </form>
    </div>
</body>

</html>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                console.log("Geolocation is not supported by this browser.");
            }
        }

        function showPosition(position) {
            document.getElementById("geolocation_COAPP").value = "Latitude: " + position.coords.latitude + " Longitude: " + position.coords.longitude;
        }

        getLocation();
        
        // Add custom file input label behavior
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    </script>
