<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); // Start the session

// Database connection parameters
include 'config.php';

date_default_timezone_set('Asia/Kolkata');

// Set the time zone to Asia/Kolkata in MySQL
// $conn->query("SET time_zone = '+05:30'");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    // Prepare the data from the form
    $form_username = $_POST['username'];
    $form_password = md5($_POST['password']); // MD5 encryption
    $form_role = $_POST['role'];
    $form_branchname = $_POST['branchname'] ?? NULL;
    $form_branchmanager = $_POST['branchmanager'] ?? NULL;
    $created_by = "admin"; // Assuming the user is created by an admin; this can be dynamic
    $creation_time = date("Y-m-d H:i:s"); // Current timestamp
    $status='active';

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO users (username, password, role, branchname, branchmanager, created_by, creation_time,status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $form_username, $form_password, $form_role, $form_branchname, $form_branchmanager, $created_by, $creation_time, $status);

    // Execute the statement
    if ($stmt->execute()) {
        $message = "<p class='success'>New user created successfully</p>";
    } else {
        $message = "<p class='error'>Error: " . $stmt->error . "</p>";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}

if (isset($_GET['branchname'])) {
    $branchname = $_GET['branchname'];

    // Fetch branch managers for the selected branch
    $stmt = $conn->prepare("SELECT username FROM users WHERE branchname = ? AND role = 'branchmanager'");
    $stmt->bind_param("s", $branchname);
    $stmt->execute();
    $result = $stmt->get_result();

    $managers = array();
    while ($row = $result->fetch_assoc()) {
        $managers[] = $row;
    }

    echo json_encode($managers);

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
            color: #333;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
            color: #555;
        }

        input[type="text"], input[type="password"], select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            background-color: #28a745;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .success {
            color: #28a745;
            font-weight: bold;
            text-align: center;
        }

        .error {
            color: #dc3545;
            font-weight: bold;
            text-align: center;
        }

        .navbar {
            background-color: #333;
            overflow: hidden;
        }

        .navbar ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        .navbar ul li {
            float: left;
        }

        .navbar ul li a {
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }

        .navbar ul li a:hover {
            background-color: #575757;
        }
    </style>
    <script>
        async function fetchBranchManagers(branchName) {
            if (branchName) {
                const response = await fetch('create_user.php?branchname=' + branchName);
                const managers = await response.json();
                
                const branchManagerSelect = document.getElementById('branchmanager');
                branchManagerSelect.innerHTML = '';

                managers.forEach(manager => {
                    const option = document.createElement('option');
                    option.value = manager.username;
                    option.textContent = manager.username;
                    branchManagerSelect.appendChild(option);
                });
            }
        }

        function toggleBranchFields() {
            var role = document.getElementById('role').value;
            var branchNameField = document.getElementById('branchname-field');
            var branchManagerField = document.getElementById('branchmanager-field');

            if (role === 'sales' || role === 'accounts' || role === 'recovery' || role === 'verifier') {
                branchNameField.style.display = 'block';
                branchManagerField.style.display = 'block';
            } else if (role === 'branchmanager') {
                branchNameField.style.display = 'block';
                branchManagerField.style.display = 'none';
            } else {
                branchNameField.style.display = 'none';
                branchManagerField.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <?php include 'leftside.php'; ?>
    <div class="container">
        <div class="register-form">
            <h2>Register</h2>
            <?php echo $message; ?>
            <form action="create_user.php" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required><br><br>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br><br>
                
                <label for="role">Role:</label>
                <select id="role" name="role" onchange="toggleBranchFields()" required>
                    <option value="admin">Admin</option>
                    <option value="sales">Sales</option>
                    <option value="accounts">Accounts</option>
                    <option value="recovery">Recovery</option>
                    <option value="branchmanager">Branch Manager</option>
                    <option value="verifier">Verification</option>
                </select><br><br>
                
                <div id="branchname-field" style="display:none;">
                    <label for="branchname">Branch Name:</label>
                    <select id="branchname" name="branchname" onchange="fetchBranchManagers(this.value)">
                        <option value="">Select Branch</option>
                        <?php
                        // Fetch branch names from the database
                        $result = $conn->query("SELECT BranchName FROM branches");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['BranchName']}'>{$row['BranchName']}</option>";
                        }
                        ?>
                    </select><br><br>
                </div>
                
                <div id="branchmanager-field" style="display:none;">
                    <label for="branchmanager">Branch Manager:</label>
                    <select id="branchmanager" name="branchmanager">
                        <option value="">Select Branch Manager</option>
                    </select><br><br>
                </div>
                
                <input type="submit" value="Register">
            </form>
        </div>
    </div>
</body>
</html>
