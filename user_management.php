<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Include database connection file
require('config.php');
// include 'leftside.php';

// Fetch all users
$users = [];
$result = $conn->query("SELECT * FROM users");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Calculate user statistics
$totalUsers = count($users);
$activeUsers = 0;
$nonActiveUsers = 0;

foreach ($users as $user) {
    if ($user['status'] == 'active') {
        $activeUsers++;
    } else {
        $nonActiveUsers++;
    }
}

// Function to sanitize user inputs
function sanitize($data) {
    return htmlspecialchars($data); // Prevent XSS attacks
}

// Function to format creation time
function formatCreationTime($time) {
    return date('F j, Y, g:i a', strtotime($time)); // Format date nicely
}

// Handle delete user action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM users WHERE id = $id");
    // Redirect back to user management page
    header("Location: user_management.php");
    exit();
}

// Handle disable user action
if (isset($_GET['action']) && $_GET['action'] == 'disable' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("UPDATE users SET status = 'disabled' WHERE id = $id");
    // Redirect back to user management page
    header("Location: user_management.php");
    exit();
}

// Handle enable user action
if (isset($_GET['action']) && $_GET['action'] == 'enable' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("UPDATE users SET status = 'active' WHERE id = $id");
    // Redirect back to user management page
    header("Location: user_management.php");
    exit();
}

// Handle reset password action
if (isset($_POST['reset_password'])) {
    $id = $_POST['id'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword === $confirmPassword) {
        $hashedPassword = md5($newPassword);
        $conn->query("UPDATE users SET password = '$hashedPassword' WHERE id = $id");
        // Redirect back to user management page
        header("Location: user_management.php");
        exit();
    } else {
        echo "Passwords do not match!";
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            margin-top:100px !important;
            width: 100%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
          
        }
        
        #table{
            overflow-x:auto;
        }
        .table th, .table td {
            vertical-align: middle;
            
        }
        .button {
            padding: 8px 16px;
            border-radius: 5px;
        }
        .delete-btn {
            background-color: #dc3545;
            color: #fff;
            border: none;
        }
        .disable-btn {
            background-color: #ffc107;
            color: #212529;
            border: none;
        }
        .enable-btn {
            background-color: #28a745;
            color: #fff;
            border: none;
        }
        .forgot-btn {
            background-color: #17a2b8;
            color: #fff;
            border: none;
        }
        
         @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
        }
    }
      
 
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">User Management</h1>
        
        <!-- User Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <p class="card-text"><?php echo $totalUsers; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Active Users</h5>
                        <p class="card-text"><?php echo $activeUsers; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h5 class="card-title">Non-active Users</h5>
                        <p class="card-text"><?php echo $nonActiveUsers; ?></p>
                    </div>
                </div>
            </div>
        </div>
<div class="mb-3">
    <input type="text" id="search-input" class="form-control" placeholder="Search by Username or Role">
</div>
        <!-- User Table -->
        <h2 class="mb-3">All Users</h2>
        
        <table class="table">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Branch Name</th>
                    <th>Created By</th>
                    <th>Creation Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo sanitize($user['username']); ?></td>
                        <td><?php echo sanitize($user['role']); ?></td>
                        <td><?php echo sanitize($user['branchname']); ?></td>
                        <td><?php echo sanitize($user['created_by']); ?></td>
                        <td><?php echo formatCreationTime($user['creation_time']); ?></td>
                        <td>
                            <!--<a href="user_management.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>-->
                            <?php if ($user['status'] == 'active'): ?>
                                <a href="user_management.php?action=disable&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning disable-btn">Disable</a>
                            <?php else: ?>
                                <a href="user_management.php?action=enable&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success enable-btn">Enable</a>
                            <?php endif; ?>
                            <a href="javascript:void(0);" class="btn btn-sm btn-info forgot-btn" data-toggle="modal" data-target="#resetPasswordModal" data-id="<?php echo $user['id']; ?>">Forgot Password</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>


        <!-- Add New User Button -->
        <!--<a href="add_user.php" class="btn btn-primary">Add New User</a>-->
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="user_management.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="reset-id">
                        <div class="form-group">
                            <label for="new-password">New Password</label>
                            <input type="password" class="form-control" id="new-password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm-password">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="reset_password" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    // Populate the reset password modal with user data
    $('#resetPasswordModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        
        var modal = $(this);
        modal.find('#reset-id').val(id);
    });
</script>
<script>
    document.getElementById('search-input').addEventListener('keyup', function() {
        var query = this.value;

        // Create an AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'search_users.php?query=' + query, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.querySelector('tbody').innerHTML = xhr.responseText;
            } else {
                console.error('Failed to fetch data');
            }
        };
        xhr.send();
    });
</script>

</body>
</html>

