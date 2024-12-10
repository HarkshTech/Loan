<?php
session_start();
include 'config.php';

// Set the time zone to Asia/Kolkata in PHP
date_default_timezone_set('Asia/Kolkata');

// Set the time zone to Asia/Kolkata in MySQL
$conn->query("SET time_zone = '+05:30'"); // Or use "SET time_zone = 'Asia/Kolkata'"

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Function to fetch logs based on search parameters
function fetchLogs($conn, $searchUsername, $searchDate) {
    $query = "SELECT log_id, username, role, login_time, logout_time, ip_address, latitude, longitude FROM user_session_logs WHERE 1=1";
    if (!empty($searchUsername)) {
        $query .= " AND username LIKE '%$searchUsername%'";
    }
    if (!empty($searchDate)) {
        $query .= " AND DATE(login_time) = '$searchDate'";
    }
    $query .= " ORDER BY login_time DESC";
    $result = $conn->query($query);

    $logs = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    }
    return $logs;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    $searchUsername = $_POST['username'] ?? '';
    $searchDate = $_POST['date'] ?? '';
    $logs = fetchLogs($conn, $searchUsername, $searchDate);
    echo json_encode($logs);
    $conn->close();
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Session Logs</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 100px;
        }
        .table thead th {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .map-link {
            color: #007bff;
        }
        .map-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }
        .alert {
            margin-top: 20px;
        }
        .iframe-container {
    position: relative;
    width: 100%;
    padding-top: 56.25%; /* 16:9 Aspect Ratio */
    margin-bottom: 1rem; /* Optional: add some spacing between iframes */
}

.iframe-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

.iframe-container a {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: block;
    z-index: 1;
    text-indent: -9999px; /* Hide the text */
}

    </style>
</head>
<body>
    <?php include 'leftside.php';?>
<div class="container">
    <h1 class="mb-4">User Session Logs</h1>
    <form class="form-inline mb-4" id="searchForm">
        <div class="form-group">
            <label for="username" class="mr-2">Username:</label>
            <input type="text" class="form-control" id="username" name="username">
        </div>
        <div class="form-group">
            <label for="date" class="mr-2">Date:</label>
            <input type="date" class="form-control" id="date" name="date">
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-bordered" id="logsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                    <th>IP Address</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="alert alert-warning d-none" id="noResultsAlert" role="alert">
        No session logs found.
    </div>
</div>

<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    function fetchLogs() {
        $.ajax({
            url: '',
            method: 'POST',
            data: {
                username: $('#username').val(),
                date: $('#date').val(),
                ajax: true
            },
            dataType: 'json',
            success: function(response) {
                var logsTableBody = $('#logsTable tbody');
                logsTableBody.empty();

                if (response.length === 0) {
                    $('#noResultsAlert').removeClass('d-none');
                } else {
                    $('#noResultsAlert').addClass('d-none');
                    response.forEach(function(log) {
                        var locationCell = log.latitude && log.longitude
                            ? `<div class="iframe-container">
                                <iframe src="https://www.google.com/maps?q=${log.latitude},${log.longitude}&hl=es;z=14&output=embed"></iframe>
                                <a href="https://www.google.com/maps?q=${log.latitude},${log.longitude}" target="_blank">View Location</a>
                               </div>`
                            : 'No Location Data';

                        var row = `<tr>
                            <td>${log.log_id}</td>
                            <td>${log.username}</td>
                            <td>${log.role}</td>
                            <td>${log.login_time}</td>
                            <td>${log.logout_time ?? 'N/A'}</td>
                            <td>${log.ip_address}</td>
                            <td>${locationCell}</td>
                        </tr>`;
                        logsTableBody.append(row);
                    });
                }
            }
        });
    }

    $('#username, #date').on('input change', fetchLogs);

    // Initial fetch
    fetchLogs();
});

</script>
</body>
</html>
