<?php 
session_start();
if (isset($_GET['username'])) {
    include 'config.php';
        $salesuser = urldecode($_GET['username']);
        
        // Sanitize the input to prevent SQL injection
        $salesuser = mysqli_real_escape_string($conn, $salesuser);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Table</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Custom CSS for responsiveness */
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
     <?php include 'leftsidebranch.php'; ?>
   <div class="container" style="margin-top:8%;">
       <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="branchmanager.php">Dashboard</a></li>
                                            <li class="breadcrumb-item"><a href="salesteambmdashboard.php">Sales Team Dashboard</a></li>
                                            <li class="breadcrumb-item active"><?php echo $salesuser; ?></li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
        </div>
        <h2 class="text-center mb-4">Total Assigned Leads for : <?php echo $salesuser?></h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <?php
                        if (isset($_GET['username'])) {
                            include 'config.php';
                                $salesuser = urldecode($_GET['username']);
                                
                                // Sanitize the input to prevent SQL injection
                                $salesuser = mysqli_real_escape_string($conn, $salesuser);
                            // Your SQL query to fetch data along with column names
                            $query = "SELECT * FROM documentcollection dc
JOIN personalinformation pi ON dc.LeadID = pi.ID
WHERE
    (pi.assignedto = '$salesuser' OR pi.generatedby = 'Self($salesuser)')
    AND (
        dc.Status1 = 'Pending'
        OR dc.Status2 = 'Pending'
        OR dc.Status3 = 'Pending'
        OR dc.Status4 = 'Pending'
        OR dc.Status5 = 'Pending'
        OR dc.Status6 = 'Pending'
        OR dc.Status7 = 'Pending'
        OR dc.Status8 = 'Pending'
        OR dc.Status9 = 'Pending'
        OR dc.Status10 = 'Pending'
        OR dc.Status11 = 'Pending'
        OR dc.Status12 = 'Pending'
        OR dc.Status13 = 'Pending'
    )";
                            // Execute the query
                            $result = $conn->query($query);
                            // Fetch column names
                            $fields = $result->fetch_fields();
                            foreach ($fields as $field) {
                                echo "<th>" . $field->name . "</th>";
                            }
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Loop through the fetched data and populate the table rows
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            foreach ($row as $value) {
                                echo "<td>" . $value . "</td>";
                            }
                            echo "</tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>