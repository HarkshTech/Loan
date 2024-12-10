<?php 
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);// Start the session

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'branchmanager') {
    // Redirect to the login page or another appropriate page
    header("Location: index.php");
    exit();
}
if (isset($_GET['username'])) {
    include 'config.php';
        $salesuser = urldecode($_GET['username']);
        
        // Sanitize the input to prevent SQL injection
        $salesuser = mysqli_real_escape_string($conn, $salesuser);

    // $bmname=$_SESSION['username'];
    include 'leftsidebranch.php';
    

    // Fetch data for approved loans
    $query_totalleads = "SELECT COUNT(DISTINCT ID) AS totalleads FROM personalinformation WHERE assignedto='$salesuser' OR generatedby = 'Self(" . $salesuser . ")'";
    $result_totalleads = mysqli_query($conn, $query_totalleads);
    $row_totalleads = mysqli_fetch_assoc($result_totalleads);
    $totalleads = $row_totalleads['totalleads'];
    
    $query_assignedleads = "SELECT COUNT(DISTINCT ID) AS assignedleads FROM personalinformation WHERE assignedto='$salesuser'";
    $result_assignedleads = mysqli_query($conn, $query_assignedleads);
    $row_assignedleads = mysqli_fetch_assoc($result_assignedleads);
    $assignedleads = $row_assignedleads['assignedleads'];
    
    
    $query_pendingleads = "SELECT COUNT(DISTINCT ID) AS pendingleads FROM personalinformation where LeadStatus='Pending' AND (assignedto='$salesuser' OR generatedby = 'Self(" . $salesuser . ")')";
    $result_pendingleads = mysqli_query($conn, $query_pendingleads);
    $row_pendingleads = mysqli_fetch_assoc($result_pendingleads);
    $pendingleads = $row_pendingleads['pendingleads'];
    
    $query_Hot = "SELECT COUNT(DISTINCT ID) AS Hot FROM personalinformation where LeadStatus='Hot Lead' AND (assignedto='$salesuser' OR generatedby = 'Self(" . $salesuser . ")')";
    $result_Hot = mysqli_query($conn, $query_Hot);
    $row_Hot = mysqli_fetch_assoc($result_Hot);
    $Hot = $row_Hot['Hot'];
    
    $query_Cold = "SELECT COUNT(DISTINCT ID) AS Cold FROM personalinformation where LeadStatus='Cold Lead' AND (assignedto='$salesuser' OR generatedby = 'Self(" . $salesuser . ")')";
    $result_Cold = mysqli_query($conn, $query_Cold);
    $row_Cold = mysqli_fetch_assoc($result_Cold);
    $Cold = $row_Cold['Cold'];
    
    $query_Rejected = "SELECT COUNT(DISTINCT ID) AS Rejected FROM personalinformation where LeadStatus='Rejected' AND (assignedto='$salesuser' OR generatedby = 'Self(" . $salesuser . ")')";
    $result_Rejected = mysqli_query($conn, $query_Rejected);
    $row_Rejected = mysqli_fetch_assoc($result_Rejected);
    $Rejected = $row_Rejected['Rejected'];
    
    $query_pendingdigitalverifications = "SELECT COUNT(DISTINCT dc.LeadID) AS pendingdigitalverifications
FROM documentcollection dc
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
    )
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
    
    
    $query_pendingdocumentcollections = "
    SELECT COUNT(DISTINCT dc.LeadID) AS pendingdocumentcollections
    FROM documentcollection dc
    JOIN personalinformation pi ON dc.LeadID = pi.ID
    WHERE
        (pi.assignedto = '$salesuser' OR pi.generatedby = 'Self($salesuser)')
        AND (
            dc.Status1 IN ('Rejected', 'Pending')
            OR dc.Status2 IN ('Rejected', 'Pending')
            OR dc.Status3 IN ('Rejected', 'Pending')
            OR dc.Status4 IN ('Rejected', 'Pending')
            OR dc.Status5 IN ('Rejected', 'Pending')
            OR dc.Status6 IN ('Rejected', 'Pending')
            OR dc.Status7 IN ('Rejected', 'Pending')
            OR dc.Status8 IN ('Rejected', 'Pending')
            OR dc.Status9 IN ('Rejected', 'Pending')
            OR dc.Status10 IN ('Rejected', 'Pending')
            OR dc.Status11 IN ('Rejected', 'Pending')
            OR dc.Status12 IN ('Rejected', 'Pending')
        )";
        
        $result_pendingdocumentcollections = mysqli_query($conn, $query_pendingdocumentcollections);
        
        if (!$result_pendingdocumentcollections) {
            die('Query Error: ' . mysqli_error($conn));
        }
        
        $row_pendingdocumentcollections = mysqli_fetch_assoc($result_pendingdocumentcollections);
        $pendingdocumentcollections = $row_pendingdocumentcollections['pendingdocumentcollections'];
    
    $query_pendingfieldverifications = "SELECT COUNT(DISTINCT vf.leadID)
            AS pendingfieldverifications 
        FROM VerificationForms vf
        JOIN personalinformation pi ON vf.leadID=pi.ID
        WHERE
                (pi.assignedto = '$salesuser' OR pi.generatedby = 'Self($salesuser)')
                AND (
            verificationStatus_Home IN ('Rejected', 'Pending')
            OR verificationStatus_Business IN ('Rejected', 'Pending'))";
    $result_pendingfieldverifications = mysqli_query($conn, $query_pendingfieldverifications);
    $row_pendingfieldverifications = mysqli_fetch_assoc($result_pendingfieldverifications);
    $pendingfieldverifications = $row_pendingfieldverifications['pendingfieldverifications'];
    

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
                                            <li class="breadcrumb-item"><a href="branchmanager">Main Dashboard</a></li>
                                            <li class="breadcrumb-item"><a href="salesteambmdashboard.php">Sales Dashboard</a></li>
                                            <li class="breadcrumb-item">Welcome !</li>
                                            <li class="breadcrumb-item active"><?php echo $salesuser; ?></li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row" >
                            <!--Total Leads-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #68e4f1;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Leads</span>
                                                <h4 class="mb-3">
                                                    
                                                    <a href="total_sales_leads.php?username=<?php echo $salesuser;?>" style="text-decoration: none; color: inherit;">
                                                    <span class="counter-value" data-target="1256"><?php echo $totalleads; ?></span>
                                                    </a>
                                                </h4>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart2" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body 2-->
                                </div><!-- end card -->
                            </div>
                            <!-- end col-->
                            
                            <!--Assigned Leads-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #becdef;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Assigned Leads</span>
                                                <h4 class="mb-3">
                                                    
                                                    <a href="total_assigned_leads.php?username=<?php echo $salesuser;?>" style="text-decoration: none; color: inherit;">
                                                    <span class="counter-value" data-target="1256"><?php echo $assignedleads; ?></span>
                                                    </a>
                                                </h4>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart2" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body 2-->
                                </div><!-- end card -->
                            </div><!-- end col-->
                            
                            <!--Hot Leads-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #c9e7db;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Hot Leads</span>
                                                <a href="total_hot_leads.php?username=<?php echo $salesuser;?>" style="text-decoration: none; color: inherit;">
                                                <h4 class="mb-3">
                                                    <span class="counter-value" data-target="7.54"><?php echo $Hot; ?></span>
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
                            
                            <!--Pending Leads-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #edd3d5;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Pending Leads</span>
                                                <a href="total_pending_leads.php?username=<?php echo $salesuser;?>" style="text-decoration: none; color: inherit;">
                                                <h4 class="mb-3">
                                                    <span class="counter-value" data-target="7.54"><?php echo $pendingleads; ?></span>
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
                            
                            <!--Cold Leads-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background:#e1e7c9;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Cold Leads</span>
                                                <a href="total_cold_leads.php?username=<?php echo $salesuser;?>" style="text-decoration: none; color: inherit;">
                                                <h4 class="mb-3">
                                                    <span class="counter-value" data-target="18.34"><?php echo $Cold; ?></span>
                                                </h4>
                                                </a>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart4" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body 4-->
                                </div><!-- end card -->
                            </div><!-- end col -->  
                            
                            <!--Rejected Leads-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background:#e78181;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Rejected</span>
                                                <a href="total_rejected_leads.php?username=<?php echo $salesuser;?>" style="text-decoration: none; color: inherit;">
                                                <h4 class="mb-3">
                                                    <span class="counter-value" data-target="18.34"><?php echo $Rejected; ?></span>
                                                </h4>
                                                </a>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart4" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body 4-->
                                </div><!-- end card -->
                            </div><!-- end col -->
                            
                            <!--Pending Digital Verifications-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background:#edcfb0;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Pending Digital Verifications</span>
                                                <a href="total_pendingdigitalverifications.php?username=<?php echo $salesuser;?>" style="text-decoration: none; color: inherit;">
                                                <h4 class="mb-3">
                                                    <span class="counter-value" data-target="18.34"><?php echo $pendingdigitalverifications; ?></span>
                                                </h4>
                                                </a>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart4" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body 4-->
                                </div><!-- end card -->
                            </div><!-- end col -->
                            
                            <!--Pending Field Verifications-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background:#efe19a;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Pending Field Verifications</span>
                                                <a href="total_pendingfieldverifications.php?username=<?php echo $salesuser;?>" style="text-decoration: none; color: inherit;">
                                                <h4 class="mb-3">
                                                    <span class="counter-value" data-target="18.34"><?php echo $pendingfieldverifications; ?></span>
                                                </h4>
                                                </a>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart4" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body 4-->
                                </div><!-- end card -->
                            </div><!-- end col -->
                            
                        </div>

                       
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