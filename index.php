<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'config.php';
    date_default_timezone_set('Asia/Kolkata');

// Set the time zone to Asia/Kolkata in MySQL
$conn->query("SET time_zone = '+05:30'");

    $form_username = $_POST['username'];
    $form_password = md5($_POST['password']); // MD5 encryption
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the IP address

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND password = ? AND status='active'");
    $stmt->bind_param("ss", $form_username, $form_password);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $username, $password, $role);
        $stmt->fetch();

        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        $_SESSION['user_id'] = $user_id;

        // Insert session log
        $login_time = date('Y-m-d H:i:s');
        $log_stmt = $conn->prepare("INSERT INTO user_session_logs (user_id, username, role, login_time, ip_address, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $log_stmt->bind_param("issssdd", $user_id, $username, $role, $login_time, $ip_address, $latitude, $longitude);
        $log_stmt->execute();

        switch ($role) {
            case "admin":
                header("Location: dashboard.php");
                break;
            case "sales":
                header("Location: dashboardsales.php");
                break;
            case "validator":
                header("Location: dashboardaccounts.php");
                break;
            case "accounts":
                header("Location: dashboardapproved_loans.php");
                break;
            case "recovery":
                header("Location: dashboardrecovery.php");
                break;
            case "payment":
                header("Location: dashboardpayment.php");
                break;
            case "branchmanager":
                header("Location: branchmanager.php");
                break;
            case "verifier":
                header("Location: dashboardverifier.php");
                break;
            default:
                header("Location: default.php");
                break;
        }
        exit();
    } else {
        echo "<script>alert('Incorrect username or password, OR ID disabled by ADMIN');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Source+Sans+Pro:200,300');
        .wrapper {
            background: linear-gradient(to bottom right, #1f76cd 0%, #a5c6e7 80%);
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 640px;
            margin-top: -300px;
            overflow: hidden;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 161px 0;
            height: 400px;
            text-align: center;
        }
        .container h1 {
            font-size: 40px;
            transition-duration: 1s;
            transition-timing-function: ease-in-out;
            font-weight: 200;
            color: white;
        }
        form {
            padding: 20px 0;
            position: relative;
            z-index: 2;
        }
        form input {
            appearance: none;
            outline: 0;
            border: 1px solid rgba(255, 255, 255, 0.4);
            background-color: rgba(255, 255, 255, 0.2);
            width: 250px;
            border-radius: 3px;
            padding: 10px 15px;
            margin: 0 auto 10px auto;
            display: block;
            text-align: center;
            font-size: 18px;
            color: white;
            transition-duration: 0.25s;
            font-weight: 300;
        }
        form input:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }
        form input:focus {
            background-color: white;
            width: 300px;
            color: #1C84EE;
        }
        form button {
            appearance: none;
            outline: 0;
            background-color: white;
            border: 0;
            padding: 10px 15px;
            color: #1C84EE;
            border-radius: 3px;
            width: 250px;
            cursor: pointer;
            font-size: 18px;
            transition-duration: 0.25s;
        }
        form button:hover {
            background-color: #dae6f2;
        }
        .bg-bubbles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        .bg-bubbles li {
            position: absolute;
            list-style: none;
            display: block;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.15);
            bottom: -160px;
            animation: square 25s infinite;
            transition-timing-function: linear;
        }
        .bg-bubbles li:nth-child(1) {
            left: 10%;
        }
        .bg-bubbles li:nth-child(2) {
            left: 20%;
            width: 80px;
            height: 80px;
            animation-delay: 2s;
            animation-duration: 17s;
        }
        .bg-bubbles li:nth-child(3) {
            left: 25%;
            animation-delay: 4s;
        }
        .bg-bubbles li:nth-child(4) {
            left: 40%;
            width: 60px;
            height: 60px;
            animation-duration: 22s;
            background-color: rgba(255, 255, 255, 0.25);
        }
        .bg-bubbles li:nth-child(5) {
            left: 70%;
        }
        .bg-bubbles li:nth-child(6) {
            left: 80%;
            width: 120px;
            height: 120px;
            animation-delay: 3s;
            background-color: rgba(255, 255, 255, 0.2);
        }
        .bg-bubbles li:nth-child(7) {
            left: 32%;
            width: 160px;
            height: 160px;
            animation-delay: 7s;
        }
        .bg-bubbles li:nth-child(8) {
            left: 55%;
            width: 20px;
            height: 20px;
            animation-delay: 15s;
            animation-duration: 40s;
        }
        .bg-bubbles li:nth-child(9) {
            left: 25%;
            width: 10px;
            height: 10px;
            animation-delay: 2s;
            animation-duration: 40s;
            background-color: rgba(255, 255, 255, 0.3);
        }
        .bg-bubbles li:nth-child(10) {
            left: 90%;
            width: 160px;
            height: 160px;
            animation-delay: 11s;
        }
        @keyframes square {
            0% {
                transform: translateY(0);
            }
            100% {
                transform: translateY(-700px) rotate(600deg);
            }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="container">
        <h1>Welcome</h1>
        <form class="form" id="login-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="text" placeholder="Username" name="username" required>
            <input type="password" placeholder="Password" name="password" required>
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">
            <button type="submit" id="login-button">Login</button>
        </form>
    </div>
    <ul class="bg-bubbles">
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
    </ul>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            $('#latitude').val(position.coords.latitude);
            $('#longitude').val(position.coords.longitude);
        });
    }

    $("#login-button").click(function(event) {
        event.preventDefault();
        $('form').submit(); // Manually submit the form
    });
});
</script>
</body>
</html>
