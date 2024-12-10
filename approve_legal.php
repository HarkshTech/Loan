<?php
include 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); // Start the session if not already started
$role=$_SESSION['role'];
date_default_timezone_set('Asia/Kolkata');

// Function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags($data));
}

// Handle form submission for status update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $evaluationID = $_POST['evaluation_id'];

    // Applicant fields
    $registreeStatus = sanitize($_POST['registree_status']);
    $registreeRemarks = sanitize($_POST['registree_remarks']);
    $fardStatus = sanitize($_POST['fard_status']);
    $fardRemarks = sanitize($_POST['fard_remarks']);
    $nocStatus = sanitize($_POST['noc_status']);
    $nocRemarks = sanitize($_POST['noc_remarks']);
    $videoStatus = sanitize($_POST['video_status']);
    $videoRemarks = sanitize($_POST['video_remarks']);
    $oldregistreeStatus = sanitize($_POST['old_registree_status']);
    $oldregistreeRemarks = sanitize($_POST['old_registree_remarks']);


    // Update the status and remarks in the database
    $stmt = $conn->prepare("UPDATE legal_evaluations SET registree_status = ?, registree_remarks = ?, old_registree_status = ?, old_registree_remarks = ?, fard_status = ?, fard_remarks = ?, noc_status = ?, noc_remarks = ?, video_status = ?, video_remarks = ? WHERE id = ?");
    $stmt->bind_param("ssssssssssi", 
        $registreeStatus, $registreeRemarks, $oldregistreeStatus, $oldregistreeRemarks, 
        $fardStatus, $fardRemarks, $nocStatus, $nocRemarks, 
        $videoStatus, $videoRemarks, $evaluationID);
    $stmt->execute();
    $stmt->close();

    echo "Status updated successfully!";
    
    $lid = $evaluationID;
    $update = "UPDATE personalinformation SET StepReached='Legal Verification Status Updated by ADMIN' WHERE ID=$lid";
    $conn->query($update);
}

// Fetch the evaluations from the database
$result = $conn->query("SELECT * FROM legal_evaluations");

// Get user role from session
$userRole = $_SESSION['role'] ?? 'guest'; // Default to 'guest' if not set
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Legal Evaluation Status</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 20px;
        }
        .container {
            margin-top: 100px;
            max-width: 1200px;
        }
        .custom-file-label::after {
            content: "Browse";
        }
    </style>
</head>

<body>
    
    <?php 
        if($role=='admin'){
            include 'leftside.php';
            $redirect='dashboard.php';
        }
        else{
            include 'leftsidebranch.php';
            $redirect='branchmanager.php';
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
                            <li class="breadcrumb-item active">Legal Verifications Approval</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="my-4">Update Legal Evaluation Status</h1>
        <input type="text" id="searchBar" class="form-control" placeholder="Search evaluations...">
        <?php if ($result->num_rows > 0) : ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Lead ID</th>
                        <th>Verifier Name</th>
                        <!--<th>Verifier Name (COAPP)</th>-->
                        <th>Registree Copy</th>
                        <!--<th>Registree Copy (COAPP)</th>-->
                        <th>Old Registree Copy</th>
                        <!--<th>Old Registree Copy (COAPP)</th>-->
                        <th>Fard Copy</th>
                        <!--<th>Fard Copy (COAPP)</th>-->
                        <th>NOC Copy</th>
                        <!--<th>NOC Copy (COAPP)</th>-->
                        <th>Property Images</th>
                        <!--<th>Property Images (COAPP)</th>-->
                        <th>Video</th>
                        <!--<th>Video (COAPP)</th>-->
                        <th>Geolocation</th>
                        <!--<th>Geolocation (COAPP)</th>-->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="evaluationTableBody">
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['lead_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['verifier_name']); ?></td>
                            <td>
                                    <?php if (!empty($row['registree_copy'])): ?>
                                        <a href="<?php echo htmlspecialchars($row['registree_copy']); ?>" target="_blank">View</a>
                                    <?php else: ?>
                                        No documents uploaded
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['old_registree_copy'])): ?>
                                        <a href="<?php echo htmlspecialchars($row['old_registree_copy']); ?>" target="_blank">View</a>
                                    <?php else: ?>
                                        No documents uploaded
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <?php if (!empty($row['fard_copy'])): ?>
                                        <a href="<?php echo htmlspecialchars($row['fard_copy']); ?>" target="_blank">View</a>
                                    <?php else: ?>
                                        No documents uploaded
                                    <?php endif; ?>
                                </td>
        
                                <td>
                                    <?php if (!empty($row['noc_copy'])): ?>
                                        <a href="<?php echo htmlspecialchars($row['noc_copy']); ?>" target="_blank">View</a>
                                    <?php else: ?>
                                        No documents uploaded
                                    <?php endif; ?>
                                </td>
                                

                            <td>
                                <?php
                                $propertyImages = json_decode($row['property_images'], true);
                                foreach ($propertyImages as $image) {
                                    echo '<a href="' . htmlspecialchars($image) . '" target="_blank">View</a><br>';
                                }
                                ?>
                            </td>
                            
                            <td>
                                <?php
                                $videos = json_decode($row['videos'], true);
                                foreach ($videos as $video) {
                                    echo '<a href="' . htmlspecialchars($video) . '" target="_blank">View</a><br>';
                                }
                                ?>
                            </td>
                            
                            <td>
    <?php
    if (!empty($row['geolocation'])) {
        // Extract latitude and longitude from the geolocation string
        if (preg_match('/Latitude: ([-+]?\d*\.\d+|\d+) Longitude: ([-+]?\d*\.\d+|\d+)/', $row['geolocation'], $matches)) {
            $latitude = $matches[1];
            $longitude = $matches[2];
    ?>
            <iframe src="https://maps.google.com/maps?q=<?php echo urlencode("$latitude,$longitude"); ?>&hl=es;z=14&output=embed" width="200" height="150"></iframe>
            <br>
            <a href="https://maps.google.com/?q=<?php echo urlencode("$latitude,$longitude"); ?>" target="_blank">Open in Google Maps</a>
    <?php
        } else {
            echo "Invalid geolocation format for 'geolocation'";
        }
    } else {
        echo "No geolocation data available for 'geolocation'";
    }
    ?>
</td>




                            <td>
                                <?php 
                                // if ($userRole === 'admin') : 
                                ?>
                                    <!-- Trigger the modal with a button -->
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-<?php echo $row['id']; ?>">Update Status</button>
                                <?php 
                                // endif; 
                                ?>
                            </td>
                        </tr>
                        <!-- Modal -->
                        <div class="modal fade" id="modal-<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabel-<?php echo $row['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel-<?php echo $row['id']; ?>">Update Status for Lead ID <?php echo $row['lead_id']; ?></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="">
                                            <input type="hidden" name="evaluation_id" value="<?php echo $row['id']; ?>">
                                            <div class="form-group">
                                                <label for="registree_status">Registree Status</label>
                                                <select name="registree_status" class="form-control" id="registree_status-<?php echo $row['id']; ?>" onchange="toggleRemarks(this, '<?php echo $row['id']; ?>')">
                                                    <option value="Pending" <?php echo ($row['registree_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Approved" <?php echo ($row['registree_status'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                                    <option value="Rejected" <?php echo ($row['registree_status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="form-group remarks-group-<?php echo $row['id']; ?>" <?php echo ($row['registree_status'] === 'Rejected') ? '' : 'style="display:none;"'; ?>>
                                                <label for="registree_remarks">Registree Remarks</label>
                                                <textarea name="registree_remarks" class="form-control"><?php echo htmlspecialchars($row['registree_remarks']); ?></textarea>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="old_registree_status">Old Registree Status</label>
                                                <select name="old_registree_status" class="form-control" id="old_registree_status-<?php echo $row['id']; ?>" onchange="toggleRemarks(this, '<?php echo $row['id']; ?>', 'old')">
                                                    <option value="Pending" <?php echo ($row['old_registree_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Approved" <?php echo ($row['old_registree_status'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                                    <option value="Rejected" <?php echo ($row['old_registree_status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="form-group remarks-group-<?php echo $row['id']; ?>-old" <?php echo ($row['old_registree_status'] === 'Rejected') ? '' : 'style="display:none;"'; ?>>
                                                <label for="old_registree_remarks">Old Registree Remarks</label>
                                                <textarea name="old_registree_remarks" class="form-control"><?php echo htmlspecialchars($row['old_registree_remarks']); ?></textarea>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="fard_status">Fard Status</label>
                                                <select name="fard_status" class="form-control" id="fard_status-<?php echo $row['id']; ?>" onchange="toggleRemarks(this, '<?php echo $row['id']; ?>', 'fard')">
                                                    <option value="Pending" <?php echo ($row['fard_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Approved" <?php echo ($row['fard_status'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                                    <option value="Rejected" <?php echo ($row['fard_status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="form-group remarks-group-<?php echo $row['id']; ?>-fard" <?php echo ($row['fard_status'] === 'Rejected') ? '' : 'style="display:none;"'; ?>>
                                                <label for="fard_remarks">Fard Remarks</label>
                                                <textarea name="fard_remarks" class="form-control"><?php echo htmlspecialchars($row['fard_remarks']); ?></textarea>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="noc_status">NOC Status</label>
                                                <select name="noc_status" class="form-control" id="noc_status-<?php echo $row['id']; ?>" onchange="toggleRemarks(this, '<?php echo $row['id']; ?>', 'noc')">
                                                    <option value="Pending" <?php echo ($row['noc_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Approved" <?php echo ($row['noc_status'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                                    <option value="Rejected" <?php echo ($row['noc_status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="form-group remarks-group-<?php echo $row['id']; ?>-noc" <?php echo ($row['noc_status'] === 'Rejected') ? '' : 'style="display:none;"'; ?>>
                                                <label for="noc_remarks">NOC Remarks</label>
                                                <textarea name="noc_remarks" class="form-control"><?php echo htmlspecialchars($row['noc_remarks']); ?></textarea>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="video_status">Video Status</label>
                                                <select name="video_status" class="form-control" id="video_status-<?php echo $row['id']; ?>" onchange="toggleRemarks(this, '<?php echo $row['id']; ?>', 'video')">
                                                    <option value="Pending" <?php echo ($row['video_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Approved" <?php echo ($row['video_status'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                                    <option value="Rejected" <?php echo ($row['video_status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="form-group remarks-group-<?php echo $row['id']; ?>-video" <?php echo ($row['video_status'] === 'Rejected') ? '' : 'style="display:none;"'; ?>>
                                                <label for="video_remarks">Video Remarks</label>
                                                <textarea name="video_remarks" class="form-control"><?php echo htmlspecialchars($row['video_remarks']); ?></textarea>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No evaluations found.</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
    // Function to filter table rows based on search input
    $("#searchBar").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#evaluationTableBody tr").filter(function() {
            // Get the text of the first column (ID column) and check if it matches the search value
            var idCellText = $(this).find('td').eq(0).text().toLowerCase();
            $(this).toggle(idCellText.indexOf(value) > -1);
        });
    });
});


        // Function to toggle the remarks field visibility
        function toggleRemarks(selectElement, evaluationID, type = '', coapp = '') {
            let remarksGroupClass = '.remarks-group-' + evaluationID;
            if (type) remarksGroupClass += '-' + type;
            if (coapp) remarksGroupClass += '-' + coapp;

            if (selectElement.value === 'Rejected') {
                $(remarksGroupClass).show();
            } else {
                $(remarksGroupClass).hide();
            }
        }
    </script>
</body>

</html>

