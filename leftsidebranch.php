<?php 
    if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to get logged-in user's name from the session
function getLoggedInUserName() {
    if (isset($_SESSION['username'])) {
        return $_SESSION['username'];
        echo $_SESSION['username'];
    } else {
        return ""; // Return an empty string if session variable is not set
    }
}

// Get the username
$username = getLoggedInUserName();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Loan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Loan" name="description" />
    <meta content="Loan" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/LOGO.png">

    <!-- plugin css -->
    <link href="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet"
        type="text/css" />

    <!-- preloader css -->
    <link rel="stylesheet" href="assets/css/preloader.min.css" type="text/css" />

    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
    <style>
        #navbar-username {
            display: none;
        }
        #profileBadge {
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            font-weight: bold;
            margin: 10px;
        }
        #profileDisplay {
            display: none;
            position: absolute;
            top: 60px;
            right: 10px;
            width: 250px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }
        #profileDisplay p {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            text-align: center;
        }
        #profileDisplay button {
            margin-top: 15px;
            width: 100%;
            padding: 10px;
            background-color: #dc3545;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #profileDisplay button:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body data-topbar="dark">

    <!-- <body data-layout="horizontal"> -->

    <!-- Begin page -->
    <div id="layout-wrapper">


        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="#" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="assets/images/finocal.png" alt="" height="72">
                            </span>
                            <span class="logo-lg">
                                <img src="assets/images/finocal.png" alt="" height="72">
                            </span>
                        </a>

                        <a href="branchmanager.php" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="assets/images/finocal.png" alt="" height="72">
                            </span>
                            <span class="logo-lg">
                                <img src="assets/images/finocal.png" alt="" height="72">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars" style="margin-left:25px;margin-top:13px;"></i>
                    </button>

                    <!-- App Search-->


                </div>

                <div class="d-flex">

                    <div class="dropdown d-inline-block d-lg-none ms-2">
                        <button type="button" class="btn header-item" id="page-header-search-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i data-feather="search" class="icon-lg"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                            aria-labelledby="page-header-search-dropdown">

                            <form class="p-3">
                                <div class="form-group m-0">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Search ..."
                                            aria-label="Search Result">

                                        <button class="btn btn-primary" type="submit"><i
                                                class="mdi mdi-magnify"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>


                    <div class="dropdown d-none d-sm-inline-block">
                        <!--<button type="button" class="btn header-item" id="mode-setting-btn">-->
                        <!--    <i data-feather="moon" class="icon-lg layout-mode-dark"></i>-->
                        <!--    <i data-feather="sun" class="icon-lg layout-mode-light"></i>-->
                        <!--</button>-->
                        <div id="navbar-username" style="display: none;"><?php echo htmlspecialchars($username); ?></div>
                        <div class="profile-badge" id="profileBadge"></div>
                        <div id="profileDisplay">
                            <p id="fullUsername"></p>
                            <form method="post" action="logout.php">
                                <button type="submit">Logout</button>
                            </form>
                        </div>

                    </div>




                </div>
            </div>
        </header>

        <!-- ========== Left Sidebar Start ========== -->
        <div class="vertical-menu">

            <div data-simplebar class="h-100">

                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    <!-- Left Menu Start -->
                    <ul class="metismenu list-unstyled" id="side-menu">
                        <li class="menu-title" data-key="t-menu">Menu</li>
                        
                        <li>
                            <a href="branchmanager.php">
                                <i data-feather="home"></i>
                                <span data-key="t-dashboard">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="salesteambmdashboard.php">
                                <i data-feather="home"></i>
                                <span data-key="t-dashboard">Sales Dashboard</span>
                            </a>
                        </li>
                    
                        <li>
                            <a href="borrowerbranchmanager.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Borrowers</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="leadsales.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Leads</span>
                            </a>
                        </li>
                        <li>
                            <a href="hot_leads.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Hot Leads</span>
                            </a>
                        </li>
                        
                        
                        <li>
                            <!-- <a href="viewapplicantdetailsbm.php"> -->
                            <a href="leadstatus.php"> 
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Lead Statuses</span>
                            </a>
                        </li>
                        
                        
                        <li>
                            <a href="#" class="has-arrow">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Verifications</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <!--<li><a href="digitalverificationsbm.php" key="t-contacts">Document Verification</a></li>-->
                                <li><a href="verify.php" key="t-contacts">Document Verification</a></li>
                                <li><a href="approve_fields3.php" data-key="t-contacts">Field Verification(APPROVAL)</a></li>
                                <li><a href="approve_legal.php" key="t-contacts">Legal Verifications(APPROVAL)</a></li>
                                <li><a href="evaluations_approval.php" key="t-contacts">Evaluation Reports(APPROVAL)</a></li>
                            </ul>
                        </li>
                        
                        
                        <li>
                            <a href="salesstatus.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Sales Status</span>
                            </a>
                        </li>
                        <li>
                            <a href="approved_loans.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Disbursals</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="paymentschedule.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">View Payment Schedules</span>
                            </a>
                        </li>
                        
                        
                        <li>
                            <a href="collect_payments2.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Collect EMIs</span>
                            </a>
                        </li>
                        <li>
                            <a href="approve_payments_bm.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Approve Collected EMIs</span>
                            </a>
                        </li>
                            <li>
                            <a href="display_loan_data.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Admin Sanctions</span>
                            </a>
                        </li>
                        <li>
                            <a href="payments.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">View Payments</span>
                            </a>
                        </li>
                        <li>
                            <a href="failed_emi.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Failed EMI's</span>
                            </a>
                        </li>
                        <li>
                            <a href="recovery_assigning.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Recovery Assigning</span>
                            </a>
                        </li>
                        <li>
                            <a href="recoveryanalysis.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Assigned Recoveries</span>
                            </a>
                        </li>
                        <li>
                            <a href="view-notifications.php">
                                <i data-feather="bell"></i>
                                <span data-key="t-contacts">Notifications</span>
                            </a>
                        </li>
                        
                    
                    
                         <li>
                            <a href="logout.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Logout</span>
                            </a>
                        </li>
                    







</ul>


</div>
<!-- Sidebar -->
</div>
</div>
<!-- Left Sidebar End -->


<!-- JAVASCRIPT -->
<script src="assets/libs/jquery/jquery.min.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/metismenu/metisMenu.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/libs/node-waves/waves.min.js"></script>
<script src="assets/libs/feather-icons/feather.min.js"></script>
<!-- pace js -->
<script src="assets/libs/pace-js/pace.min.js"></script>


<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>

<!-- Plugins js-->
<script src="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>

<script src="assets/js/pages/allchart.js"></script>
<!-- dashboard init -->
<script src="assets/js/pages/dashboard.init.js"></script>

<script src="assets/js/app.js"></script>
<script>
        document.addEventListener('DOMContentLoaded', function () {
            var fullName = document.getElementById('navbar-username').textContent;

            var initials = fullName ? fullName.split(' ').map(name => name.charAt(0)).join('').toUpperCase() : '';
            var badgeElement = document.getElementById('profileBadge');
            badgeElement.textContent = initials;

            // Toggle the profile display on badge click
            badgeElement.addEventListener('click', function() {
                var profileDisplay = document.getElementById('profileDisplay');
                var fullUsernameElement = document.getElementById('fullUsername');
                fullUsernameElement.textContent = fullName;
                
                if (profileDisplay.style.display === 'none' || profileDisplay.style.display === '') {
                    profileDisplay.style.display = 'block';
                } else {
                    profileDisplay.style.display = 'none';
                }
            });
        });
    </script>

</body>

</html>