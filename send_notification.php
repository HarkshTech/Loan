<?php 
    include 'leftside.php';
    include 'config.php';
    date_default_timezone_set('Asia/Kolkata');

    // Set the time zone to Asia/Kolkata in MySQL
    $conn->query("SET time_zone = '+05:30'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Notification</title>
  <!-- Include Bootstrap CSS -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <!-- Include Custom CSS -->
  <style>
    body {
      padding-top: 50px;
      background-color: #f8f9fa;
      font-family: 'Arial', sans-serif;
    }
    .container {
        margin-top:100px;
      max-width: 600px;
      background-color: #ffffff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    h1 {
      font-size: 24px;
      margin-bottom: 20px;
      text-align: center;
      color: #343a40;
    }
    .form-group label {
      font-weight: bold;
      color: #495057;
    }
    .required-label::after {
      content: " *";
      color: red;
    }
    .btn-primary {
      background-color: #007bff;
      border-color: #007bff;
      transition: background-color 0.3s ease;
    }
    .btn-primary:hover {
      background-color: #0056b3;
      border-color: #0056b3;
    }
    .alert {
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Send Instructions</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
      <div class="form-group">
        <label for="title" class="required-label">Title:</label>
        <input type="text" class="form-control" id="title" name="title" required>
      </div>
      <div class="form-group">
        <label for="message" class="required-label">Message:</label>
        <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
      </div>
      <div class="form-group">
        <label for="selectType" class="required-label">Select Type:</label>
        <select class="form-control" id="selectType" name="selectType" onchange="populateDropdown()" required>
          <option value="" disabled selected>Select Type</option>
          <option value="role">Role</option>
          <option value="username">Username</option>
        </select>
      </div>
      <div class="form-group">
        <label for="nfor" class="required-label">For:</label>
        <select class="form-control" id="nfor" name="nfor" required>
          <option value="" disabled selected>Select Role or Username</option>
          <!-- Options populated dynamically using JavaScript -->
        </select>
      </div>
      <input type="hidden" id="nby" name="nby" value="Super Admin">
      <button type="submit" class="btn btn-primary btn-block">Send Instructions</button>
    </form>
  </div>

  <!-- Include Bootstrap JS and jQuery -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    function populateDropdown() {
      var selectType = document.getElementById('selectType').value;
      var nforDropdown = document.getElementById('nfor');
      nforDropdown.innerHTML = '<option value="" disabled selected>Select ' + selectType.charAt(0).toUpperCase() + selectType.slice(1) + '</option>';

      if (selectType === 'role') {
        var roles = {
          'branchmanager': 'Branch Managers',
          'sales': 'Sales Executives',
          'admin': 'Super Admin',
          'accounts': 'Accounts Dept.',
          'recovery': 'Recovery Executives'
        };

        Object.keys(roles).forEach(function(role) {
          nforDropdown.innerHTML += '<option value="' + role + '">' + roles[role] + '</option>';
        });
      } else {
        // Populate options for username type
        $.ajax({
          url: 'get_options.php',
          type: 'GET',
          data: { type: selectType },
          dataType: 'json',
          success: function(data) {
            data.forEach(function(item) {
              nforDropdown.innerHTML += '<option value="' + item + '">' + item + '</option>';
            });
          },
          error: function(error) {
            console.error('Error fetching data', error);
          }
        });
      }
    }
  </script>

  <?php
  // Handle form submission and insert data into database
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $message = $_POST['message'];
    $nfor = $_POST['nfor'];
    $nby = $_POST['nby'];
    $status = 'unread'; // Default status

    // Database connection
    include 'config.php';

    // Insert notification into database
    $sql = "INSERT INTO notifications (title, message, nfor, nby, status, created_at)
            VALUES ('$title', '$message', '$nfor', '$nby', '$status', CURRENT_TIMESTAMP)";

    if ($conn->query($sql) === TRUE) {
        // Success message using JavaScript alert box
        echo '<script>alert("Notification added successfully!");</script>';
    } else {
        // Error message using JavaScript alert box with error details
        echo '<script>alert("Error adding notification: ' . $conn->error . '");</script>';
    }

    // Close database connection
    $conn->close();
  }
  ?>
</body>
</html>
