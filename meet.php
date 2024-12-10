<?php

include 'config.php';

// Process form data and insert into database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape user inputs for security
    $srno = $conn->real_escape_string($_POST['srno']);
    $datepicker = $conn->real_escape_string($_POST['datepicker']);
     $datepicker = $conn->real_escape_string($_POST['case_of']);
    // Add the rest of the fields similarly

    // Insert data into database
    $sql = "INSERT INTO form (srno, date) VALUES ('$srno', '$datepicker')";
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
