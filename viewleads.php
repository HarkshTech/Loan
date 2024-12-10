<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Personal Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        .action-buttons a {
            text-decoration: none;
            padding: 5px 10px;
            margin-right: 5px;
            border-radius: 3px;
        }

        .view-button {
            background-color: #4CAF50;
            color: white;
        }

        .edit-button {
            background-color: #008CBA;
            color: white;
        }

        .action-buttons a:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>

<?php
include 'leftsidebranch.php';
include 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);


$sql = "SELECT ID,sno,FullName  FROM personalinformation";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Sno</th><th>FullName</th><th>Action</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["ID"] . "</td>";
        echo "<td>" . $row["sno"] . "</td>";
        echo "<td>" . $row["FullName"] . "</td>";
   echo "<td class='action-buttons'><a class='view-button' href='view.php?id=" . $row["ID"] . "'>View</a><a class='edit-button' href='edit.php?id=" . $row["ID"] . "'>Edit</a></td>";

        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}

$conn->close();
?>

</body>
</html>
