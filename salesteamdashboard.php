<?php 
session_start(); // Start the session

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'branchmanager') {
    // Redirect to the login page or another appropriate page
    header("Location: index.php");
    exit();
} else {
    $bmname = $_SESSION['username'];
    include 'leftsidebranch.php';
    include 'config.php';

    // Fetch data for approved loans
    $query_approved_loans = "SELECT COUNT(DISTINCT LeadID) AS approved_loans FROM approval_information";
    $result_approved_loans = mysqli_query($conn, $query_approved_loans);
    $row_approved_loans = mysqli_fetch_assoc($result_approved_loans);
    $approved_loans = $row_approved_loans['approved_loans'];

    // Fetch user data for displaying cards
    $query_users = "SELECT id, username FROM users WHERE role='sales' AND branchmanager='$bmname'";
    $result_users = mysqli_query($conn, $query_users);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta tags and other head content -->
</head>

<body>
    <!-- Your existing HTML content -->

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

                <div class="row">
                    <?php
                    if ($result_users->num_rows > 0) {
                        while ($row = $result_users->fetch_assoc()) {
                            $username = $row['username'];

                            // Calculate sales count for each user
                            $query_sales_count = "
                                SELECT COUNT(DISTINCT ID) AS sales_count
                                FROM personalinformation
                                WHERE assignedto='$username' OR generatedby = 'Self(" . $username . ")'";
                            $result_sales_count = mysqli_query($conn, $query_sales_count);
                            $row_sales_count = mysqli_fetch_assoc($result_sales_count);
                            $sales_count = $row_sales_count['sales_count'];
                            ?>
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body" style="background: #becdef;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate"><?php echo htmlspecialchars($username); ?></span>
                                                <h4 class="mb-3">
                                                    <a href="#" style="text-decoration: none; color: inherit;">
                                                        <span class="counter-value" data-target="<?php echo htmlspecialchars($sales_count); ?>"><?php echo htmlspecialchars($sales_count); ?></span>
                                                    </a>
                                                </h4>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart2" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col -->
                            <?php
                        }
                    } else {
                        echo "No users found";
                    }
                    ?>
                </div><!-- end row -->

            </div><!-- container-fluid -->
        </div><!-- End Page-content -->

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
    </div><!-- end main content -->

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
                <h6 class="mb-3">Select Custom Colors</h6>
                <!-- Color selection and other settings -->
            </div>
        </div> <!-- end slimscroll-menu-->
    </div><!-- /Right-bar -->

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

</body>
</html>
