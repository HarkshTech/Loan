<?php
include 'config.php';

$query = $_GET['query'];
$sql = "SELECT * FROM personalinformation WHERE FullName LIKE '%$query%' OR ID LIKE '%$query%' OR PhoneNumber LIKE '%$query%' OR LoanPurpose LIKE '%$query%'";
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
        echo "<td>" . $row["generatedby"] . "</td>";
        echo "<td>" . $row["assignedto"] . "</td>";
        echo "<td>" . $row["LeadStatus"] . "</td>";
        echo '<td><form action="update_lead.php" method="POST" class="lead-status">
                <input type="hidden" name="leadID" value="' . $row["ID"] . '">
                <div class="form-group">
                    <select class="form-control" name="leadStatus">
                        <option value="Hot Lead">Hot Lead</option>
                        <option value="Cold Lead">Cold Lead</option>
                        <option value="Rejection">Rejection</option>
                        <option value="New Lead">New Lead</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Update</button>
            </form></td>';
        echo '<td><form action="assign_lead.php" method="POST" class="assign-lead">
                <input type="hidden" name="leadID" value="' . $row["ID"] . '">
                <div class="form-group">
                    <select class="form-control" name="assignedTo">';

        // Fetch users with role 'branchmanager' or 'sales'
        $usersSql = "SELECT username FROM users WHERE role IN ('branchmanager', 'sales')";
        $usersResult = $conn->query($usersSql);
        if ($usersResult->num_rows > 0) {
            while ($userRow = $usersResult->fetch_assoc()) {
                echo "<option value='" . $userRow['username'] . "'>" . $userRow['username'] . "</option>";
            }
        }

        echo '        </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Assign</button>
            </form></td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='13'>No leads found</td></tr>";
}

$conn->close();
?>
