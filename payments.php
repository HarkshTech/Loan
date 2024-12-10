<?php
session_start();
$username = $_SESSION['username'];
$role = $_SESSION['role'];
if($role==='admin'){
    include 'leftside.php';
}
elseif($role==='accounts'){
    include 'leftbaraccounts.php';
}
elseif($role==='branchmanager'){
    include 'leftsidebranch.php';
}

switch($role){
    case 'admin':
        $redirecturl='dashboard.php';
        break;
    case 'branchmanager':
        $redirecturl='branchmanager.php';
        break;
    case 'accounts':
        $redirecturl='dashboardapproved_loans.php';
        break;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMI Schedule Payments</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /*.container{*/
        /*    margin-top:11%;*/
        /*    margin-left:7% !important;*/
        /*}*/
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        header {
            background: #50b3a2;
            color: #fff;
            padding: 20px 0;
            border-bottom: #e8491d 3px solid;
        }
        header h1 {
            margin: 0;
            font-size: 2rem;
        }
        #search-bar {
            margin: 20px 0;
        }
        .form-inline .form-group {
            margin-right: 10px;
        }
        table th {
            background: #50b3a2;
            color: white;
            position: sticky;
            top: 0;
            z-index: 1; /* Ensure table headers stay on top */
        }
        .navbar-header{
            height: 30px;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            function fetchPayments() {
                const search = $('#search').val();
                const start_date = $('#start_date').val();
                const end_date = $('#end_date').val();

                $.ajax({
                    url: 'fetch_payments.php',
                    type: 'GET',
                    data: { search: search, start_date: start_date, end_date: end_date },
                    success: function(data) {
                        let tableBody = '';
                        data.forEach(payment => {
                            tableBody += `
                                <tr>
                                    <td>${payment.PaymentID}</td>
                                    <td>${payment.LeadID}</td>
                                    <td>${payment.FullName}</td>
                                    <td>${payment.PaymentDate}</td>
                                    <td>${payment.EMIAmount}</td>
                                    <td>${payment.PaidEMIs}</td>
                                    <td>${payment.PendingEMIs}</td>
                                </tr>
                            `;
                        });
                        $('table tbody').html(tableBody);
                    }
                });
            }

            fetchPayments();

            $('#search, #start_date, #end_date').on('input', function() {
                fetchPayments();
            });
        });
    </script>
</head>
<body>
    <div class="container" style="margin-top:100px;">
        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="<?php echo $redirecturl?>">Dashboard</a></li>
                                            <li class="breadcrumb-item">Welcome !</li>
                                            <li class="breadcrumb-item active">View Collected Payments</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
        <h3 class="mt-4">Search Payments</h3>
        <div id="search-bar" class="form-inline justify-content-between mb-3">
            <div class="form-group">
                <input type="text" id="search" class="form-control" placeholder="Search by LeadID or Name" style="width: 250px;">
            </div>
            <div class="form-group">
                <label for="start_date" class="mr-2">From:</label>
                <input type="date" id="start_date" class="form-control" style="width: 200px;">
            </div>
            <div class="form-group">
                <label for="end_date" class="mr-2">To:</label>
                <input type="date" id="end_date" class="form-control" style="width: 200px;">
            </div>
        </div>
        <h3>Payments Till Date</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Lead ID</th>
                        <th>Name</th>
                        <th>Payment Date</th>
                        <th>Amount</th>
                        <th>Paid EMIs</th>
                        <th>Pending EMIs</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be inserted here by AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
