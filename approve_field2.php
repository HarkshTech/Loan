<?php
require 'config.php'; // Database connection

// Pagination setup
$limit = 10; // Rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch paginated data
$query = "SELECT * FROM verificationforms LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

// Count total rows
$totalQuery = "SELECT COUNT(*) AS total FROM verificationforms";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    if (isset($_POST['applicant_update'])) {
        $verificationStatus_Home = $_POST['verificationStatus_Home'];
        $verificationNotes_Home = $_POST['verificationNotes_Home'];
        $verificationStatus_Business = $_POST['verificationStatus_Business'];
        $businessVerificationNotes = $_POST['businessVerificationNotes'];

        $updateQuery = "UPDATE verificationforms 
                        SET verificationStatus_Home = ?, verificationNotes_Home = ?, 
                            verificationStatus_Business = ?, businessVerificationNotes = ? 
                        WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ssssi", $verificationStatus_Home, $verificationNotes_Home, $verificationStatus_Business, $businessVerificationNotes, $id);
        $updateStmt->execute();
    } elseif (isset($_POST['coapplicant_update'])) {
        $verificationStatus_Home_COAPP = $_POST['verificationStatus_Home_COAPP'];
        $verificationNotes_Home_COAPP = $_POST['verificationNotes_Home_COAPP'];
        $verificationStatus_Business_COAPP = $_POST['verificationStatus_Business_COAPP'];
        $businessVerificationNotes_COAPP = $_POST['businessVerificationNotes_COAPP'];

        $updateQuery = "UPDATE verificationforms 
                        SET verificationStatus_Home_COAPP = ?, verificationNotes_Home_COAPP = ?, 
                            verificationStatus_Business_COAPP = ?, businessVerificationNotes_COAPP = ? 
                        WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ssssi", $verificationStatus_Home_COAPP, $verificationNotes_Home_COAPP, $verificationStatus_Business_COAPP, $businessVerificationNotes_COAPP, $id);
        $updateStmt->execute();
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?page=$page");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verification Forms</title>
</head>
<body>
    <h1>Verification Forms</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Lead ID</th>
                <th>Verifier Name (Home)</th>
                <th>Verifier Name (Business)</th>
                <th>Applicant Documents</th>
                <th>Co-Applicant Documents</th>
                <th>Applicant Geolocation</th>
                <th>Co-Applicant Geolocation</th>
                <th>Actions (Applicant)</th>
                <th>Actions (Co-Applicant)</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['leadID'] ?></td>
                    <td><?= $row['verifierName_Home'] ?></td>
                    <td><?= $row['verifierName_Business'] ?></td>
                    <td>
                        <ul>
                            <li><a href="<?= $row['electricity_bill_home'] ?>" target="_blank">Electricity Bill (Home)</a></li>
                            <li><a href="<?= $row['electricity_meter_home'] ?>" target="_blank">Electricity Meter (Home)</a></li>
                            <?php 
                                if (!empty($row['image_path_home']) && $row['image_path_home'] !== 'NULL') {
                                    $documents = json_decode($row['image_path_home'], true) ?: explode(',', $row['image_path_home']);
                                    foreach ($documents as $document) {
                                        $document = trim($document);
                                        if (!empty($document)) {
                                            echo "<li><a href='$document' target='_blank'>" . basename($document) . "</a></li>";
                                        }
                                    }
                                } else {
                                    echo "<li>Document not available</li>";
                                }
                            ?>
                            <li>
    <?php if (!empty($row['electricity_bill_business']) && $row['electricity_bill_business'] !== 'NULL'): ?>
        <a href="<?= $row['electricity_bill_business'] ?>" target="_blank">Electricity Bill (Business)</a>
    <?php else: ?>
        Electricity Bill (Business) NOT UPLOADED
    <?php endif; ?>
</li>
<li>
    <?php if (!empty($row['electricity_meter_business']) && $row['electricity_meter_business'] !== 'NULL'): ?>
        <a href="<?= $row['electricity_meter_business'] ?>" target="_blank">Electricity Meter (Business)</a>
    <?php else: ?>
        Electricity Meter (Business) NOT UPLOADED
    <?php endif; ?>
</li>

                            <?php 
                                if (!empty($row['business_images']) && $row['business_images'] !== 'NULL') {
                                    $documents = json_decode($row['business_images'], true) ?: explode(',', $row['business_images']);
                                    foreach ($documents as $document) {
                                        $document = trim($document);
                                        if (!empty($document)) {
                                            echo "<li><a href='$document' target='_blank'>" . basename($document) . "</a></li>";
                                        }
                                    }
                                } else {
                                    echo "<li>Document not available</li>";
                                }
                            ?>
                        </ul>
                    </td>
                    <td>
                        <ul>
                            <li><a href="<?= $row['electricity_bill_home_COAPP'] ?>" target="_blank">Electricity Bill (Home)</a></li>
                            <li><a href="<?= $row['electricity_meter_home_COAPP'] ?>" target="_blank">Electricity Meter (Home)</a></li>
                            <?php foreach (explode(',', $row['image_path_home_COAPP']) as $image): ?>
                                <li><a href="<?= trim($image) ?>" target="_blank">Home Image</a></li>
                            <?php endforeach; ?>
                            <li><a href="<?= $row['electricity_bill_business_COAPP'] ?>" target="_blank">Electricity Bill (Business)</a></li>
                            <li><a href="<?= $row['electricity_meter_business_COAPP'] ?>" target="_blank">Electricity Meter (Business)</a></li>
                            <?php foreach (explode(',', $row['business_images_COAPP']) as $image): ?>
                                <li><a href="<?= trim($image) ?>" target="_blank">Business Image</a></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                    <td>
                        <iframe 
                            src="https://www.google.com/maps?q=<?= urlencode($row['verification_geolocation_home']) ?>&output=embed" 
                            width="300" 
                            height="200"></iframe>
                        <iframe 
                            src="https://www.google.com/maps?q=<?= urlencode($row['verification_geolocation_business']) ?>&output=embed" 
                            width="300" 
                            height="200"></iframe>
                    </td>
                    <td>
                        <iframe 
                            src="https://www.google.com/maps?q=<?= urlencode($row['verification_geolocation_home_COAPP']) ?>&output=embed" 
                            width="300" 
                            height="200"></iframe>
                        <iframe 
                            src="https://www.google.com/maps?q=<?= urlencode($row['verification_geolocation_business_COAPP']) ?>&output=embed" 
                            width="300" 
                            height="200"></iframe>
                    </td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <select name="verificationStatus_Home">
                                <option value="Approved" <?= $row['verificationStatus_Home'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="Rejected" <?= $row['verificationStatus_Home'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                <option value="Pending" <?= $row['verificationStatus_Home'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            </select>
                            <textarea name="verificationNotes_Home"><?= $row['verificationNotes_Home'] ?></textarea>
                            <select name="verificationStatus_Business">
                                <option value="Approved" <?= $row['verificationStatus_Business'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="Rejected" <?= $row['verificationStatus_Business'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                <option value="Pending" <?= $row['verificationStatus_Business'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            </select>
                            <textarea name="businessVerificationNotes"><?= $row['businessVerificationNotes'] ?></textarea>
                            <button type="submit" name="applicant_update">Update</button>
                        </form>
                    </td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <select name="verificationStatus_Home_COAPP">
                                <option value="Approved" <?= $row['verificationStatus_Home_COAPP'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="Rejected" <?= $row['verificationStatus_Home_COAPP'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                <option value="Pending" <?= $row['verificationStatus_Home_COAPP'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            </select>
                            <textarea name="verificationNotes_Home_COAPP"><?= $row['verificationNotes_Home_COAPP'] ?></textarea>
                            <select name="verificationStatus_Business_COAPP">
                                <option value="Approved" <?= $row['verificationStatus_Business_COAPP'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="Rejected" <?= $row['verificationStatus_Business_COAPP'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                <option value="Pending" <?= $row['verificationStatus_Business_COAPP'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            </select>
                            <textarea name="businessVerificationNotes_COAPP"><?= $row['businessVerificationNotes_COAPP'] ?></textarea>
                            <button type="submit" name="coapplicant_update">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</body>
</html>
