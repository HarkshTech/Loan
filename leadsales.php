<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 900px;
            margin: 50px auto;
            margin-top: 80px !important;
        }

        div#sidebar-menu {
            position: fixed;
        }

        .table .thead-dark th {
            color: #fff;
            background-color: #000000;
            border-color: #454d55;
        }

        .lead-status {
            max-width: 150px;
        }

        /* Adjust width of the select element */
        .action-column select {
            width: 130px;
        }

        @media (max-width: 768px) {
            .container {
                margin-top: 20px;
            }

            .container table {
                font-size: 14px;
            }

            .container th,
            .container td {
                padding: 8px;
            }

            .container .action-column {
                width: 150px;
            }

            .container .action-column select {
                width: 110px;
            }
        }
    </style>
</head>

<body>
    <?php include 'leftsidebranch.php'; ?>
    
    <div class="container">
        <h1 class="mb-4">Leads Management</h1>
        
        <div class="mb-4">
            <input type="text" id="search-bar" class="form-control" placeholder="Search Leads">
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Occupation</th>
                        <th>Employer</th>
                        <th>Loan Amount</th>
                        <th>Loan Purpose</th>
                        <th>Lead Status</th>
                        <th>Assigned To</th>
                        <th>Assign To</th>
                    </tr>
                </thead>
                <tbody id="leads-table-body">
                    <?php
include 'config.php';

session_start();
$loggedinuser = $_SESSION['username']; // Assuming the logged-in user's username is stored in the session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leadID = $_POST['leadID'];
    $assignedTo = $_POST['assignedTo']; // Corrected to match the form name attribute

    // Update AssignedTo in the database
    $updateSql = "UPDATE personalinformation SET assignedto='$assignedTo' WHERE ID=$leadID";
    if ($conn->query($updateSql) === TRUE) {
        echo "Assigned To updated successfully";
    } else {
        echo "Error updating Assigned To: " . $conn->error;
    }
}

$sql = "SELECT * FROM personalinformation PI WHERE PI.LeadStatus != 'Hot Lead'";
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
        echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>";
        echo "<input type='hidden' name='leadID' value='" . $row["ID"] . "'>";
        echo "<select class='form-control' name='assignedTo'>"; // Corrected name attribute

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
    echo "<tr><td colspan='10'>No leads found</td></tr>";
}

$conn->close();
?>

                </tbody>
            </table>
        </div>
    </div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function () {
        $("#search-bar").on("keyup", function () {
            var query = $(this).val();
            $.ajax({
                url: "search_bm_leads.php",
                method: "POST",
                data: {query: query},
                success: function (data) {
                    $("#leads-table-body").html(data);
                }
            });
        });
    });
</script>

</body>

</html>
