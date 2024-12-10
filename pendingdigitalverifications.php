<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Collection Status</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
        .table-container {
            margin-top: 20px;
        }
        .table thead th {
            background-color: #007bff;
            color: white;
        }
        /*mandeep css*/
        body[data-sidebar-size=sm] .vertical-menu {
            left:0px !important;
        }
        .vertical-menu{
            left:0px !important;
        }
    </style>
</head>
<body>

<div class="container">
    <?php 
        include 'leftside.php';
    ?>
    <h1 class="text-center">Document Collection Status</h1>

    <?php
    // Database connection
    include 'config.php';

    // Fetch all Hot Leads
    $hotLeadsSql = "SELECT ID, FullName 
FROM personalinformation 
WHERE LeadStatus = 'Hot Lead' 
  AND LoanStatus NOT IN ('Disbursed', 'Rejected');
";
    $hotLeadsResult = $conn->query($hotLeadsSql);

    if ($hotLeadsResult->num_rows > 0) {
        echo '<div class="table-container">';
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>Lead ID</th><th>Full Name</th><th>Status</th></tr></thead>';
        echo '<tbody>';

        // Process each hot lead
        while ($lead = $hotLeadsResult->fetch_assoc()) {
            $leadID = $lead["ID"];
            $fullName = $lead["FullName"];

            // Check if there's a row for this LeadID in documentcollection table
            $docCollectionSql = "SELECT 
                                    Status1, Status2, Status3, Status4, Status5, 
                                    Status6, Status7, Status8, Status9, Status10, 
                                    Status11, Status12, Status13 
                                FROM documentcollection 
                                WHERE LeadID = $leadID";
            $docCollectionResult = $conn->query($docCollectionSql);

            if ($docCollectionResult->num_rows > 0) {
                $row = $docCollectionResult->fetch_assoc();
                // Check if any document has status 'Rejected' or 'Pending'
                $pendingOrRejected = false;
                for ($i = 1; $i <= 13; $i++) {
                    if ($row["Status$i"] === 'Pending' || $row["Status$i"] === 'Rejected') {
                        $pendingOrRejected = true;
                        break;
                    }
                }

                if ($pendingOrRejected) {
                    echo "<tr><td>$leadID</td><td>$fullName</td><td>Pending Digital Verification</td></tr>";
                }
            } else {
                // No row for this LeadID in documentcollection table
                echo "<tr><td>$leadID</td><td>$fullName</td><td>Document Uploads Pending</td></tr>";
            }
        }

        echo '</tbody></table>';
        echo '</div>';
    } else {
        echo "<p class='text-center'>No hot leads found</p>";
    }

    $conn->close();
    ?>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
