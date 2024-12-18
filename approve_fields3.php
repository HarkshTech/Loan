<?php
// Include database configuration
include 'config.php';

// Pagination setup
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";

// Fetch total records for pagination
$count_query = "SELECT COUNT(*) AS total FROM VerificationForms WHERE 
    leadID LIKE '%$search%' OR 
    verifierName_Home LIKE '%$search%' OR 
    verifierName_Home_COAPP LIKE '%$search%' OR 
    verifierName_Business LIKE '%$search%' OR 
    verifierName_Business_COAPP LIKE '%$search%'";
$total_result = $conn->query($count_query);
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch paginated results with search filter
$query = "SELECT * FROM VerificationForms
          WHERE 
            leadID LIKE '%$search%' OR 
            verifierName_Home LIKE '%$search%' OR 
            verifierName_Home_COAPP LIKE '%$search%' OR 
            verifierName_Business LIKE '%$search%' OR 
            verifierName_Business_COAPP LIKE '%$search%'
          LIMIT $offset, $records_per_page";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Forms</title>
    <style>
        .table-container {
            width: 100%;
            height: 90vh;
            overflow: auto;
        }

        table {
            padding: 0;
            width: 100%;
            /* Table width, can be adjusted as needed */
            border-collapse: collapse;
            /* Optional, for better border handling */
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
            /* Adds borders to table cells */
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        thead th {
            position: sticky;
            top: 0;
            /* Makes the header stick to the top of the container */
            background-color: #f1f1f1;
            /* Optional: to make the header background stand out */
            z-index: 1;
            /* Ensures the header stays on top of the content */
        }

        /*       mandeep css */
        select {
            display: block;
            width: 100%;
            padding: .47rem .75rem;
            font-size: .8125rem;
            font-weight: 400;
            line-height: 1.5;
            color: var(--bs-body-color);
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-color: var(--bs-input-bg);
            background-clip: padding-box;
            border: var(--bs-border-width) solid var(--bs-border-color);
            border-radius: var(--bs-border-radius);
        }

        textarea {
            display: block;
            width: 100%;
            padding: .47rem .75rem;
            font-size: .8125rem;
            font-weight: 400;
            line-height: 1.5;
            color: var(--bs-body-color);
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-color: var(--bs-input-bg);
            background-clip: padding-box;
            border: var(--bs-border-width) solid var(--bs-border-color);
            border-radius: var(--bs-border-radius);
        }
    </style>
</head>

<body>
    <?php include 'leftside.php'; ?>
    <div class="container" style="margin-top:8pc;">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18" style="margin-left:14px;">Welcome !</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo $dashboard; ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Update Verifications Status</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <h1 style="margin-bottom:2pc !important;">Verification Details</h1>
            <!-- Search Form -->
            <form method="get" action="" style="margin-bottom:25px !important;">
                <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search by LeadID or Verifier Name" style="display: inline;width: 25% !important;padding: .47rem .75rem;font-size: .8125rem;font-weight: 400;line-height: 1.5;color: var(--bs-body-color); -webkit-appearance: none; -moz-appearance: none;appearance: none;background-color: var(--bs-input-bg);background-clip: padding-box;border: var(--bs-border-width) solid var(--bs-border-color);border-radius: var(--bs-border-radius);-webkit-transition: border-color .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;transition: border-color .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;">
                <button type="submit" style="display: inline;width: 10% !important;padding: .47rem .75rem;font-size: .8125rem;font-weight: 400;line-height: 1.5;color: var(--bs-body-color); -webkit-appearance: none; -moz-appearance: none;appearance: none;background-color: var(--bs-input-bg);background-clip: padding-box;border: var(--bs-border-width) solid var(--bs-border-color);border-radius: var(--bs-border-radius);-webkit-transition: border-color .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;transition: border-color .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;">Search</button>
            </form>
            <div class="table-container">
                <table border="1" cellpadding="10">
                    <thead>
                        <tr>
                            <th>Lead ID</th>
                            <th>Applicant Documents</th>
                            <th>CO-Applicant Documents</th>
                            <th>Verification Person</th>
                            <th>Residence/Business Place</th>
                            <th>Verification Geolocation (Applicant)</th>
                            <th>Verification Geolocation (CO-APP)</th>
                            <th>Home Status (Applicant)</th>
                            <th>Business Status (Applicant)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['leadID']); ?></td>
                                <td>
                                    <!-- Applicant Documents -->
                                    <ul>
                                        <?php
                                        // Define the applicant documents with labels
                                        $applicantDocs = [
                                            'Electricity Bill (Home)' => $row['electricity_bill_home'],
                                            'Electricity Meter (Home)' => $row['electricity_meter_home'],
                                            'Electricity Bill (Business)' => $row['electricity_bill_business'],
                                            'Electricity Meter (Business)' => $row['electricity_meter_business']
                                        ];

                                        // Loop through and display the documents with appropriate labels
                                        foreach ($applicantDocs as $label => $doc):
                                            if ($doc):
                                                // If document exists, display the link
                                        ?>
                                                <li><strong><?= $label ?>:</strong> <a href="<?= htmlspecialchars($doc); ?>" target="_blank"><?= htmlspecialchars(basename($doc)); ?></a></li>
                                            <?php
                                            else:
                                                // If document does not exist, display a message indicating no document uploaded
                                            ?>
                                                <li><strong><?= $label ?>:</strong> No Documents Uploaded</li>
                                            <?php
                                            endif;
                                        endforeach;

                                        // Handle JSON documents for home and business images
                                        $applicantJSONDocs = array_merge(
                                            json_decode($row['image_path_home'], true) ?: [],
                                            json_decode($row['business_images'], true) ?: []
                                        );

                                        // Display home and business images with appropriate labels
                                        if (count($applicantJSONDocs) > 0):
                                            ?>
                                            <li><strong>Home Images:</strong></li>
                                            <ul>
                                                <?php foreach ($applicantJSONDocs as $doc):
                                                    if ($doc):
                                                ?>
                                                        <li><a href="<?= htmlspecialchars($doc); ?>" target="_blank"><?= htmlspecialchars(basename($doc)); ?></a></li>
                                                    <?php else: ?>
                                                        <li>No Images Uploaded</li>
                                                <?php endif;
                                                endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <li><strong>Home Images:</strong> No Images Uploaded</li>
                                        <?php endif; ?>

                                        <!-- Business Images check -->
                                        <?php if (count(json_decode($row['business_images'], true) ?: []) > 0): ?>
                                            <li><strong>Business Images:</strong></li>
                                            <ul>
                                                <?php foreach (json_decode($row['business_images'], true) ?: [] as $doc):
                                                    if ($doc):
                                                ?>
                                                        <li><a href="<?= htmlspecialchars($doc); ?>" target="_blank"><?= htmlspecialchars(basename($doc)); ?></a></li>
                                                    <?php else: ?>
                                                        <li>No Images Uploaded</li>
                                                <?php endif;
                                                endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <li><strong>Business Images:</strong> No Images Uploaded</li>
                                        <?php endif; ?>
                                    </ul>
                                </td>

                                <td>
                                    <!-- CO-Applicant Documents -->
                                    <ul>
                                        <?php
                                        // Define the co-applicant documents with labels
                                        $coappDocs = [
                                            'Electricity Bill (Home) CO-Applicant' => $row['electricity_bill_home_COAPP'],
                                            'Electricity Meter (Home) CO-Applicant' => $row['electricity_meter_home_COAPP'],
                                            'Electricity Bill (Business) CO-Applicant' => $row['electricity_bill_business_COAPP'],
                                            'Electricity Meter (Business) CO-Applicant' => $row['electricity_meter_business_COAPP']
                                        ];

                                        // Loop through and display the documents with appropriate labels
                                        foreach ($coappDocs as $label => $doc):
                                            if ($doc):
                                                // If document exists, display the link
                                        ?>
                                                <li><strong><?= $label ?>:</strong> <a href="<?= htmlspecialchars($doc); ?>" target="_blank"><?= htmlspecialchars(basename($doc)); ?></a></li>
                                            <?php
                                            else:
                                                // If document does not exist, display a message indicating no document uploaded
                                            ?>
                                                <li><strong><?= $label ?>:</strong> No Documents Uploaded</li>
                                            <?php
                                            endif;
                                        endforeach;

                                        // Handle JSON documents for home and business images for co-applicant
                                        $coappJSONDocs = array_merge(
                                            json_decode($row['image_path_home_COAPP'], true) ?: [],
                                            json_decode($row['business_images_COAPP'], true) ?: []
                                        );

                                        // Display home and business images with appropriate labels
                                        if (count($coappJSONDocs) > 0):
                                            ?>
                                            <li><strong>Home Images (CO-Applicant):</strong></li>
                                            <ul>
                                                <?php foreach ($coappJSONDocs as $doc):
                                                    if ($doc):
                                                ?>
                                                        <li><a href="<?= htmlspecialchars($doc); ?>" target="_blank"><?= htmlspecialchars(basename($doc)); ?></a></li>
                                                    <?php else: ?>
                                                        <li>No Images Uploaded</li>
                                                <?php endif;
                                                endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <li><strong>Home Images (CO-Applicant):</strong> No Images Uploaded</li>
                                        <?php endif; ?>

                                        <!-- Business Images check for CO-Applicant -->
                                        <?php if (count(json_decode($row['business_images_COAPP'], true) ?: []) > 0): ?>
                                            <li><strong>Business Images (CO-Applicant):</strong></li>
                                            <ul>
                                                <?php foreach (json_decode($row['business_images_COAPP'], true) ?: [] as $doc):
                                                    if ($doc):
                                                ?>
                                                        <li><a href="<?= htmlspecialchars($doc); ?>" target="_blank"><?= htmlspecialchars(basename($doc)); ?></a></li>
                                                    <?php else: ?>
                                                        <li>No Images Uploaded</li>
                                                <?php endif;
                                                endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <li><strong>Business Images (CO-Applicant):</strong> No Images Uploaded</li>
                                        <?php endif; ?>
                                    </ul>
                                </td>


                                <td>
                                    <strong>Home Verifier (Applicant):</strong>
                                    <?= !empty($row['verifierName_Home']) ? htmlspecialchars($row['verifierName_Home']) : 'Not done yet'; ?><br>

                                    <strong>Home Verifier (CO-Applicant):</strong>
                                    <?= !empty($row['verifierName_Home_COAPP']) ? htmlspecialchars($row['verifierName_Home_COAPP']) : 'Not done yet'; ?><br>

                                    <strong>Business Verifier (Applicant):</strong>
                                    <?= !empty($row['verifierName_Business']) ? htmlspecialchars($row['verifierName_Business']) : 'Not done yet'; ?><br>

                                    <strong>Business Verifier (CO-Applicant):</strong>
                                    <?= !empty($row['verifierName_Business_COAPP']) ? htmlspecialchars($row['verifierName_Business_COAPP']) : 'Not done yet'; ?>
                                </td>
                                <td>
                                    <!-- Residence and Business Status (Applicant) and (CO-Applicant) -->
                                    <strong>Home Residence Status (Applicant):</strong><br>
                                    <?php if (!empty($row['homestatus'])): ?>
                                        <?= htmlspecialchars($row['homestatus']); ?>
                                    <?php else: ?>
                                        <span>Not done yet</span>
                                    <?php endif; ?>
                                    <br>

                                    <strong>Business Place Status (Applicant):</strong><br>
                                    <?php if (!empty($row['businessstatus'])): ?>
                                        <?= htmlspecialchars($row['businessstatus']); ?>
                                    <?php else: ?>
                                        <span>Not done yet</span>
                                    <?php endif; ?>
                                    <br>

                                    <strong>Home Residence Status (CO-Applicant):</strong><br>
                                    <?php if (!empty($row['homestatus_COAPP'])): ?>
                                        <?= htmlspecialchars($row['homestatus_COAPP']); ?>
                                    <?php else: ?>
                                        <span>Not done yet</span>
                                    <?php endif; ?>
                                    <br>

                                    <strong>Business Place Status (CO-Applicant):</strong><br>
                                    <?php if (!empty($row['businessstatus_COAPP'])): ?>
                                        <?= htmlspecialchars($row['businessstatus_COAPP']); ?>
                                    <?php else: ?>
                                        <span>Not done yet</span>
                                    <?php endif; ?>
                                    <br>
                                </td>



                                <td>
                                    <!-- Home and Business Verification Geolocation (Applicant) -->
                                    <strong>Home (Applicant):</strong><br>
                                    <?php
                                    // Extract latitude and longitude for Home (Applicant)
                                    preg_match('/Latitude:\s*(-?\d+\.\d+)\s*Longitude:\s*(-?\d+\.\d+)/', $row['verification_geolocation_home'], $matches);
                                    if (count($matches) == 3): // Match found
                                        $latitude = $matches[1];
                                        $longitude = $matches[2];
                                    ?>
                                        <a href="https://www.google.com/maps?q=<?= $latitude ?>,<?= $longitude ?>" target="_blank">
                                            <iframe src="https://www.google.com/maps?q=<?= $latitude ?>,<?= $longitude ?>&output=embed" width="200" height="150"></iframe>
                                        </a><br>
                                    <?php else: ?>
                                        <span>Not done yet</span><br>
                                    <?php endif; ?>

                                    <strong>Business (Applicant):</strong><br>
                                    <?php
                                    // Extract latitude and longitude for Business (Applicant)
                                    preg_match('/Latitude:\s*(-?\d+\.\d+)\s*Longitude:\s*(-?\d+\.\d+)/', $row['verification_geolocation_business'], $matches);
                                    if (count($matches) == 3): // Match found
                                        $latitude = $matches[1];
                                        $longitude = $matches[2];
                                    ?>
                                        <a href="https://www.google.com/maps?q=<?= $latitude ?>,<?= $longitude ?>" target="_blank">
                                            <iframe src="https://www.google.com/maps?q=<?= $latitude ?>,<?= $longitude ?>&output=embed" width="200" height="150"></iframe>
                                        </a><br>
                                    <?php else: ?>
                                        <span>Not done yet</span><br>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <!-- Home and Business Verification Geolocation (CO-Applicant) -->
                                    <strong>Home (CO-Applicant):</strong><br>
                                    <?php
                                    // Extract latitude and longitude for Home (CO-Applicant)
                                    preg_match('/Latitude:\s*(-?\d+\.\d+)\s*Longitude:\s*(-?\d+\.\d+)/', $row['verification_geolocation_home_COAPP'], $matches);
                                    if (count($matches) == 3): // Match found
                                        $latitude = $matches[1];
                                        $longitude = $matches[2];
                                    ?>
                                        <a href="https://www.google.com/maps?q=<?= $latitude ?>,<?= $longitude ?>" target="_blank">
                                            <iframe src="https://www.google.com/maps?q=<?= $latitude ?>,<?= $longitude ?>&output=embed" width="200" height="150"></iframe>
                                        </a><br>
                                    <?php else: ?>
                                        <span>Not done yet</span><br>
                                    <?php endif; ?>

                                    <strong>Business (CO-Applicant):</strong><br>
                                    <?php
                                    // Extract latitude and longitude for Business (CO-Applicant)
                                    preg_match('/Latitude:\s*(-?\d+\.\d+)\s*Longitude:\s*(-?\d+\.\d+)/', $row['verification_geolocation_business_COAPP'], $matches);
                                    if (count($matches) == 3): // Match found
                                        $latitude = $matches[1];
                                        $longitude = $matches[2];
                                    ?>
                                        <a href="https://www.google.com/maps?q=<?= $latitude ?>,<?= $longitude ?>" target="_blank">
                                            <iframe src="https://www.google.com/maps?q=<?= $latitude ?>,<?= $longitude ?>&output=embed" width="200" height="150"></iframe>
                                        </a><br>
                                    <?php else: ?>
                                        <span>Not done yet</span><br>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <button class="btn btn-primary btn-sm update-status-btn" data-bs-toggle="modal" data-bs-target="#statusModalApplicant<?= $row['leadID']; ?>">Update Status (Applicant)</button>
                                    <!-- Modal for Applicant Status -->
                                    <div class="modal fade" id="statusModalApplicant<?= $row['leadID']; ?>" tabindex="-1" aria-labelledby="statusLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="statusLabel">Update Verification Status (Applicant)</h5>
                                                </div>
                                                <form action="update_field_verification_status.php" method="post">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="leadID" value="<?= $row['leadID']; ?>">

                                                        <!-- Home Status Applicant -->
                                                        <label for="home_status_applicant">Home Status:</label>
                                                        <select name="home_status_applicant" required>
                                                            <option value="Pending" <?= $row['verificationStatus_Home'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="Approved" <?= $row['verificationStatus_Home'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                                            <option value="Rejected" <?= $row['verificationStatus_Home'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                        </select>
                                                        <br><br>
                                                        <label for="home_verification_notes_applicant">Home Status Notes:</label>
                                                        <textarea name="home_verification_notes_applicant" rows="3"><?= htmlspecialchars($row['verificationNotes_Home']); ?></textarea>
                                                        <br><br>

                                                        <!-- Business Status Applicant -->
                                                        <label for="business_status_applicant">Business Status:</label>
                                                        <select name="business_status_applicant" required>
                                                            <option value="Pending" <?= $row['verificationStatus_Business'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="Approved" <?= $row['verificationStatus_Business'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                                            <option value="Rejected" <?= $row['verificationStatus_Business'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                        </select>
                                                        <br><br>
                                                        <label for="business_verification_notes_applicant">Business Status Notes:</label>
                                                        <textarea name="business_verification_notes_applicant" rows="3"><?= htmlspecialchars($row['businessVerificationNotes']); ?></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Save</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm update-status-btn" data-bs-toggle="modal" data-bs-target="#statusModalCOAPP<?= $row['leadID']; ?>">Update Status (CO-Applicant)</button>
                                    <!-- Modal for CO-Applicant Status -->
                                    <div class="modal fade" id="statusModalCOAPP<?= $row['leadID']; ?>" tabindex="-1" aria-labelledby="statusCOAPPLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="statusCOAPPLabel">Update Verification Status (CO-Applicant)</h5>
                                                </div>
                                                <form action="update_field_verification_status.php" method="post">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="leadID" value="<?= $row['leadID']; ?>">

                                                        <!-- Home Status CO-Applicant -->
                                                        <label for="home_status_coapp">Home Status:</label>
                                                        <select name="home_status_coapp" required>
                                                            <option value="Pending" <?= $row['verificationStatus_Home_COAPP'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="Approved" <?= $row['verificationStatus_Home_COAPP'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                                            <option value="Rejected" <?= $row['verificationStatus_Home_COAPP'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                        </select>
                                                        <br><br>
                                                        <label for="home_verification_notes_coapp">Home Status Notes:</label>
                                                        <textarea name="home_verification_notes_coapp" rows="3"><?= htmlspecialchars($row['verificationNotes_Home_COAPP']); ?></textarea>
                                                        <br><br>

                                                        <!-- Business Status CO-Applicant -->
                                                        <label for="business_status_coapp">Business Status:</label>
                                                        <select name="business_status_coapp" required>
                                                            <option value="Pending" <?= $row['verificationStatus_Business_COAPP'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="Approved" <?= $row['verificationStatus_Business_COAPP'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                                            <option value="Rejected" <?= $row['verificationStatus_Business_COAPP'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                        </select>
                                                        <br><br>
                                                        <label for="business_verification_notes_coapp">Business Status Notes:</label>
                                                        <textarea name="business_verification_notes_coapp" rows="3"><?= htmlspecialchars($row['businessVerificationNotes_COAPP']); ?></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Save</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1; ?>&search=<?= htmlspecialchars($search); ?>">Previous</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?= $i; ?>&search=<?= htmlspecialchars($search); ?>"><?= $i; ?></a></li>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1; ?>&search=<?= htmlspecialchars($search); ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
</body>

</html>