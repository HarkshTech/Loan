<?php 
    session_start(); // Start the session
    $loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
    if($role=='admin'){
        include 'leftside.php';
        $redirect='dashboard.php';
    }
    elseif($role=='branchmanager'){
        include 'leftsidebranch.php';
        $redirect='branchmanager.php';
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hot Leads and Document Collection</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 900px;
            margin-top: 8%;
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

        .action-column {
            width: 180px;
        }

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
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo $redirect;?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Welcome !</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="mb-4" style="font-size:24px !important;">Hot Leads and Document Collection</h1>
        <!-- Search bar -->
        <div class="row mb-3">
            <div class="col-12">
                <input type="text" id="search-input" class="form-control" placeholder="Search by Name, Lead ID, or Phone Number">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Lead Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="hot-leads-table-body">
                    <?php
                    include 'config.php';
                    
                    if ($role=='admin') {
                        $sql = "SELECT ID, FullName, Email, PhoneNumber FROM personalinformation WHERE LeadStatus='Hot Lead' AND StepReached<>'Disbursed' AND LoanStatus<>'Rejected'";
                    } elseif($role=='branchmanager') {
                        // $sql = "SELECT ID, FullName, Email, PhoneNumber FROM personalinformation WHERE LeadStatus='Hot Lead' AND (assignedto = '$loggedInUser' OR generatedby = 'Self($loggedInUser)')";
                        $sql = "SELECT ID, FullName, Email, PhoneNumber FROM personalinformation WHERE LeadStatus='Hot Lead' AND StepReached<>'Disbursed' AND LoanStatus<>'Rejected'";
                    }

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["ID"] . "</td>";
                            echo "<td>" . $row["FullName"] . "</td>";
                            echo "<td>" . $row["Email"] . "</td>";
                            echo "<td>" . $row["PhoneNumber"] . "</td>";
                            echo "<td>Hot Lead</td>";
                            echo '<td><a href="document_collection.php?id=' . $row["ID"] . '" class="btn btn-primary btn-sm">Document Collection</a></td>';
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No hot leads found</td></tr>";
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
        $(document).ready(function(){
            $('#search-input').on('input', function() {
                let query = $(this).val();
                $.ajax({
                    url: 'search_hot_leads.php',
                    method: 'GET',
                    data: { query: query },
                    success: function(data) {
                        $('#hot-leads-table-body').html(data);
                    }
                });
            });
        });
    </script>
</body>

</html>
