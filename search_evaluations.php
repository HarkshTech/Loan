<?php
include 'config.php';
session_start();

function sanitize($data) {
    return htmlspecialchars(strip_tags($data));
}

$search = sanitize($_GET['search'] ?? '');

$sql = "SELECT * FROM legal_evaluations WHERE lead_id LIKE ? OR verifier_name LIKE ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['lead_id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['verifier_name']) . '</td>';
        echo '<td><a href="' . htmlspecialchars($row['registree_copy']) . '" target="_blank">View</a></td>';
        echo '<td><a href="' . htmlspecialchars($row['old_registree_copy']) . '" target="_blank">View</a></td>';
        echo '<td><a href="' . htmlspecialchars($row['fard_copy']) . '" target="_blank">View</a></td>';
        echo '<td><a href="' . htmlspecialchars($row['noc_copy']) . '" target="_blank">View</a></td>';
        echo '<td>';
        $propertyImages = json_decode($row['property_images'], true);
        foreach ($propertyImages as $image) {
            echo '<a href="' . htmlspecialchars($image) . '" target="_blank">View</a><br>';
        }
        echo '</td>';
        echo '<td>';
        $videos = json_decode($row['videos'], true);
        foreach ($videos as $video) {
            echo '<a href="' . htmlspecialchars($video) . '" target="_blank">View</a><br>';
        }
        echo '</td>';
        echo '<td>';
        preg_match('/Latitude: ([\d.]+) Longitude: ([\d.]+)/', $row['geolocation'], $matches);
        if ($matches) {
            $latitude = $matches[1];
            $longitude = $matches[2];
            $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$latitude},{$longitude}";
            echo "<iframe width='200' height='150' frameborder='0' style='border:0' src='https://www.google.com/maps?q={$latitude},{$longitude}&z=14&output=embed'></iframe>";
            echo "<br><a href='{$mapsUrl}' target='_blank'>Open in Maps</a>";
        } else {
            echo htmlspecialchars($row['geolocation']);
        }
        echo '</td>';
        echo '<td>';
        if ($_SESSION['role'] === 'admin') {
            echo '<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#updateStatusModal' . $row['id'] . '">Update Status</button>';
        } else {
            echo '<button class="btn btn-secondary btn-sm" disabled>Update Status</button>';
        }
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="10">No evaluations found.</td></tr>';
}
?>
