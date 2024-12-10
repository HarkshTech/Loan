<?php
include 'config.php';
session_start();
$role = $_SESSION['role'];
date_default_timezone_set('Asia/Kolkata');

// Function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags($data));
}

// Function to update applicant's verification status
function updateApplicantVerificationStatus($conn, $evaluationID, $verificationStatusHome, $verificationNotesHome, $verificationStatusBusiness, $businessVerificationNotes) {
    $stmt = $conn->prepare("UPDATE VerificationForms SET verificationStatus_Home = ?, verificationNotes_Home = ?, verificationStatus_Business = ?, businessVerificationNotes = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $verificationStatusHome, $verificationNotesHome, $verificationStatusBusiness, $businessVerificationNotes, $evaluationID);
    $stmt->execute();
    $stmt->close();
}

// Function to update co-applicant's verification status
function updateCoApplicantVerificationStatus($conn, $evaluationID, $verificationStatusHomeCOAPP, $verificationNotesHomeCOAPP, $verificationStatusBusinessCOAPP, $businessVerificationNotesCOAPP) {
    $stmt = $conn->prepare("UPDATE VerificationForms SET verificationStatus_Home_COAPP = ?, verificationNotes_Home_COAPP = ?, verificationStatus_Business_COAPP = ?, businessVerificationNotes_COAPP = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $verificationStatusHomeCOAPP, $verificationNotesHomeCOAPP, $verificationStatusBusinessCOAPP, $businessVerificationNotesCOAPP, $evaluationID);
    $stmt->execute();
    $stmt->close();
}

// Handle form submission for status update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $evaluationID = intval($_POST['evaluation_id']);

    if (isset($_POST['applicant_form'])) {
        $verificationStatusHome = sanitize($_POST['verification_status_home']);
        $verificationNotesHome = sanitize($_POST['verification_notes_home']);
        $verificationStatusBusiness = sanitize($_POST['verification_status_business']);
        $businessVerificationNotes = sanitize($_POST['business_verification_notes']);

        // Update the applicant's status and remarks in the database
        updateApplicantVerificationStatus($conn, $evaluationID, $verificationStatusHome, $verificationNotesHome, $verificationStatusBusiness, $businessVerificationNotes);
    }

    if (isset($_POST['coapplicant_form'])) {
        $verificationStatusHomeCOAPP = sanitize($_POST['verification_status_home_coapp']);
        $verificationNotesHomeCOAPP = sanitize($_POST['verification_notes_home_coapp']);
        $verificationStatusBusinessCOAPP = sanitize($_POST['verification_status_business_coapp']);
        $businessVerificationNotesCOAPP = sanitize($_POST['business_verification_notes_coapp']);

        // Update the co-applicant's status and remarks in the database
        updateCoApplicantVerificationStatus($conn, $evaluationID, $verificationStatusHomeCOAPP, $verificationNotesHomeCOAPP, $verificationStatusBusinessCOAPP, $businessVerificationNotesCOAPP);
    }
    



    // Update step reached in personalinformation table
    $lid = intval($_POST['leadID']);
    $update = "UPDATE personalinformation SET StepReached='Field Verification Status Updated by ADMIN' WHERE ID=$lid";
    $conn->query($update);
    
    
    // Prepare notification data
    $title = "Field Verification Rejected for ID ({$lid})";
    $message = "Field Verification Rejected for ID ({$lid}). Please reupload the verification documents for it and check remarks for instructions.";
    $nfor = 'verifier';
    $leadID = $lid;

// Check statuses and add notification if rejected
if ($row['verificationStatus_Home'] === 'Rejected' ||
    $row['verificationStatus_Business'] === 'Rejected' ||
    $row['verificationStatus_Home_COAPP'] === 'Rejected' ||
    $row['verificationStatus_Business_COAPP'] === 'Rejected') {
    
    // Assuming $conn is your database connection
    $sql = "INSERT INTO notifications (title, message, nfor, nby, status, created_at) VALUES ('$title', '$message', '$nfor', 'System', 'unread', NOW())";
    $conn->query($sql);
}

    // Get the user role from the session
    $userRole = isset($_SESSION['role']) ? $_SESSION['role'] : '';

    // Determine the redirection URL based on the role
    if ($role=='admin') {
        $redirectUrl = 'verify.php';
        
    } elseif ($role=='branchmanager') {
        $redirectUrl = 'digitalverificationsbm.php';
    }
    // echo $dashboard;
    // echo $redirectUrl;

    echo "<script>
            alert('Form data saved successfully!');
            window.location.href = '$redirectUrl';
          </script>";
    exit();
}

// Define the number of records per page
$recordsPerPage = 10;

// Get the current page number from the URL, default to page 1 if not provided
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the starting point (offset) for the query
$offset = ($currentPage - 1) * $recordsPerPage;

// Fetch the total number of records to calculate total pages
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM VerificationForms");
$totalRow = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];

// Calculate the total number of pages
$totalPages = ceil($totalRecords / $recordsPerPage);

// Fetch the paginated records
$query = "SELECT * FROM VerificationForms LIMIT $recordsPerPage OFFSET $offset";
$result = $conn->query($query);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Verification Status</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 20px;
        }

        .container {
            margin-top: 100px;
            max-width: 1200px;
        }
        thead th {
            position: sticky;
            top: 0; /* Stick to the top when scrolling */
        }
        
        #table-container {
            width: 88vw;         /* Adjust to desired width */
            height: 100vh;       /* Fixed height */
            overflow: auto;      /* Enable scrolling */
            border: 1px solid #ccc;
        }

        table {
            width: 100%;         /* Make the table width 100% of the container */
            border-collapse: collapse;
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
            $dashboard = 'dashboard.php';
        }
        elseif($role=='branchmanager'){
            include 'leftsidebranch.php';
            $dashboard = 'branchmanager.php';
        }
     ?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo $dashboard; ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Legal Verifications Approval</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="my-4">Update Verification Status</h1>
        <div class="form-group">
            <input type="text" id="search" class="form-control" value="<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>" placeholder="Search by Verifier Name (Home or Business)">
        </div>
        <div id="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Lead ID</th>
                        <th>Verifier Name (Home)</th>
                        <th>Electricity Bill (Home)</th>
                        <th>Electricity Meter (Home)</th>
                        <th>Home Verification Status</th>
                        <th>Home Residence Status</th>
                        <th>Verification Notes (Home)</th>
                        <th>Home Image</th>
                        <th>Verifier Name (Business)</th>
                        <th>Electricity Bill (Business)</th>
                        <th>Electricity Meter (Business)</th>
                        <th>Business Verification Status</th>
                        <th>Business Place Status</th>
                        <th>Business Verification Notes</th>
                        <th>Home Geolocation</th>
                        <th>Business Geolocation</th>
                        <th>Business Images</th>
                        <th>Actions</th>
                        <!-- New CO-Applicant Columns -->
                        <th>CO-Applicant Verifier Name (Home)</th>
                        <th>CO-Applicant Electricity Bill (Home)</th>
                        <th>CO-Applicant Electricity Meter (Home)</th>
                        <th>CO-Applicant Home Verification Status</th>
                        <th>CO-Applicant Home Residence Status</th>
                        <th>CO-Applicant Verification Notes (Home)</th>
                        <th>CO-Applicant Home Image</th>
                        <th>CO-Applicant Verifier Name (Business)</th>
                        <th>CO-Applicant Electricity Bill (Business)</th>
                        <th>CO-Applicant Electricity Meter (Business)</th>
                        <th>CO-Applicant Business Verification Status</th>
                        <th>CO-Applicant Business Place Status</th>
                        <th>CO-Applicant Business Verification Notes</th>
                        <th>CO-Applicant Home Geolocation</th>
                        <th>CO-Applicant Business Geolocation</th>
                        <th>CO-Applicant Business Images</th>
                        <th>CO-Applicant Actions</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['leadID']); ?></td>
                            <td><?php echo htmlspecialchars($row['verifierName_Home']); ?></td>
                            <td>
                                <?php if (!empty($row['electricity_bill_home'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['electricity_bill_home']); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    No documents uploaded
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['electricity_meter_home'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['electricity_meter_home']); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    No documents uploaded
                                <?php endif; ?>
                            </td>

                            <td><?php echo htmlspecialchars($row['verificationStatus_Home']); ?></td>
                            <td><?php echo htmlspecialchars($row['homestatus']); ?></td>
                            <td><?php echo htmlspecialchars($row['verificationNotes_Home']); ?></td>
                            <td>
                                <?php
                                $homeImages = json_decode($row['image_path_home'], true);
                                foreach ($homeImages as $image) {
                                    echo '<a href="' . htmlspecialchars($image) . '" target="_blank">View</a><br>';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['verifierName_Business']); ?></td>
                            <td>
                                <?php if (!empty($row['electricity_bill_business'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['electricity_bill_business']); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    No documents uploaded
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['electricity_meter_business'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['electricity_meter_business']); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    No documents uploaded
                                <?php endif; ?>
                            </td>

                            <td><?php echo htmlspecialchars($row['verificationStatus_Business']); ?></td>
                            <td><?php echo htmlspecialchars($row['businessstatus']); ?></td>
                            <td><?php echo htmlspecialchars($row['businessVerificationNotes']); ?></td>
                            <td>
                                <?php
                                preg_match('/Latitude: ([\d.]+) Longitude: ([\d.]+)/', $row['verification_geolocation_home'], $matches);
                                if ($matches) {
                                    $latitude = $matches[1];
                                    $longitude = $matches[2];
                                    $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$latitude},{$longitude}";
                                    echo "<iframe width='200' height='150' frameborder='0' style='border:0' src='https://www.google.com/maps?q={$latitude},{$longitude}&z=14&output=embed'></iframe>";
                                    echo "<br><a href='{$mapsUrl}' target='_blank'>Open in Maps</a>";
                                } else {
                                    echo htmlspecialchars($row['verification_geolocation_home']);
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                preg_match('/Latitude: ([\d.]+) Longitude: ([\d.]+)/', $row['verification_geolocation_business'], $matches);
                                if ($matches) {
                                    $latitude = $matches[1];
                                    $longitude = $matches[2];
                                    $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$latitude},{$longitude}";
                                    echo "<iframe width='200' height='150' frameborder='0' style='border:0' src='https://www.google.com/maps?q={$latitude},{$longitude}&z=14&output=embed'></iframe>";
                                    echo "<br><a href='{$mapsUrl}' target='_blank'>Open in Maps</a>";
                                } else {
                                    echo htmlspecialchars($row['verification_geolocation_business']);
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $businessImages = json_decode($row['business_images'], true);
                                foreach ($businessImages as $image) {
                                    echo '<a href="' . htmlspecialchars($image) . '" target="_blank">View</a><br>';
                                }
                                ?>
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm update-status-btn" data-toggle="modal" data-target="#updateStatusModal<?php echo $row['id']; ?>">Update Status</button>
                            </td>

                            <!-- CO-Applicant Verification Fields -->
                            <td><?php echo htmlspecialchars($row['verifierName_Home_COAPP']); ?></td>
                            <td>
                                <?php if (!empty($row['electricity_bill_home_COAPP'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['electricity_bill_home_COAPP']); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    No documents uploaded
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['electricity_meter_home_COAPP'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['electricity_meter_home_COAPP']); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    No documents uploaded
                                <?php endif; ?>
                            </td>

                            <td><?php echo htmlspecialchars($row['verificationStatus_Home_COAPP']); ?></td>
                            <td><?php echo htmlspecialchars($row['homestatus_COAPP']); ?></td>
                            <td><?php echo htmlspecialchars($row['verificationNotes_Home_COAPP']); ?></td>
                            <td>
                                <?php
                                $homeImagesCOAPP = json_decode($row['image_path_home_COAPP'], true);
                                foreach ($homeImagesCOAPP as $image) {
                                    echo '<a href="' . htmlspecialchars($image) . '" target="_blank">View</a><br>';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['verifierName_Business_COAPP']); ?></td>
                           <td>
                                <?php if (!empty($row['electricity_bill_business_COAPP'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['electricity_bill_business_COAPP']); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    No documents uploaded
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['electricity_meter_business_COAPP'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['electricity_meter_business_COAPP']); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    No documents uploaded
                                <?php endif; ?>
                            </td>

                            <td><?php echo htmlspecialchars($row['verificationStatus_Business_COAPP']); ?></td>
                            <td><?php echo htmlspecialchars($row['businessstatus_COAPP']); ?></td>
                            <td><?php echo htmlspecialchars($row['businessVerificationNotes_COAPP']); ?></td>
                            <td>
                                <?php
                                preg_match('/Latitude: ([\d.]+) Longitude: ([\d.]+)/', $row['verification_geolocation_home_COAPP'], $matches);
                                if ($matches) {
                                    $latitude = $matches[1];
                                    $longitude = $matches[2];
                                    $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$latitude},{$longitude}";
                                    echo "<iframe width='200' height='150' frameborder='0' style='border:0' src='https://www.google.com/maps?q={$latitude},{$longitude}&z=14&output=embed'></iframe>";
                                    echo "<br><a href='{$mapsUrl}' target='_blank'>Open in Maps</a>";
                                } else {
                                    echo htmlspecialchars($row['verification_geolocation_home_COAPP']);
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                preg_match('/Latitude: ([\d.]+) Longitude: ([\d.]+)/', $row['verification_geolocation_business_COAPP'], $matches);
                                if ($matches) {
                                    $latitude = $matches[1];
                                    $longitude = $matches[2];
                                    $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$latitude},{$longitude}";
                                    echo "<iframe width='200' height='150' frameborder='0' style='border:0' src='https://www.google.com/maps?q={$latitude},{$longitude}&z=14&output=embed'></iframe>";
                                    echo "<br><a href='{$mapsUrl}' target='_blank'>Open in Maps</a>";
                                } else {
                                    echo htmlspecialchars($row['verification_geolocation_business_COAPP']);
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $businessImagesCOAPP = json_decode($row['business_images_COAPP'], true);
                                foreach ($businessImagesCOAPP as $image) {
                                    echo '<a href="' . htmlspecialchars($image) . '" target="_blank">View</a><br>';
                                }
                                ?>
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm update-status-btn" data-toggle="modal" data-target="#updateStatusModalCOAPP<?php echo $row['id']; ?>">Update Status</button>
                            </td>
                        </tr>

                        <!-- Modal for updating status -->
                        <div class="modal fade" id="updateStatusModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateStatusModalLabel<?php echo $row['id']; ?>">Update Status for <?php echo htmlspecialchars($row['verifierName_Home']); ?></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="" method="post">
                                        <div class="modal-body">
                                            <input type="hidden" name="evaluation_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="leadID" value="<?php echo $row['leadID']; ?>">

                                            <div class="form-group">
                                                <label for="verification_status_home">Home Verification Status</label>
                                                <select class="form-control" id="verification_status_home<?php echo $row['id']; ?>" name="verification_status_home" onchange="toggleRemarks(this, 'verification_notes_home<?php echo $row['id']; ?>')">
                                                    <option value="Pending" <?php echo $row['verificationStatus_Home'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Approved" <?php echo $row['verificationStatus_Home'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                                    <option value="Rejected" <?php echo $row['verificationStatus_Home'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="form-group" id="verification_notes_home<?php echo $row['id']; ?>" style="display: <?php echo $row['verificationStatus_Home'] == 'Rejected' ? 'block' : 'none'; ?>;">
                                                <label for="verification_notes_home">Home Verification Notes</label>
                                                <textarea class="form-control" name="verification_notes_home"><?php echo htmlspecialchars($row['verificationNotes_Home']); ?></textarea>
                                            </div>

                                            <div class="form-group">
                                                <label for="verification_status_business">Business Verification Status</label>
                                                <select class="form-control" id="verification_status_business<?php echo $row['id']; ?>" name="verification_status_business" onchange="toggleRemarks(this, 'business_verification_notes<?php echo $row['id']; ?>')">
                                                    <option value="Pending" <?php echo $row['verificationStatus_Business'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Approved" <?php echo $row['verificationStatus_Business'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                                    <option value="Rejected" <?php echo $row['verificationStatus_Business'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="form-group" id="business_verification_notes<?php echo $row['id']; ?>" style="display: <?php echo $row['verificationStatus_Business'] == 'Rejected' ? 'block' : 'none'; ?>;">
                                                <label for="business_verification_notes">Business Verification Notes</label>
                                                <textarea class="form-control" name="business_verification_notes"><?php echo htmlspecialchars($row['businessVerificationNotes']); ?></textarea>
                                            </div>
                                            <input type="hidden" name="applicant_form" value="1">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary update-status-btn">Save changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal for updating CO-Applicant status -->
                        <div class="modal fade" id="updateStatusModalCOAPP<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabelCOAPP<?php echo $row['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateStatusModalLabelCOAPP<?php echo $row['id']; ?>">Update Status for CO-Applicant <?php echo htmlspecialchars($row['verifierName_Home_COAPP']); ?></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="" method="post">
                                        <div class="modal-body">
                                            <input type="hidden" name="evaluation_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="leadID" value="<?php echo $row['leadID']; ?>">

                                            <div class="form-group">
                                                <label for="verification_status_home_coapp">CO-Applicant Home Verification Status</label>
                                                <select class="form-control" id="verification_status_home_coapp<?php echo $row['id']; ?>" name="verification_status_home_coapp" onchange="toggleRemarks(this, 'verification_notes_home_coapp<?php echo $row['id']; ?>')">
                                                    <option value="Pending" <?php echo $row['verificationStatus_Home_COAPP'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Approved" <?php echo $row['verificationStatus_Home_COAPP'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                                    <option value="Rejected" <?php echo $row['verificationStatus_Home_COAPP'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="form-group" id="verification_notes_home_coapp<?php echo $row['id']; ?>" style="display: <?php echo $row['verificationStatus_Home_COAPP'] == 'Rejected' ? 'block' : 'none'; ?>;">
                                                <label for="verification_notes_home_coapp">CO-Applicant Home Verification Notes</label>
                                                <textarea class="form-control" name="verification_notes_home_coapp"><?php echo htmlspecialchars($row['verificationNotes_Home_COAPP']); ?></textarea>
                                            </div>

                                            <div class="form-group">
                                                <label for="verification_status_business_coapp">CO-Applicant Business Verification Status</label>
                                                <select class="form-control" id="verification_status_business_coapp<?php echo $row['id']; ?>" name="verification_status_business_coapp" onchange="toggleRemarks(this, 'business_verification_notes_coapp<?php echo $row['id']; ?>')">
                                                    <option value="Pending" <?php echo $row['verificationStatus_Business_COAPP'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Approved" <?php echo $row['verificationStatus_Business_COAPP'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                                    <option value="Rejected" <?php echo $row['verificationStatus_Business_COAPP'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="form-group" id="business_verification_notes_coapp<?php echo $row['id']; ?>" style="display: <?php echo $row['verificationStatus_Business_COAPP'] == 'Rejected' ? 'block' : 'none'; ?>;">
                                                <label for="business_verification_notes_coapp">CO-Applicant Business Verification Notes</label>
                                                <textarea class="form-control" name="business_verification_notes_coapp"><?php echo htmlspecialchars($row['businessVerificationNotes_COAPP']); ?></textarea>
                                            </div>
                                        </div>
                                        <input type="hidden" name="coapplicant_form" value="1">
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary update-status-btn">Save changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php
            // Pagination controls
            echo "<div>";

            // "First" Page link
            if ($currentPage > 1) {
                echo '<a href="?page=1">First</a> ';
            }

            // "Back" link
            if ($currentPage > 1) {
                echo '<a href="?page=' . ($currentPage - 1) . '">Back</a> ';
            }

            // Numbered Page Links
            for ($page = 1; $page <= $totalPages; $page++) {
                // Highlight the current page
                if ($page == $currentPage) {
                    echo '<span>' . $page . '</span> ';
                } else {
                    echo '<a href="?page=' . $page . '">' . $page . '</a> ';
                }
            }

            // "Next" link
            if ($currentPage < $totalPages) {
                echo '<a href="?page=' . ($currentPage + 1) . '">Next</a> ';
            }

            // "Last" Page link
            if ($currentPage < $totalPages) {
                echo '<a href="?page=' . $totalPages . '">Last</a>';
            }

            echo "</div>";
        ?>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Function to toggle verification notes based on status selection
        function toggleRemarks(selectElement, notesFieldId) {
            var selectedValue = selectElement.value;
            var notesField = document.getElementById(notesFieldId);
            if (selectedValue === 'Rejected') {
                notesField.style.display = 'block';
            } else {
                notesField.style.display = 'none';
            }
        }

        $(document).ready(function() {
        // Store all table rows
        var allRows = $('#table-body tr').get();

            // Bind the search functionality
            $('#search').on('keyup', function() {
                var value = $(this).val().toLowerCase();

                // Loop through all rows and filter based on search input
                allRows.forEach(function(row) {
                    var idCellText = $(row).find('td').eq(0).text().toLowerCase();
                    $(row).toggle(idCellText.indexOf(value) > -1);
                });
            });
        });


    </script>
</body>

</html>
