<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); // Start the session

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'verifier') {
    // Redirect to the login page or another appropriate page
    header("Location: index.php");
    exit();
}



else{
    include 'leftsideverifier.php';
    include 'config.php';
    $username = $_SESSION['username'];


    $username = $_SESSION['username']; // Assuming username is stored in the session
    echo $username;

$query_missing_count_field = "
    SELECT COUNT(p.ID) AS missing_count_field
    FROM personalinformation p
    LEFT JOIN VerificationForms vf ON p.ID = vf.leadID
    LEFT JOIN documentcollection dc ON p.ID = dc.LeadID
    WHERE vf.leadID IS NULL
    AND dc.fieldverifier = ?
";
$stmt = $conn->prepare($query_missing_count_field);
$stmt->bind_param("s", $username);
$stmt->execute();
$result_missing_count_field = $stmt->get_result();
$row_missing_count_field = $result_missing_count_field->fetch_assoc();
$missing_count_field = $row_missing_count_field['missing_count_field'];
    
    $query_missing_count_legal = "
    SELECT COUNT(p.ID) AS missing_count_legal
    FROM personalinformation p
    LEFT JOIN legal_evaluations le ON p.ID = le.lead_id
    LEFT JOIN documentcollection dc ON p.ID = dc.LeadID
    WHERE le.lead_id IS NULL
    AND p.LoanPurpose = 'LAP'
    AND dc.legalverifier = ?
";
$stmt = $conn->prepare($query_missing_count_legal);
$stmt->bind_param("s", $username);
$stmt->execute();
$result_missing_count_legal = $stmt->get_result();
$row_missing_count_legal = $result_missing_count_legal->fetch_assoc();
$missing_count_legal = $row_missing_count_legal['missing_count_legal'];
    
    $query_missing_count_evaluation = "SELECT COUNT(p.ID) AS missing_count_evaluation
    FROM personalinformation p
    LEFT JOIN VerificationForms vf ON p.ID = vf.leadID
    INNER JOIN legal_evaluations le ON p.ID = le.lead_id
    WHERE vf.leadID IS NULL
    AND le.evaluation_needed = 1";
    $result_missing_count_evaluation = mysqli_query($conn, $query_missing_count_evaluation);
    $row_missing_count_evaluation = mysqli_fetch_assoc($result_missing_count_evaluation);
    $missing_count_evaluation = $row_missing_count_evaluation['missing_count_evaluation'];
    

    $missing_count_total = intval($missing_count_field) + intval($missing_count_evaluation) + intval($missing_count_legal);
    
    
    
    
}

?>

            

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">
                
                <!---mandeep css-->
                <style>
                    .footer{
                        bottom:-99px;
                    }
                    body[data-sidebar-size=sm] .vertical-menu #sidebar-menu>ul>li {
                        background-color:white !important;
                    }
                    #sidebar-menu {
    padding: 10px 0 0px 0;
}
                </style>

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

                        <div class="row">
                            <!--Total Pending Verifications-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100" >
                                    <!-- card body -->
                                   <div class="card-body" style="background: #bed2ef;">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Pending Verifications</span>
                                            <h4 class="mb-3">
                                                <a href="#" style="text-decoration: none; color: inherit;">
                                                    <span class="counter-value" data-target="354.5"><?php echo $missing_count_total; ?></span>
                                                </a>
                                            </h4>
                                        </div>

        <div class="flex-shrink-0 text-end dash-widget">
            <div id="mini-chart1" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
        </div>
    </div>
</div><!-- end card body 1-->

                                </div><!-- end card -->
                            </div><!-- end col -->
                            
                            <!--Pending Field Verifications-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100" >
                                    <!-- card body -->
                                   <div class="card-body" style="background: #ebd5df;">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Pending Field Verifications</span>
                                            <h4 class="mb-3">
                                                <a href="#" style="text-decoration: none; color: inherit;">
                                                    <span class="counter-value" data-target="354.5"><?php echo $missing_count_field; ?></span>
                                                </a>
                                            </h4>
                                        </div>

                                    <div class="flex-shrink-0 text-end dash-widget">
                                        <div id="mini-chart1" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                    </div>
                                </div>
                            </div><!-- end card body 1-->

                                </div><!-- end card -->
                            </div><!-- end col -->
                            
                            <!--Pending Legal Verifications-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100" >
                                    <!-- card body -->
                                   <div class="card-body" style="background: #edf7c6;">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Pending Legal Verifications</span>
                                            <h4 class="mb-3">
                                                <a href="#" style="text-decoration: none; color: inherit;">
                                                    <span class="counter-value" data-target="354.5"><?php echo $missing_count_legal; ?></span>
                                                </a>
                                            </h4>
                                        </div>

        <div class="flex-shrink-0 text-end dash-widget">
            <div id="mini-chart1" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
        </div>
    </div>
</div><!-- end card body 1-->

                                </div><!-- end card -->
                            </div><!-- end col -->
                            
                            <!--Pending Evaluation Reports-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100" >
                                    <!-- card body -->
                                   <div class="card-body" style="background: #e3d1fb;">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Pending Evaluation Reports</span>
                                            <h4 class="mb-3">
                                                <a href="#" style="text-decoration: none; color: inherit;">
                                                    <span class="counter-value" data-target="354.5"><?php echo $missing_count_evaluation; ?></span>
                                                </a>
                                            </h4>
                                        </div>

                                    <div class="flex-shrink-0 text-end dash-widget">
                                        <div id="mini-chart1" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                    </div>
                                </div>
                            </div><!-- end card body 1-->

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
        <!--<div class="rightbar-overlay"></div>-->


    </body>



</html>