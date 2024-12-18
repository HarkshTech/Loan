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
    <link rel="shortcut icon" href="assets/images/favicon.ico">

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
                                <img src="assets/images/logo-sm.svg" alt="" height="30">
                            </span>
                            <span class="logo-lg">
                                <img src="assets/images/logo-sm.svg" alt="" height="24"> <span
                                    class="logo-txt">Loan</span>
                            </span>
                        </a>

                        <a href="dashboardsales.php" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="assets/images/logo-sm.svg" alt="" height="30">
                            </span>
                            <span class="logo-lg">
                                <img src="assets/images/logo-sm.svg" alt="" height="24"> <span
                                    class="logo-txt">Loan</span>
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>

                    <!-- App Search-->


                </div>
                <?php
                $adminname = $_SESSION['username'];
                $role = $_SESSION['role'];
                // Database connection
                include('config.php');

                // Fetch the unread notifications count
                $queryn = "SELECT COUNT(*) AS unread_count FROM notifications WHERE status = 'unread' AND (nfor='$adminname' OR nfor='$role')";
                $resultn = mysqli_query($conn, $queryn);

                // Fetch the count value
                $unreadCount = 0; // Default count is 0 in case of no result
                if ($resultn && $rown = mysqli_fetch_assoc($resultn)) {
                    $unreadCount = $rown['unread_count'];
                }

                ?>

                <div class="notifications">
                    <button id="openNotifications"><img src="assets/images/notifications.svg" alt="" width="24px"></button>
                    <p><?php echo $unreadCount; ?></p>
                </div>

                <?php
                $adminname = $_SESSION['username'];
                $role = $_SESSION['role'];
                // Database connection
                // include('config.php');

                // Fetch unread notifications for the logged-in admin or their role
                $queryno = "SELECT * FROM notifications WHERE status = 'unread' AND (nfor='$adminname' OR nfor='$role')";
                $resultno = mysqli_query($conn, $queryno);

                // Begin the modal HTML
                echo '<div class="notifications-modal" id="notifications-modal">';

                // Check if there are results
                if (mysqli_num_rows($resultno) > 0) {
                    // Loop through notifications and populate the modal dynamically
                    while ($rowno = mysqli_fetch_assoc($resultno)) {
                        echo '<div class="notification2">';
                        echo '    <div class="notification-details">';
                        echo '        <p>' . htmlspecialchars($rowno['title']) . '</p>';
                        echo '        <p>' . htmlspecialchars($rowno['message']) . '</p>';
                        echo '    </div>';
                        echo '    <div class="sender">';
                        echo '        <p class="' . htmlspecialchars($rowno['status']) . '" onclick="markAsReadAndHide(' . $rowno['id'] . ')">&#10004;</p>';
                        echo '        <p><span>Notification By:</span> ' . htmlspecialchars($rowno['nby']) . '</p>';
                        echo '    </div>';
                        echo '</div>';
                    }
                } else {
                    // Display a message if no unread notifications are found
                    echo '<p class="no-notifications">No new notifications</p>';
                }

                // Close the modal HTML
                echo '</div>';
                ?>
                <style>
                    .sender>p[class="unread"]{
                        color: red;
                        cursor: pointer;
                    }
                    p{
                        margin: 0;padding: 0;
                    }
                    .notifications{
                        position: absolute;
                        cursor: pointer;
                        right: 100px;
                        border-radius: 50px;
                        padding: 10px 5px;
                    }
                    .notifications:hover{
                        background-color: #d3d3d347;
                    }
                    .notifications>button{
                        border: none;
                        background-color: transparent;
                    }
                    .notifications>p{
                        position: absolute;
                        right: 3px;
                        top: 6px;
                        color: white;
                        padding: 0px 5px;
                        background-color: red;
                        border-radius: 50px;
                        margin: 0 !important;
                    }
                    .notifications-modal{
                        display: none;
                        position: absolute;
                        /* display: flex; */
                        flex-direction: column;
                        gap: 10px;
                        background-color: lightgray;
                        border-radius: 5px;
                        padding: 10px;
                        right: 16px;
                        top: 88px;
                        max-height: 40vh;
                        width: 30%;
                        box-shadow: 2px 2px 10px 5px #00000040;
                        animation: myAnim 2s ease 0s 1 normal forwards;
                        overflow: auto;
                        box-sizing: border-box;
                    }
                    @keyframes myAnim {
                        0% {
                            animation-timing-function: ease-in;
                            opacity: 1;
                            transform: translateY(-45px);
                        }

                        24% {
                            opacity: 1;
                        }

                        40% {
                            animation-timing-function: ease-in;
                            transform: translateY(-24px);
                        }

                        65% {
                            animation-timing-function: ease-in;
                            transform: translateY(-12px);
                        }

                        82% {
                            animation-timing-function: ease-in;
                            transform: translateY(-6px);
                        }

                        93% {
                            animation-timing-function: ease-in;
                            transform: translateY(-4px);
                        }

                        25%,
                        55%,
                        75%,
                        87% {
                            animation-timing-function: ease-out;
                            transform: translateY(0px);
                        }

                        100% {
                            animation-timing-function: ease-out;
                            opacity: 1;
                            transform: translateY(0px);
                        }
                    }
                    .notifications-modal::-webkit-scrollbar{
                        width: 12px; /* Width of vertical scrollbar */
                        height: 12px; /* Height of horizontal scrollbar */
                    }

                    /* Scrollbar thumb */
                    .notifications-modal::-webkit-scrollbar-thumb {
                        background: darkgray; /* Color of the scrollbar thumb */
                        border-radius: 6px;   /* Rounded corners for the thumb */
                    }

                    /* Scrollbar thumb on hover */
                    .notifications-modal::-webkit-scrollbar-thumb:hover {
                        background: gray; /* Color of the thumb when hovered */
                    }

                    /* Scrollbar track */
                    .notifications-modal::-webkit-scrollbar-track {
                        background: lightgray; /* Background of the scrollbar track */
                        border-radius: 6px;
                    }
                    .notification2{
                        display: flex;
                        gap: 20px;
                        background-color: white;
                        padding: 4px 20px;
                        border-radius: 5px;

                    }
                    .notification-details p:first-child{
                        font-weight: 700;
                    }
                    .notification-details p:nth-child(2){
                        font-weight: 500;
                    }
                    .notification-details p:nth-child(2){
                        font-weight: 500;
                    }
                    .sender span{
                        font-weight: 600;
                    }
                </style>

                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script>
                    // Get modal, toggle button, and close button
                    const modal = document.getElementById("notifications-modal");
                    const toggleModalBtn = document.getElementById("openNotifications");
                    const closeModalBtn = document.getElementById("closeModalBtn");

                    // Toggle modal on button click
                    toggleModalBtn.addEventListener("click", () => {
                    if (modal.style.display === "flex") {
                        modal.style.display = "none"; // Close the modal
                    } else {
                        modal.style.display = "flex"; // Open the modal
                    }
                    });

                    // Close modal when close button is clicked
                    closeModalBtn.addEventListener("click", () => {
                    modal.style.display = "none";
                    });

                    // Close modal when clicking outside modal content
                    window.addEventListener("click", (e) => {
                    if (e.target === modal) {
                        modal.style.display = "none";
                    }
                    });

                        function markAsReadAndHide(id) {
                        // AJAX call to update the status in the database
                        $.ajax({
                            url: 'update_notification.php',
                            method: 'POST',
                            data: {
                                id: id
                            },
                            success: function(response) {
                                if (response === 'success') {
                                    // Reload the page upon successful response
                                    location.reload();
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

                <div class="d-flex">
                    <div class="dropdown d-none d-sm-inline-block">
                        <div id="navbar-username" style="display: none;"><?php
                        $username=$_SESSION['username']; 
                        echo htmlspecialchars($username); 
                        ?></div>
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
                            <a href="dashboardsales.php">
                                <i data-feather="home"></i>
                                <span data-key="t-dashboard">Dashboard</span>
                            </a>
                        </li>
                    
                        <li>
                            <a href="borrowersales.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Borrowers</span>
                            </a>
                        </li>
                    
                       
                     <li>
                            <a href="leadsales1.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Leads</span>
                            </a>
                        </li>
                      


                       <li>
                            <a href="hotleadsales.php">
                                <i data-feather="users"></i>
                                <span data-key="t-contacts">Hot Leads</span>
                            </a>
                        </li>
                     
                      


                       <!--<li>-->
                       <!--     <a href="salesverify1.php">-->
                       <!--         <i data-feather="users"></i>-->
                       <!--         <span data-key="t-contacts">Field Verification</span>-->
                       <!--     </a>-->
                       <!-- </li>-->
                        
                       <!--<li>-->
                       <!--     <a href="salesverify1.php">-->
                       <!--         <i data-feather="users"></i>-->
                       <!--         <span data-key="t-contacts">Doc Verification</span>-->
                       <!--     </a>-->
                       <!-- </li>-->
                     
                      
                        <!--<li>-->
                        <!--    <a href="failedemi1.php">-->
                        <!--        <i data-feather="users"></i>-->
                        <!--        <span data-key="t-contacts">Failed EMI</span>-->
                        <!--    </a>-->
                        <!--</li>-->
                        
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
