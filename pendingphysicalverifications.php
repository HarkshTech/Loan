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

    // Fetch LeadIDs from personalinformation table not present in VerificationForms table
    $leadIDsQuery = "SELECT pi.ID as LeadID, pi.FullName, pi.LeadStatus
FROM personalinformation pi
LEFT JOIN VerificationForms vf ON pi.ID = vf.leadID
WHERE (vf.leadID IS NULL 
       OR vf.verificationStatus_Home IN ('Pending', 'Rejected') 
       OR vf.verificationStatus_Business IN ('Pending', 'Rejected'))
  AND pi.LeadStatus = 'Hot Lead' 
  AND pi.LoanStatus NOT IN ('Rejected', 'Disbursed');
";
    $leadIDsResult = $conn->query($leadIDsQuery);
    $leadIDs = [];
    while ($row = $leadIDsResult->fetch_assoc()) {
        $leadIDs[] = $row;
    }

    // Fetch records from VerificationForms where Home or Business Verification is Pending or Rejected
    $verificationQuery = "SELECT vf.ID, vf.leadID, pi.FullName, vf.verifierName_Home, vf.verificationStatus_Home, vf.electricity_bill_home, vf.electricity_meter_home, vf.verificationNotes_Home, vf.verifierName_Business, vf.verificationStatus_Business, vf.businessVerificationNotes, vf.created_at, vf.image_path_home, vf.electricity_bill_business, vf.electricity_meter_business, vf.verification_geolocation_home, vf.verification_geolocation_business, vf.business_images
                          FROM VerificationForms vf
                          JOIN personalinformation pi ON vf.leadID = pi.ID
                          WHERE vf.verificationStatus_Home IN ('Pending', 'Rejected')
                             OR vf.verificationStatus_Business IN ('Pending', 'Rejected')
                             AND pi.LoanStatus NOT IN ('Rejected','Disbursed')";
    $verificationResult = $conn->query($verificationQuery);

    if ($leadIDsResult->num_rows > 0 || $verificationResult->num_rows > 0) {
        echo '<div class="table-container">';
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>LeadID</th><th>FullName</th><th>Verification Status</th></tr></thead>';
        echo '<tbody>';

        // Output leadIDs not present in VerificationForms
        foreach ($leadIDs as $lead) {
            echo '<tr>';
            echo '<td>'.$lead["LeadID"].'</td>';
            echo '<td>'.$lead["FullName"].'</td>';
            echo '<td>Pending</td>';
            echo '</tr>';
        }

        // Output data of each row from VerificationForms
        while($row = $verificationResult->fetch_assoc()) {
            echo '<tr>';
            echo '<td>'.$row["leadID"].'</td>';
            echo '<td>'.$row["FullName"].'</td>';
            echo '<td>';
            if ($row["verificationStatus_Home"] == 'Pending' || $row["verificationStatus_Home"] == 'Rejected') {
                echo 'Home Verification: '.$row["verificationStatus_Home"];
            }
            elseif($row["verificationStatus_Home"]==='Completed'){
                echo 'Home Verification: Completed, Admin approval pending';
            }
            if ($row["verificationStatus_Business"] === 'Pending' || $row["verificationStatus_Business"] == 'Rejected') {
                echo '<br>Business Verification: '.$row["verificationStatus_Business"];
            }
            elseif($row["verificationStatus_Business"]==='Completed'){
                echo '<br>Business Verification: Completed, Admin approval pending';
            }
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
    } else {
        echo "<p class='text-center'>No records found</p>";
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
