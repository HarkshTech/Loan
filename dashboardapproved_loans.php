<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); // Start the session

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'accounts' && $_SESSION['role'] !== 'admin')) {
    // Redirect to the login page or another appropriate page
    header("Location: index.php");
    exit();
}

else{
    $role=$_SESSION['role'];
    if($role=='accounts'){
        include 'leftbaraccounts.php';
    }
    elseif($role=='admin'){
        include 'leftside.php';
    }
    include 'config.php';

    // Fetch data for approved loans
    $query_totalloanamount = "
SELECT 
    SUM(sanctionedAmount - ((sanctionedAmount / TotalEMIs) * COALESCE(ep.QualifiedPaidEMIs, 0))) AS totalloanamount 
FROM 
    emi_schedule es
LEFT JOIN (
    SELECT 
        LeadID,
        COUNT(*) AS QualifiedPaidEMIs
    FROM 
        emi_payments
    WHERE 
        bmapproval = '1' 
        AND (PaymentType = 'collect_payment' 
             OR PaymentType = 'Partial Payment Adjusted in EMI' 
             OR PaymentType = 'advance_emi')
    GROUP BY 
        LeadID
) ep ON es.LeadID = ep.LeadID;
";

$result_totalloanamount = mysqli_query($conn, $query_totalloanamount);
$row_totalloanamount = mysqli_fetch_assoc($result_totalloanamount);
$totalloanamount = $row_totalloanamount['totalloanamount'];


    
    $query_totalemistoday = "SELECT COUNT(LeadID) AS totalemistoday
FROM emi_payments
WHERE PaymentDate = CURDATE();";
    $result_totalemistoday = mysqli_query($conn, $query_totalemistoday);
    $row_totalemistoday = mysqli_fetch_assoc($result_totalemistoday);
    $totalemistoday = $row_totalemistoday['totalemistoday'];
    
    $query_totalrecoveries = "SELECT COUNT(ID) AS totalrecoveries FROM emi_schedule where overdue_days>='10'";
    $result_totalrecoveries = mysqli_query($conn, $query_totalrecoveries);
    $row_totalrecoveries = mysqli_fetch_assoc($result_totalrecoveries);
    $totalrecoveries = $row_totalrecoveries['totalrecoveries'];
    
    $query_totaldisbursals = "SELECT COUNT(ID) AS totaldisbursals FROM emi_schedule where DATE(datestarted) = CURDATE();";
    $result_totaldisbursals = mysqli_query($conn, $query_totaldisbursals);
    $row_totaldisbursals = mysqli_fetch_assoc($result_totaldisbursals);
    $totaldisbursals = $row_totaldisbursals['totaldisbursals'];
    
    $query_Cold = "SELECT COUNT(DISTINCT ID) AS Cold FROM personalinformation where LeadStatus='Cold Lead'";
    $result_Cold = mysqli_query($conn, $query_Cold);
    $row_Cold = mysqli_fetch_assoc($result_Cold);
    $Cold = $row_Cold['Cold'];
    
    $query_Rejected = "SELECT COUNT(DISTINCT ID) AS Rejected FROM personalinformation where LeadStatus='Rejected'";
    $result_Rejected = mysqli_query($conn, $query_Rejected);
    $row_Rejected = mysqli_fetch_assoc($result_Rejected);
    $Rejected = $row_Rejected['Rejected'];
    
    $query_pendingdigitalverifications = "SELECT COUNT(DISTINCT ID)
    AS pendingdigitalverifications
FROM documentcollection
WHERE 
    Status1    IN ('Rejected', 'Pending')
    OR Status2 IN ('Rejected', 'Pending')
    OR Status3 IN ('Rejected', 'Pending')
    OR Status4 IN ('Rejected', 'Pending')
    OR Status5 IN ('Rejected', 'Pending')
    OR Status6 IN ('Rejected', 'Pending')
    OR Status7 IN ('Rejected', 'Pending')
    OR Status8 IN ('Rejected', 'Pending')
    OR Status9 IN ('Rejected', 'Pending')
    OR Status10 IN ('Rejected', 'Pending')
";
    $result_pendingdigitalverifications = mysqli_query($conn, $query_pendingdigitalverifications);
    $row_pendingdigitalverifications = mysqli_fetch_assoc($result_pendingdigitalverifications);
    $pendingdigitalverifications = $row_pendingdigitalverifications['pendingdigitalverifications'];
    
    $query_pendingphyiscalverifications = "SELECT COUNT(DISTINCT leadID)
    AS pendingphyiscalverifications
FROM VerificationForms
WHERE 
    verificationStatus_Home IN ('Rejected', 'Pending')
    OR verificationStatus_Business IN ('Rejected', 'Pending')";
    $result_pendingphyiscalverifications = mysqli_query($conn, $query_pendingphyiscalverifications);
    $row_pendingphyiscalverifications = mysqli_fetch_assoc($result_pendingphyiscalverifications);
    $pendingphyiscalverifications = $row_pendingphyiscalverifications['pendingphyiscalverifications'];
    
    $query_approved_loans = "SELECT COUNT(DISTINCT LeadID) AS approved_loans FROM approval_information";
    $result_approved_loans = mysqli_query($conn, $query_approved_loans);
    $row_approved_loans = mysqli_fetch_assoc($result_approved_loans);
    $approved_loans = $row_approved_loans['approved_loans'];
    
    
    
    $query_pendingrecovery = "SELECT COUNT(DISTINCT LeadID) AS pendingrecovery FROM emi_schedule WHERE overdue_days >= 10";
    $result_pendingrecovery = mysqli_query($conn, $query_pendingrecovery);
    $row_pendingrecovery = mysqli_fetch_assoc($result_pendingrecovery);
    $pendingrecovery = $row_pendingrecovery['pendingrecovery'];
    
    $query_failedemi = "SELECT COUNT(DISTINCT LeadID) AS failedemi FROM emi_schedule WHERE overdue_days > 5";
    $result_failedemi = mysqli_query($conn, $query_failedemi);
    $row_failedemi = mysqli_fetch_assoc($result_failedemi);
    $failedemi = $row_failedemi['failedemi'];
    
    $query_totalemisdue = "SELECT COUNT(DISTINCT LeadID) AS totalemisdue FROM emi_schedule WHERE TotalEMis>PaidEMIs";
    $result_totalemisdue = mysqli_query($conn, $query_totalemisdue);
    $row_totalemisdue = mysqli_fetch_assoc($result_totalemisdue);
    $totalemisdue = $row_totalemisdue['totalemisdue'];
    
    $query_closedloans = "SELECT COUNT(DISTINCT ID) AS closedloans FROM personalinformation WHERE LoanStatus=''";
    $result_closedloans = mysqli_query($conn, $query_closedloans);
    $row_closedloans = mysqli_fetch_assoc($result_closedloans);
    $closedloans = $row_closedloans['closedloans'];
    
    
}
?>

            

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                                            <li class="breadcrumb-item active">Welcome !</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row" >
                           
                           
                            <!--Total loan amount-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #f3dcc5;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Loan Amount(Total Outstanding)</span>
                                                   <a href="totalloanamount.php" style="text-decoration: none; color: inherit;">
                                                <h4 class="mb-3">
                                                    <span class="counter-value" data-target="7.54"><?php echo !empty($totalloanamount) ? $totalloanamount : 0; ?>
</span>
                                                </h4>
                                                </a>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart3" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body 3-->
                                </div><!-- end card -->
                            </div><!-- end col -->
                          
                           

                            <!--Total EMI's Today-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #c9e7db;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Total EMI's Today(Recieved)</span>
                                                <a href="totalemistoday.php" style="text-decoration: none; color: inherit;">
                                                <h4 class="mb-3">
                                                    <span class="counter-value" data-target="7.54"><?php echo $totalemistoday; ?></span>
                                                </h4>
                                                </a>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart3" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body 3-->
                                </div><!-- end card -->
                            </div><!-- end col -->
                            
                         
                              <!--Total Recoveries-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #bee6ef;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Recoveries</span>
                                                <a href="totalrecoveries.php" style="text-decoration: none; color: inherit;">
                                                <h4 class="mb-3">
                                                    <span class="counter-value" data-target="7.54"><?php echo  $pendingrecovery; ?></span>
                                                </h4>
                                                </a>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart3" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body 3-->
                                </div><!-- end card -->
                            </div><!-- end col -->
                            
                            
                       
                            
                            
                            
                              <!-- Disbusal's  Today -->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #f3dcc5;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Disbusal's Today</span>
                                                <a href="disbursalstoday.php" style="text-decoration: none; color: inherit;">
                                                <h4 class="mb-3">
                                                    <span class="counter-value" data-target="7.54"><?php echo $totaldisbursals; ?></span>
                                                </h4>
                                                </a>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart3" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body 3-->
                                </div><!-- end card -->
                            </div><!-- end col -->
                            
                            
                            
                            <!--failed emi-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #d37b7b;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Failed EMI's</span>
                                                <a href="failed_emi.php" style="text-decoration: none; color: inherit;">
                                                <h4 class="mb-3">
                                                    <span class="counter-value" data-target="7.54"><?php echo  $failedemi; ?></span>
                                                </h4>
                                                </a>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart3" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body 3-->
                                </div><!-- end card -->
                            </div><!-- end col -->
                            <!--Closed Loans-->
                            <div class="col-xl-3 col-md-6">
                                    <!-- card -->
                                    <div class="card card-h-100">
                                        <!-- card body -->
                                        <div class="card-body" style="background: #cccbfd;">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Closed Loans</span>
                                                    <h4 class="mb-3">
                                                        <a href="closed_loans.php" style="text-decoration: none; color: inherit;">
                                                        <span class="counter-value" data-target="7.54"><?php echo $closedloans; ?></span>
                                                        </a>
                                                    </h4>
                                                </div>
                                                <div class="flex-shrink-0 text-end dash-widget">
                                                    <div id="mini-chart3" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                                </div>
                                            </div>
                                        </div><!-- end card body 3-->
                                    </div><!-- end card -->
                        </div><!-- end col -->
                        </div><!-- end row-->

                       
                        <!-- end row-->

                    
                     
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6">
                                <script>document.write(new Date().getFullYear())</script> © Loan.
                            </div>
                            <div class="col-sm-6">
                                <div class="text-sm-end d-none d-sm-block">
                                    Design & Develop by <a href="https://seculabs.in/" class="text-decoration-underline" target="_blank">Seculabs</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        
        <!-- Right Sidebar -->
        <div class="right-bar">
            <div data-simplebar class="h-100">
                <div class="rightbar-title d-flex align-items-center p-3">

                    <h5 class="m-0 me-2"></h5>

                    <a href="javascript:void(0);" class="right-bar-toggle ms-auto">
                        <i class="mdi mdi-close noti-icon"></i>
                    </a>
                </div>

                <!-- Settings -->
                <hr class="m-0" />

                <div class="p-4">
                    <h6 class="mb-3">Select Custome Colors</h6>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input theme-color" type="radio" name="theme-mode"
                            id="theme-default" value="default" onchange="document.documentElement.setAttribute('data-theme-mode', 'default')" checked>
                        <label class="form-check-label" for="theme-default">Default</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input theme-color" type="radio" name="theme-mode"
                            id="theme-red" value="red" onchange="document.documentElement.setAttribute('data-theme-mode', 'red')">
                        <label class="form-check-label" for="theme-red">Red</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input theme-color" type="radio" name="theme-mode"
                            id="theme-purple" value="purple" onchange="document.documentElement.setAttribute('data-theme-mode', 'purple')">
                        <label class="form-check-label" for="theme-purple">Purple</label>
                    </div>


                    <h6 class="mt-4 mb-3 pt-2">Layout</h6>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout"
                            id="layout-vertical" value="vertical">
                        <label class="form-check-label" for="layout-vertical">Vertical</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout"
                            id="layout-horizontal" value="horizontal">
                        <label class="form-check-label" for="layout-horizontal">Horizontal</label>
                    </div>

                    <h6 class="mt-4 mb-3 pt-2">Layout Mode</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-mode"
                            id="layout-mode-light" value="light">
                        <label class="form-check-label" for="layout-mode-light">Light</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-mode"
                            id="layout-mode-dark" value="dark">
                        <label class="form-check-label" for="layout-mode-dark">Dark</label>
                    </div>

                    <h6 class="mt-4 mb-3 pt-2">Layout Width</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-width"
                            id="layout-width-fuild" value="fuild" onchange="document.body.setAttribute('data-layout-size', 'fluid')">
                        <label class="form-check-label" for="layout-width-fuild">Fluid</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-width"
                            id="layout-width-boxed" value="boxed" onchange="document.body.setAttribute('data-layout-size', 'boxed'),document.body.setAttribute('data-sidebar-size', 'sm')">
                        <label class="form-check-label" for="layout-width-boxed">Boxed</label>
                    </div>

                    <h6 class="mt-4 mb-3 pt-2">Layout Position</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-position"
                            id="layout-position-fixed" value="fixed" onchange="document.body.setAttribute('data-layout-scrollable', 'false')">
                        <label class="form-check-label" for="layout-position-fixed">Fixed</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-position"
                            id="layout-position-scrollable" value="scrollable" onchange="document.body.setAttribute('data-layout-scrollable', 'true')">
                        <label class="form-check-label" for="layout-position-scrollable">Scrollable</label>
                    </div>

                    <h6 class="mt-4 mb-3 pt-2">Topbar Color</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="topbar-color"
                            id="topbar-color-light" value="light" onchange="document.body.setAttribute('data-topbar', 'light')">
                        <label class="form-check-label" for="topbar-color-light">Light</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="topbar-color"
                            id="topbar-color-dark" value="dark" onchange="document.body.setAttribute('data-topbar', 'dark')">
                        <label class="form-check-label" for="topbar-color-dark">Dark</label>
                    </div>

                    <h6 class="mt-4 mb-3 pt-2 sidebar-setting">Sidebar Size</h6>

                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-size"
                            id="sidebar-size-default" value="default" onchange="document.body.setAttribute('data-sidebar-size', 'lg')">
                        <label class="form-check-label" for="sidebar-size-default">Default</label>
                    </div>
                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-size"
                            id="sidebar-size-compact" value="compact" onchange="document.body.setAttribute('data-sidebar-size', 'md')">
                        <label class="form-check-label" for="sidebar-size-compact">Compact</label>
                    </div>
                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-size"
                            id="sidebar-size-small" value="small" onchange="document.body.setAttribute('data-sidebar-size', 'sm')">
                        <label class="form-check-label" for="sidebar-size-small">Small (Icon View)</label>
                    </div>

                    <h6 class="mt-4 mb-3 pt-2 sidebar-setting">Sidebar Color</h6>

                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-color"
                            id="sidebar-color-light" value="light" onchange="document.body.setAttribute('data-sidebar', 'light')">
                        <label class="form-check-label" for="sidebar-color-light">Light</label>
                    </div>
                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-color"
                            id="sidebar-color-dark" value="dark" onchange="document.body.setAttribute('data-sidebar', 'dark')">
                        <label class="form-check-label" for="sidebar-color-dark">Dark</label>
                    </div>
                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-color"
                            id="sidebar-color-brand" value="brand" onchange="document.body.setAttribute('data-sidebar', 'brand')">
                        <label class="form-check-label" for="sidebar-color-brand">Brand</label>
                    </div>

                    <h6 class="mt-4 mb-3 pt-2">Direction</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-direction"
                            id="layout-direction-ltr" value="ltr">
                        <label class="form-check-label" for="layout-direction-ltr">LTR</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-direction"
                            id="layout-direction-rtl" value="rtl">
                        <label class="form-check-label" for="layout-direction-rtl">RTL</label>
                    </div>

           

                </div>

            </div> <!-- end slimscroll-menu-->
        </div>
        <!-- /Right-bar -->

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>


    </body>



</html>