<?php
include 'config.php';
session_start();

$loggedinuser = $_SESSION['username'];

if (isset($_POST['query'])) {
    $search = $_POST['query'];
    $sql = "SELECT * FROM personalinformation PI 
            WHERE (PI.LeadStatus != 'Hot Lead') 
            AND ( PI.ID LIKE '%$search%'
            OR PI.FullName LIKE '%$search%' 
            OR PI.Email LIKE '%$search%' 
            OR PI.PhoneNumber LIKE '%$search%' 
            OR PI.details1 LIKE '%$search%' 
            OR PI.employer1 LIKE '%$search%' 
            OR PI.LoanAmount LIKE '%$search%' 
            OR PI.LoanPurpose LIKE '%$search%' 
            OR PI.LeadStatus LIKE '%$search%' 
            OR PI.assignedto LIKE '%$search%')";
} else {
    $sql = "SELECT * FROM personalinformation PI WHERE PI.LeadStatus != 'Hot Lead'";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["ID"] . "</td>";
        echo "<td>" . $row["FullName"] . "</td>";
        echo "<td>" . $row["Email"] . "</td>";
        echo "<td>" . $row["PhoneNumber"] . "</td>";
        echo "<td>" . $row["details1"] . "</td>";
        echo "<td>" . $row["employer1"] . "</td>";
        echo "<td>" . $row["LoanAmount"] . "</td>";
        echo "<td>" . $row["LoanPurpose"] . "</td>";
        echo "<td>" . $row["LeadStatus"] . "</td>";
        echo "<td>" . $row["assignedto"] . "</td>";
        echo "<td>";
        echo "<form action='update_lead.php' method='POST'>";
        echo "<input type='hidden' name='leadID' value='" . $row["ID"] . "'>";
        echo "<select class='form-control' name='assignedTo'>";
        
        // Fetch available sales users for the branch manager
        $usersSql = "SELECT username FROM users WHERE branchmanager='$loggedinuser' AND role='sales'";
        $usersResult = $conn->query($usersSql);
        if ($usersResult->num_rows > 0) {
            while ($userRow = $usersResult->fetch_assoc()) {
                echo "<option value='" . $userRow['username'] . "'>" . $userRow['username'] . "</option>";
            }
        }

        echo "</select>";
        echo "<button type='submit' class='btn btn-primary'>Update</button>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='11'>No leads found</td></tr>";
}

$conn->close();
?>
