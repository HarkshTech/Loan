<?php 
    session_start();
    $loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

  // Check if the user is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'sales') {
    // Redirect to the login page or another appropriate page
    header("Location: index.php");
    exit();
}
else{
    include 'leftsidesales.php';
    include 'config.php';
    $username = $_SESSION['username'];
    $assignedto = $_SESSION['username'];
    
    
    $query_borrowers = "SELECT COUNT(DISTINCT ID) AS borrowers FROM personalinformation WHERE assignedto='$assignedto' OR generatedby = 'Self(" . $loggedInUser . ")'";
    $result_borrowers = mysqli_query($conn, $query_borrowers);
    $row_borrowers = mysqli_fetch_assoc($result_borrowers);
    $borrowers = $row_borrowers['borrowers'];
    
        // Fetch data for approved loans
        $query_approved_loans = "SELECT COUNT(DISTINCT LeadID) AS approved_loans FROM approval_information";
        $result_approved_loans = mysqli_query($conn, $query_approved_loans);
        $row_approved_loans = mysqli_fetch_assoc($result_approved_loans);
        $approved_loans = $row_approved_loans['approved_loans'];
        
        
        
        $query_pendingleads = "SELECT COUNT(DISTINCT ID) AS pendingleads FROM personalinformation where LeadStatus='New Lead' &&  (assignedto='$assignedto' OR generatedby = 'Self(" . $loggedInUser . ")')";
        $result_pendingleads = mysqli_query($conn, $query_pendingleads);
        $row_pendingleads = mysqli_fetch_assoc($result_pendingleads);
        $pendingleads = $row_pendingleads['pendingleads'];
        
        $query_Hot = "SELECT COUNT(DISTINCT ID) AS Hot FROM personalinformation where LeadStatus='Hot Lead' &&  (assignedto='$assignedto' OR generatedby = 'Self(" . $loggedInUser . ")')";
        $result_Hot = mysqli_query($conn, $query_Hot);
        $row_Hot = mysqli_fetch_assoc($result_Hot);
        $Hot = $row_Hot['Hot'];
        
        $query_Cold = "SELECT COUNT(DISTINCT ID) AS Cold FROM personalinformation where LeadStatus='Cold Lead' && (assignedto='$assignedto' OR generatedby = 'Self(" . $loggedInUser . ")')";
        $result_Cold = mysqli_query($conn, $query_Cold);
        $row_Cold = mysqli_fetch_assoc($result_Cold);
        $Cold = $row_Cold['Cold'];
        
        $query_Rejected = "SELECT COUNT(DISTINCT ID) AS Rejected FROM personalinformation where LeadStatus='Rejected' && (assignedto='$assignedto' OR generatedby = 'Self(" . $loggedInUser . ")')";
        $result_Rejected = mysqli_query($conn, $query_Rejected);
        $row_Rejected = mysqli_fetch_assoc($result_Rejected);
        $Rejected = $row_Rejected['Rejected'];
        
        $query_pendingdigitalverifications = "SELECT COUNT(DISTINCT ID)
        AS pendingdigitalverifications
    FROM documentcollection
    WHERE 
        Status1 IN ('Rejected', 'Pending')
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && is_numeric($_POST['id'])) {
            // Sanitize the ID to prevent SQL injection
            $id = mysqli_real_escape_string($conn, $_POST['id']);
            
            // Update the status of the notification to 'read' in the database
            $update_query = "UPDATE notifications SET status = 'read' WHERE id = '$id'";
            if (mysqli_query($conn, $update_query)) {
                echo 'success'; // Send 'success' response back to the AJAX call
            } else {
                echo 'error';
            }
        }
        
        
        
}
?>

<!DOCTYPE html>
    <html>
    <head>
        <style>
        /* CSS styles for notification */
        .notifications-container {
            /*margin: 50px auto;*/
            width: 100%;
            /*max-width: 800px;*/
        }

        .notification {
            border: 1px solid #ccc;
            background-color: #fff;
            padding: 15px;
            margin-bottom: 10px;
            position: relative;
            transition: all 0.3s ease;
        }

        .notification h3 {
            margin-top: 0;
            margin-bottom: 5px;
            color: #333;
        }

        .notification .details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification .details p {
            margin: 0;
            color: #666;
        }

        .notification .mark-read {
            position: absolute;
            top: 5px;
            right: 5px;
            color: green;
            cursor: pointer;
        }

        /* Style for read notifications */
        .notification.read {
            background-color: #f2f2f2;
            border-color: #ccc;
        }

        /* Add margin when there are no notifications */
        .no-notifications {
            margin-top: 20px;
            text-align: center;
            color: #666;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    </head>
    <body>

            

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="notifications-container">
                <!-- PHP script to fetch dynamic notifications -->
                <?php
                // Include your database connection file
                include 'config.php';
        
                // Fetch unread notifications from the database
                $query = "SELECT * FROM notifications WHERE status = 'unread' AND (nfor='$loggedInUser' OR nfor='$role')";
                $result = mysqli_query($conn, $query);
        
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $statusClass = $row['status'] === 'read' ? 'read' : '';
                        echo "<div class='notification $statusClass' id='notification_{$row['id']}'>
                                <h3>{$row['title']}</h3>
                                <div class='details'>
                                    <p>{$row['message']}</p>
                                    <p><strong>Notification By:</strong> {$row['nby']}</p>
                                    <span class='mark-read' onclick='markAsReadAndHide({$row['id']})'>&#10004;</span>
                                </div>
                            </div>";
                    }
                }
                ?>
                </div>
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
                            <!--Total Borrowers-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #becdef;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Leads</span>
                                                <h4 class="mb-3">
                                                     <a href="salesborrower_details.php" style="text-decoration: none; color: inherit;">
                                                    <span class="counter-value" data-target="1256"><?php echo $borrowers; ?></span>
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
                            <!--Pending Leads-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #edd3d5;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Pending Leads</span>
                                                    <a href="salespendingleads.php" style="text-decoration: none; color: inherit;">
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
                            <!--Hot Leads-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #c9e7db;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Hot Leads</span>
                                                   <a href="saleshotleads.php" style="text-decoration: none; color: inherit;">
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
                            <!--Cold Leads-->
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background:#e1e7c9;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Cold Leads</span>
                                                <h4 class="mb-3">
                                                      <a href="salescoldleads.php" style="text-decoration: none; color: inherit;">
                                                    <span class="counter-value" data-target="18.34"><?php echo $Cold; ?></span>
                                                    </a>
                                                </h4>
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
                                                <h4 class="mb-3">
                                                    <a href="salesrejectleads.php" style="text-decoration: none; color: inherit;">
                                                    <span class="counter-value" data-target="18.34"><?php echo $Rejected; ?></span>
                                                    </a>
                                                </h4>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart4" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body 4-->
                                </div><!-- end card -->
                            </div><!-- end col -->
                     

                     
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
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- JavaScript for AJAX and closing notifications -->
    <script>
        function markAsReadAndHide(id) {
            // AJAX call to update the status in the database
            $.ajax({
                url: 'update_notification.php',
                method: 'POST',
                data: { id: id },
                success: function(response) {
                    if (response === 'success') {
                        $('#notification_' + id).hide();
                    } else {
                        console.log('Error updating notification status');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', error);
                }
            });
        }
    </script>


    </body>



</html>