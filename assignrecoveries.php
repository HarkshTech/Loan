<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Recoveries</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Assign Recoveries</h2>
        
        <?php
        // Database connection
        include 'config.php';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Handle form submission
            $lead_ids = $_POST['lead_id'];
            $assigned_tos = $_POST['assigned_to'];
            
            for ($i = 0; $i < count($lead_ids); $i++) {
                $lead_id = $lead_ids[$i];
                $assigned_to = $assigned_tos[$i];
                $assigned_date = date('Y-m-d');

                // Update recovery_data with assigned person and date
                $sql = "UPDATE recovery_data 
                        SET AssignedTo = ?, AssignedDate = ? 
                        WHERE LeadID = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $assigned_to, $assigned_date, $lead_id);
                $stmt->execute();
            }

            echo "<div class='alert alert-success'>Recoveries assigned successfully.</div>";
        }

        // Fetch overdue loans from personalinformation and recovery_data
        $sql = "SELECT p.ID, p.FullName, pi.Overdue_days, r.No_of_EMIs_bounced 
                FROM personalinformation p 
                JOIN recovery_data r ON p.ID = r.LeadID 
                WHERE r.Overdue_days > 0";
        $result = $conn->query($sql);
        ?>

        <form action="" method="post">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Lead ID</th>
                        <th>Name</th>
                        <th>Overdue Days</th>
                        <th>No of EMIs Bounced</th>
                        <th>Assign To</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["ID"] . "</td>";
                            echo "<td>" . $row["Name"] . "</td>";
                            echo "<td>" . $row["Overdue_days"] . "</td>";
                            echo "<td>" . $row["No_of_EMIs_bounced"] . "</td>";
                            echo '<td>
                                    <input type="hidden" name="lead_id[]" value="' . $row["ID"] . '">
                                    <input type="text" name="assigned_to[]" class="form-control" required>
                                  </td>';
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No overdue loans found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Assign Recoveries</button>
        </form>

        <?php
        $conn->close();
        ?>
    </div>
</body>
</html>
