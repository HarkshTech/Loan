<?php 
session_start(); // Start the session

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'recovery' && $_SESSION['role'] !== 'admin')) {
    // Redirect to the login page or another appropriate page
    header("Location: index.php");
    exit();
}

else{
    if($_SESSION['role'] === 'recovery'){
        include 'leftbarrecovery.php';
    }
    elseif($_SESSION['role'] === 'admin'){
        include 'leftside.php';
    }
    
    include 'config.php';
    
    $loggedUser=$_SESSION['username'];


    // Fetch data for approved loans
    $query_pendingrecoveries = "SELECT COUNT(es.ID) AS pendingrecoveries
FROM emi_schedule es
JOIN recovery_data rd ON es.LeadID = rd.LeadID
WHERE es.overdue_days > 5 
  AND es.RecoveryAssigning = 'yes'
  AND rd.CaseStatus NOT IN ('Recovery Done', 'Legal Case')";
    $result_pendingrecoveries = mysqli_query($conn, $query_pendingrecoveries);
    $row_pendingrecoveries = mysqli_fetch_assoc($result_pendingrecoveries);
    $pendingrecoveries = $row_pendingrecoveries['pendingrecoveries'];
    if($_SESSION['role']==='recovery'){
        $query_visits = "SELECT COUNT(DISTINCT LeadID) AS visits FROM recovery_data WHERE (visitscheduled=CURDATE() OR visitagaindate=CURDATE()) AND CaseStatus NOT IN ('Legal Case','Recovery Done') AND AssignedTo='$loggedUser' ";
    }
    else{
        $query_visits = "SELECT COUNT(DISTINCT LeadID) AS visits FROM recovery_data WHERE (visitscheduled=CURDATE() OR visitagaindate=CURDATE()) AND CaseStatus NOT IN ('Legal Case','Recovery Done')";
    }
    
    $result_visits = mysqli_query($conn, $query_visits);
    $row_visits = mysqli_fetch_assoc($result_visits);
    $visits = $row_visits['visits'];
    
    $query_pendingrecovery = "SELECT COUNT(DISTINCT LeadID) AS pendingrecovery FROM emi_schedule WHERE overdue_days > 5 
  AND (RecoveryAssigning <> 'yes' OR RecoveryAssigning IS NULL)";
    $result_pendingrecovery = mysqli_query($conn, $query_pendingrecovery);
    $row_pendingrecovery = mysqli_fetch_assoc($result_pendingrecovery);
    $pendingrecovery = $row_pendingrecovery['pendingrecovery'];
    
    $query_failedemi = "SELECT COUNT(DISTINCT LeadID) AS failedemi FROM emi_schedule WHERE overdue_days > 5 
  AND (RecoveryAssigning <> 'yes' OR RecoveryAssigning IS NULL)";
    $result_failedemi = mysqli_query($conn, $query_failedemi);
    $row_failedemi = mysqli_fetch_assoc($result_failedemi);
    $failedemi = $row_failedemi['failedemi'];
    
    $query_donerecoveries = "SELECT COUNT(DISTINCT LeadID) AS donerecoveries FROM recovery_data WHERE CaseStatus=''";
    $result_donerecoveries = mysqli_query($conn, $query_donerecoveries);
    $row_donerecoveries = mysqli_fetch_assoc($result_donerecoveries);
    $donerecoveries= $row_donerecoveries['donerecoveries'];
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
                                            <li class="breadcrumb-item"><a href="#">Recovery Dept. Dashboard</a></li>
                                            <li class="breadcrumb-item active">Welcome !</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row" >
                            <!--Total Pending Recoveries-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100" >
                                    <!-- card body -->
                                    <div class="card-body" style="background: #bee6ef;" >
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Pending Recoveries</span>
                                                <h4 class="mb-3">
                                                    <?php if($_SESSION['role']==='admin'){?>
                                                        <a href="allpendingrecoveries.php" style="text-decoration: none; color: inherit;">
                                                    <?php }?>
                                                    <span class="counter-value" data-target="354.5"><?php echo $pendingrecoveries; ?></span>
                                                    <?php if($_SESSION['role']==='admin'){?>
                                                        </a>
                                                    <?php }?>
                                                </h4>
                                            </div>
        
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart1" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body 1-->
                                </div><!-- end card -->
                            </div><!-- end col -->
                            
                            <!--Visit's Today-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #c9e7db;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Visit's Today</span>
                                                <h4 class="mb-3">
                                                    <a href="visitneeded.php" style="text-decoration: none; color: inherit;">
                                                    <span class="counter-value" data-target="7.54"><?php echo $visits; ?></span>
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
                            
                            <!--Assigned Recoveries-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #becdef;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Assigned Recoveries</span>
                                                <h4 class="mb-3">
                                                    <a href="assigned_recoveries.php" style="text-decoration: none; color: inherit;">
                                                    <span class="counter-value" data-target="1256"><?php echo $pendingrecovery; ?></span>
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
                            
                            <!--Follow Ups-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #edd3d5;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Follow Ups</span>
                                                <h4 class="mb-3">
                                                    <a href="followups_recovery.php" style="text-decoration: none; color: inherit;">
                                                    <span class="counter-value" data-target="7.54"><?php echo $failedemi; ?></span>
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
                            
                            <?php if($_SESSION['role']==='admin'){?>
                                <!--View Recovery Data-->
                                <div class="col-xl-3 col-md-6">
                                    <!-- card -->
                                    <div class="card card-h-100">
                                        <!-- card body -->
                                        <div class="card-body" style="background: #e4e1e9;">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">View Recovery Data</span>
                                                    <h4 class="mb-3">
                                                        <a href="viewrecoverydata.php" style="text-decoration: none; color: inherit;">
                                                        <span class="counter-value" data-target="7.54"><?php echo $failedemi; ?></span>
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
                                
                                <!--Done Recoveries-->
                                <div class="col-xl-3 col-md-6">
                                    <!-- card -->
                                    <div class="card card-h-100">
                                        <!-- card body -->
                                        <div class="card-body" style="background: #bdebba;">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Recoveries Done</span>
                                                    <h4 class="mb-3">
                                                        <a href="recoveriesdone.php" style="text-decoration: none; color: inherit;">
                                                        <span class="counter-value" data-target="7.54"><?php echo $donerecoveries; ?></span>
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
                            <?php }?>
                            
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