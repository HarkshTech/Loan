<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'config.php';
date_default_timezone_set('Asia/Kolkata');
session_start();
$role=$_SESSION['role'];


// Function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags($data));
}

// Fetch existing data if any
$leadID = $_GET['id'] ?? $_POST['lead_id'] ?? null;
$existingData = null;
if ($leadID) {
    $stmt = $conn->prepare("SELECT * FROM legal_evaluations WHERE lead_id = ?");
    $stmt->bind_param("i", $leadID);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingData = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $currentTime = date('Y-m-d H:i:s');
    
    $leadID = $_POST['lead_id'];
    $verifierName = $_POST['verifier_name'];
    $geolocation = sanitize($_POST['geolocation'] ?? '');
    $evaluationNeeded = isset($_POST['evaluation_needed']) ? 1 : 0;

    // Debugging: Log the leadID and verifierName
    error_log("Lead ID: " . $leadID);
    error_log("Verifier Name: " . $verifierName);

    // Upload files
    $uploadDir = 'uploads/';
    $registreeCopy = $existingData['registree_copy'] ?? '';
    $oldRegistreeCopy = $existingData['old_registree_copy'] ?? '';
    $fardCopy = $existingData['fard_copy'] ?? '';
    $nocCopy = $existingData['noc_copy'] ?? '';
    $videos = json_decode($existingData['videos'] ?? '[]', true);
    
    if (isset($_FILES['registree_copy']) && $_FILES['registree_copy']['error'] == UPLOAD_ERR_OK) {
        $registreeCopy = $uploadDir . basename($_FILES['registree_copy']['name']);
        move_uploaded_file($_FILES['registree_copy']['tmp_name'], $registreeCopy);
    }

    if (isset($_FILES['old_registree_copy']) && $_FILES['old_registree_copy']['error'] == UPLOAD_ERR_OK) {
        $oldRegistreeCopy = $uploadDir . basename($_FILES['old_registree_copy']['name']);
        move_uploaded_file($_FILES['old_registree_copy']['tmp_name'], $oldRegistreeCopy);
    }

    if (isset($_FILES['fard_copy']) && $_FILES['fard_copy']['error'] == UPLOAD_ERR_OK) {
        $fardCopy = $uploadDir . basename($_FILES['fard_copy']['name']);
        move_uploaded_file($_FILES['fard_copy']['tmp_name'], $fardCopy);
    }

    if (isset($_FILES['noc_copy']) && $_FILES['noc_copy']['error'] == UPLOAD_ERR_OK) {
        $nocCopy = $uploadDir . basename($_FILES['noc_copy']['name']);
        move_uploaded_file($_FILES['noc_copy']['tmp_name'], $nocCopy);
    }

    if (isset($_FILES['videos'])) {
        foreach ($_FILES['videos']['name'] as $key => $name) {
            if ($_FILES['videos']['error'][$key] == UPLOAD_ERR_OK) {
                $video = $uploadDir . basename($name);
                move_uploaded_file($_FILES['videos']['tmp_name'][$key], $video);
                $videos[] = $video;
            }
        }
    }
    $videosJson = json_encode($videos);

    // Upload multiple images
    $propertyImages = json_decode($existingData['property_images'] ?? '[]', true);
    if (isset($_FILES['property_images'])) {
        foreach ($_FILES['property_images']['name'] as $key => $name) {
            if ($_FILES['property_images']['error'][$key] == UPLOAD_ERR_OK) {
                $propertyImage = $uploadDir . basename($name);
                move_uploaded_file($_FILES['property_images']['tmp_name'][$key], $propertyImage);
                $propertyImages[] = $propertyImage;
            }
        }
    }
    $propertyImagesJson = json_encode($propertyImages);
    
    $registreestatus='Pending';
    $fardstatus='Pending';
    $NOCstatus='Pending';
    $videostatus='Pending';
    $oldregstatus='Pending';

    if ($existingData) {
        $stmt = $conn->prepare("UPDATE legal_evaluations SET verifier_name = ?, registree_copy = ?, old_registree_copy = ?, fard_copy = ?, noc_copy = ?, property_images = ?, videos = ?, geolocation = ?,evaluation_needed=?, updated_at = ?, registree_status = ?, fard_status = ?, noc_status = ?, video_status = ?, old_registree_status = ? WHERE lead_id = ?");
        $stmt->bind_param("sssssssssssssssi", $verifierName, $registreeCopy, $oldRegistreeCopy, $fardCopy, $nocCopy, $propertyImagesJson, $videosJson, $geolocation,$evaluationNeeded, $currentTime,$registreestatus,$fardstatus,$NOCstatus,$videostatus,$oldregstatus, $leadID);
    } else {
        $stmt = $conn->prepare("INSERT INTO legal_evaluations (lead_id, verifier_name, registree_copy, old_registree_copy, fard_copy, noc_copy, property_images, videos, geolocation,evaluation_needed,registree_status,fard_status,noc_status,video_status,old_registree_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssssssssss", $leadID, $verifierName, $registreeCopy, $oldRegistreeCopy, $fardCopy, $nocCopy, $propertyImagesJson, $videosJson, $geolocation,$evaluationNeeded,$registreestatus,$fardstatus,$NOCstatus,$videostatus,$oldregstatus, $currentTime);
    }
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
        if($role==='admin'){
        include 'leftside.php';
        $redirect='dashboard.php'; // Assuming this includes the left-side navigation
        }
        elseif($role==='verifier'){
            include 'leftsideverifier.php';
            $redirect='dashboardverifier.php';
        }
    ?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo $redirect;?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="verify.php">Verifications</a></li>
                            <li class="breadcrumb-item active">Legal Verifications</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="my-4">Legal Evaluation</h1>
        <form action="legal_evaluation.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars($_GET['id']); ?>" required>

            <div class="form-group">
                <label for="verifier_name">Verifier Name</label>
                <input type="text" class="form-control" id="verifier_name" name="verifier_name" value="<?php echo htmlspecialchars($existingData['verifier_name'] ?? ''); ?>" <?php echo isset($existingData['verifier_name']) ? 'readonly' : 'required'; ?>>
            </div>

            <div class="form-group">
                <label for="registree_copy">Registree Copy</label>
                <?php if (isset($existingData['registree_copy']) && $existingData['registree_status'] != 'Rejected') : ?>
                    <p><a href="<?php echo $existingData['registree_copy']; ?>" target="_blank">View uploaded file</a></p>
                <?php endif; ?>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="registree_copy" name="registree_copy" <?php echo isset($existingData['registree_copy']) && $existingData['registree_status'] != 'Rejected' ? 'disabled' : 'required'; ?>>
                    <label class="custom-file-label" for="registree_copy"><?php echo isset($existingData['registree_copy']) ? basename($existingData['registree_copy']) : 'Choose file'; ?></label>
                </div>
            </div>

            <div class="form-group">
                <label for="old_registree_copy">Old Registree Copy</label>
                <?php 
                    if (isset($existingData['old_registree_copy']) && $existingData['old_registree_status'] != 'Rejected') : 
                ?>
                    <p><a href="<?php echo $existingData['old_registree_copy']; ?>" target="_blank">View uploaded file</a></p>
                <?php endif; ?>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="old_registree_copy" name="old_registree_copy" <?php echo isset($existingData['old_registree_copy']) && $existingData['old_registree_status'] != 'Rejected' ? 'disabled' : 'required'; ?>>
                    <label class="custom-file-label" for="old_registree_copy"><?php echo isset($existingData['old_registree_copy']) ? basename($existingData['old_registree_copy']) : 'Choose file'; ?></label>
                </div>
            </div>

            <div class="form-group">
                <label for="fard_copy">Fard Copy</label>
                <?php if (isset($existingData['fard_copy']) && $existingData['fard_status'] != 'Rejected') : ?>
                    <p><a href="<?php echo $existingData['fard_copy']; ?>" target="_blank">View uploaded file</a></p>
                <?php endif; ?>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="fard_copy" name="fard_copy" <?php echo isset($existingData['fard_copy']) && $existingData['fard_status'] != 'Rejected' ? 'disabled' : 'required'; ?>>
                    <label class="custom-file-label" for="fard_copy"><?php echo isset($existingData['fard_copy']) ? basename($existingData['fard_copy']) : 'Choose file'; ?></label>
                </div>
            </div>

            <div class="form-group">
                <label for="noc_copy">NOC Copy</label>
                <?php if (isset($existingData['noc_copy']) && $existingData['noc_status'] != 'Rejected') : ?>
                    <p><a href="<?php echo $existingData['noc_copy']; ?>" target="_blank">View uploaded file</a></p>
                <?php endif; ?>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="noc_copy" name="noc_copy" <?php echo isset($existingData['noc_copy']) && $existingData['noc_status'] != 'Rejected' ? 'disabled' : 'required'; ?>>
                    <label class="custom-file-label" for="noc_copy"><?php echo isset($existingData['noc_copy']) ? basename($existingData['noc_copy']) : 'Choose file'; ?></label>
                </div>
            </div>

            <div class="form-group">
                <label for="property_images">Property Images</label>
                <?php 
                if (!empty($existingData['property_images'])) {
                    $propertyImages = json_decode($existingData['property_images'], true);
                    foreach ($propertyImages as $image) {
                        echo '<p><a href="' . $image . '" target="_blank">View uploaded file</a></p>';
                    }
                }
                ?>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="property_images" name="property_images[]" multiple>
                    <label class="custom-file-label" for="property_images"><?php echo isset($existingData['property_images']) ? 'Files uploaded' : 'Choose files'; ?></label>
                </div>
            </div>

            <div class="form-group">
                <label for="videos">Videos</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="videos" name="videos[]" multiple>
                    <label class="custom-file-label" for="videos"><?php echo isset($existingData['videos']) ? 'Files uploaded' : 'Choose files'; ?></label>
                </div>
            </div>

            <div class="video-preview">
                <?php
                if (!empty($existingData['videos'])) {
                    $videos = json_decode($existingData['videos'], true);
                    foreach ($videos as $video) {
                        echo '<div>';
                        echo '<video src="' . $video . '" controls width="200"></video>';
                        echo '<button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">Remove</button>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
            
            <div class="form-group">
                <label for="evaluation_needed">Needed Evaluation Report?</label>
                <input type="checkbox" id="evaluation_needed" name="evaluation_needed"
                    <?php echo isset($existingData['evaluation_needed']) && $existingData['evaluation_needed'] ? 'checked' : ''; ?>
                    <?php echo isset($existingData['evaluation_needed']) ? 'disabled' : ''; ?>>
                <?php if (isset($existingData['evaluation_needed'])): ?>
                    <input type="hidden" name="evaluation_needed" value="<?php echo $existingData['evaluation_needed']; ?>">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="geolocation">Geolocation:</label>
                <input type="text" class="form-control" name="geolocation" id="geolocation" value="<?php echo htmlspecialchars($existingData['geolocation'] ?? ''); ?>" readonly required>
            </div>

            <button type="submit" class="btn btn-primary">Submit Evaluation</button>
        </form>
    </div>

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
            document.getElementById("geolocation").value = "Latitude: " + position.coords.latitude + " Longitude: " + position.coords.longitude;
        }

        getLocation();

        // Add custom file input label behavior
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });

        // Preview uploaded videos
        function previewVideos(input) {
            var preview = document.querySelector('.video-preview');
            preview.innerHTML = ''; // Clear existing previews

            for (var i = 0; i < input.files.length; i++) {
                var file = input.files[i];
                var reader = new FileReader();

                reader.onload = function(e) {
                    var video = document.createElement('video');
                    video.src = e.target.result;
                    video.controls = true;
                    video.width = 200;

                    var removeButton = document.createElement('button');
                    removeButton.textContent = 'Remove';
                    removeButton.className = 'btn btn-danger btn-sm';
                    removeButton.onclick = function() {
                        video.parentElement.remove();
                    };

                    var container = document.createElement('div');
                    container.appendChild(video);
                    container.appendChild(removeButton);
                    preview.appendChild(container);
                }

                reader.readAsDataURL(file);
            }
        }

        document.getElementById('videos').addEventListener('change', function() {
            previewVideos(this);
        });
    </script>
</body>
</html>