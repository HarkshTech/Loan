<?php
session_start(); // Start the session
include 'config.php'; // Include your database connection file


// Check if the user is logged in and has the appropriate role to view notifications
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'branchmanager') {
    // Redirect to the login page or another appropriate page
    header("Location: index.php");
    exit();
}

// Fetch user's name for display
$username = $_SESSION['username'];
$role= $_SESSION['role'];

if($role==='admin'){
    include 'leftside.php';
}
elseif($role==='branchmanager'){
    include 'leftsidebranch.php';
}

// Fetch notifications from the database based on user's role
$query = "SELECT * FROM notifications WHERE nfor = '{$_SESSION['role']}' OR nfor='$username' ORDER BY id DESC";
$result = mysqli_query($conn, $query);



// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Notifications</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* Custom styles for notification table */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            margin-top: 100px;
        }

        .notification-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
        }

        .notification-table th,
        .notification-table td {
            padding: 12px;
            text-align: center;
            vertical-align: middle;
        }

        .notification-read {
            color: green;
        }

        .notification-unread {
            color: red;
        }

        .no-notifications {
            text-align: center;
            padding: 20px;
            font-size: 18px;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h1 class="mb-4 text-center">Instructions For  <?php echo $username; ?></h1>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover notification-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Message</th>
                                        <th>Notification For</th>
                                        <th>Notification By</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $statusIcon = $row['status'] === 'read' ? '<i class="bi bi-check2 notification-read"></i>' : '<i class="bi bi-check notification-unread"></i>';
                                            echo "<tr>
                                                    <td>{$row['title']}</td>
                                                    <td>{$row['message']}</td>
                                                    <td>{$row['nfor']}</td>
                                                    <td>{$row['nby']}</td>
                                                    <td>{$row['status']} {$statusIcon}</td>
                                                </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='no-notifications'>No notifications found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap icons -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/bootstrap-icons.min.js"></script>
</body>

</html>
