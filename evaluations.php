<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'config.php';
date_default_timezone_set('Asia/Kolkata');

session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    // Redirect to the login page or another appropriate page
    header("Location: index.php");
    exit();
}

if($_SESSION['role']==='branchmanager'){
        $redirecturl='digitalverificationsbm.php';
        
    }
    elseif($_SESSION['role']==='admin'){
        $redirecturl='verify.php';
        
    }
    elseif($_SESSION['role']==='verifier'){
        $redirecturl='verify.php';
        
    }

// Function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags($data));
}

// Fetch lead ID from GET parameter
$leadID = $_GET['id'] ?? null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leadID = sanitize($_POST['lead_id']);
    $evaluatorName = sanitize($_POST['evaluator_name']);

    // Upload evaluation report
    $uploadDir = 'Evaluation Reports/';
    $currentTime = date('Y-m-d_H-i-s');
    $fileName = $leadID . '_' . str_replace(' ', '_', $evaluatorName) . '_' . $currentTime . '_evaluation_report.pdf';
    $filePath = $uploadDir . $fileName;

    if (isset($_FILES['evaluation_report']) && $_FILES['evaluation_report']['error'] == UPLOAD_ERR_OK) {
        move_uploaded_file($_FILES['evaluation_report']['tmp_name'], $filePath);

        // Insert record into database
        $stmt = $conn->prepare("INSERT INTO evaluation_reports (lead_id, evaluator_name, report_file) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $leadID, $evaluatorName, $filePath);
        $stmt->execute();
        $stmt->close();

        echo '<script>alert("Evaluation report submitted successfully!"); window.location.replace("'.$redirecturl.'");</script>';
    } else {
        echo '<script>alert("Failed to upload evaluation report.");</script>';
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Reports Submission</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 20px;
        }
        .container {
            margin-top: 100px;
            max-width: 800px;
        }
    </style>
</head>
<body>
    <?php 
    if($_SESSION['role']==='branchmanager'){
        include 'leftsidebranch.php';
        
    }
    elseif($_SESSION['role']==='admin'){
        include 'leftside.php';
        
    }
    elseif($_SESSION['role']==='verifier'){
        include 'leftsideverifier.php';
        
    }
    
    ?>
    <div class="container">
        <h1 class="my-4">Evaluation Reports Submission</h1>
        <form action="evaluations.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars($leadID); ?>" required>

            <div class="form-group">
                <label for="evaluator_name">Evaluator Name</label>
                <input type="text" class="form-control" id="evaluator_name" name="evaluator_name" required>
            </div>

            <div class="form-group">
                <label for="evaluation_report">Evaluation Report</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="evaluation_report" name="evaluation_report" required>
                    <label class="custom-file-label" for="evaluation_report">Choose file</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Submit Report</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Add custom file input label behavior
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    </script>
</body>
</html>
