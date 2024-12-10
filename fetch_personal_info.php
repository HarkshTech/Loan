<?php
include 'config.php';


$leadID = $_GET['leadID'];
$sql = "SELECT * FROM personalinformation WHERE ID = $leadID";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "Full Name: " . $row['FullName'] . "<br>";
    echo "Email: " . $row['Email'] . "<br>";
    echo "Phone Number: " . $row['PhoneNumber'] . "<br>";
    echo "Date of Birth: " . $row['DateOfBirth'] . "<br>";
    echo "Address: " . $row['Address'] . "<br>"; 
} else {
    echo "No personal information found for this lead.";
}

$conn->close();
?>
