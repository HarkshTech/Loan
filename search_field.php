<?php
include 'config.php';

// Sanitize the search query
$query = isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '';

$sql = "SELECT * FROM VerificationForms WHERE leadID LIKE ? OR verifierName_Home LIKE ? OR verifierName_Business LIKE ?";
$stmt = $conn->prepare($sql);
$searchTerm = '%' . $query . '%';
$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['leadID']) . '</td>';
        echo '<td>' . htmlspecialchars($row['verifierName_Home']) . '</td>';
        echo '<td><a href="' . htmlspecialchars($row['electricity_bill_home']) . '" target="_blank">View</a></td>';
        echo '<td><a href="' . htmlspecialchars($row['electricity_meter_home']) . '" target="_blank">View</a></td>';
        echo '<td>' . htmlspecialchars($row['verificationStatus_Home']) . '</td>';
        echo '<td>' . htmlspecialchars($row['verificationNotes_Home']) . '</td>';
        echo '<td>';
        if (!empty($row['image_path_home'])) {
            echo '<a href="' . htmlspecialchars($row['image_path_home']) . '" target="_blank">View Image</a>';
        }
        echo '</td>';
        echo '<td>' . htmlspecialchars($row['verifierName_Business']) . '</td>';
        echo '<td><a href="' . htmlspecialchars($row['electricity_bill_business']) . '" target="_blank">View</a></td>';
        echo '<td><a href="' . htmlspecialchars($row['electricity_meter_business']) . '" target="_blank">View</a></td>';
        echo '<td>' . htmlspecialchars($row['verificationStatus_Business']) . '</td>';
        echo '<td>' . htmlspecialchars($row['businessVerificationNotes']) . '</td>';
        echo '<td>';
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
        echo '</td>';
        echo '<td>';
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
        echo '</td>';
        echo '<td>';
        $businessImages = json_decode($row['business_images'], true);
        foreach ($businessImages as $image) {
            echo '<a href="' . htmlspecialchars($image) . '" target="_blank">View</a><br>';
        }
        echo '</td>';
        echo '<td>';
        echo '<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#updateStatusModal' . $row['id'] . '">Update Status</button>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="16">No results found</td></tr>';
}
$stmt->close();
$conn->close();
?>
