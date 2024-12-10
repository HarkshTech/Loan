<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records with overdue_days >= 10</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        include 'config.php';

        // SQL query to fetch data
        $sql = "SELECT rd.*, pi.FullName 
                FROM recovery_data rd
                LEFT JOIN personalinformation pi ON rd.LeadID = pi.ID;";

        // Execute query
        $result = $conn->query($sql);

        // Check if there are any results
        if ($result->num_rows > 0) {
            // Output the results in a table
            echo "<h2 class='my-4'>All Recoveries Status</h2>";
            echo "<div class='table-responsive'>";
            echo "<table class='table table-bordered table-striped'>";
            echo "<thead class='thead-dark'>
                    <tr>
                        <th>ID</th>
                        <th>Lead ID</th>
                        <th>Name</th>
                        <th>Overdue Days</th>
                        <th>Assigned To</th>
                        <th>Assigning Date</th>
                        <th>Visit Needed</th>
                        <th>Visit Scheduled</th>
                        <th>Visit Status</th>
                        <th>Case Status</th>
                        <th>Visit Again</th>
                        <th>Visit Again Date</th>
                        <th>Geolocation</th>
                        <th>Next Steps</th>
                        <th>Remarks</th>
                        <th>Followups Count</th>
                    </tr>
                </thead>
                <tbody>";
            
            // Fetch each row and display
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['ID']}</td>
                        <td>{$row['LeadID']}</td>
                        <td>{$row['FullName']}</td>
                        <td>{$row['Overdue_days']}</td>
                        <td>{$row['AssignedTo']}</td>
                        <td>{$row['AssignedDate']}</td>
                        <td>{$row['VisitNeeded']}</td>
                        <td>{$row['visitscheduled']}</td>
                        <td>{$row['visitstatus']}</td>
                        <td>{$row['CaseStatus']}</td>
                        <td>{$row['visitagain']}</td>
                        <td>{$row['visitagaindate']}</td>
                        <td><a href='https://www.google.com/maps/search/?api=1&query={$row['visitgeolocation']}' target='_blank'>View on Google Maps</a></td>
                        <td>{$row['NextSteps']}</td>
                        <td>{$row['Remarks']}</td>
                        <td>{$row['followupcount']}</td>
                    </tr>";
            }
            echo "</tbody></table></div>";
        } else {
            echo "<p class='mt-4'>No records found</p>";
        }

        // Close connection
        $conn->close();
        ?>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
