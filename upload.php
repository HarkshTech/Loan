<?php
// Connect to your database
include 'config.php';

// Check if files were uploaded
if(isset($_FILES['files'])) {
    // $id = $_SESSION['id']; // Assuming you have an ID stored in the session
    
    // Directory where you want to save the uploaded images
    $targetDir = "uploads/";
    
    // Iterate through the uploaded files
    foreach($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        $file_name = $_FILES['files']['name'][$key];
        $file_tmp = $_FILES['files']['tmp_name'][$key];
        
        // Move the uploaded file to the destination directory
        $targetFilePath = $targetDir . $file_name;
        move_uploaded_file($file_tmp, $targetFilePath);
        
        // Insert the file path into the database
        $sql = "INSERT INTO VerificationForms (image_path) VALUES ('$targetFilePath')";
        if ($conn->query($sql) === TRUE) {
            echo "File uploaded successfully.";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Close the database connection
$conn->close();
?>
