<?php
include 'config.php';

$type = $_GET['type'];

if ($type === 'role') {
    $sql = "SELECT DISTINCT role FROM users";
} else {
    $sql = "SELECT DISTINCT username FROM users";
}

$result = $conn->query($sql);

$options = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $options[] = $type === 'role' ? $row['role'] : $row['username'];
    }
}

echo json_encode($options);
?>
