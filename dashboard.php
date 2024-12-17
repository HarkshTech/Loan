<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';
session_start(); // Start the session

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the login page or another appropriate page
    header("Location: index.php");
    exit();
} else {
    include 'leftside.php';
    $adminname = $_SESSION['username'];
    $role = $_SESSION['role'];
    // include 'config.php';
    // Start output buffering

    // Include the notification script
    include 'system_notifications.php';

    // Clean the buffer

    // Fetch data for approved loans
    $query_approved_loans = "SELECT COUNT(DISTINCT LeadID) AS approved_loans FROM approval_information WHERE isDisbursed='1'";
    $result_approved_loans = mysqli_query($conn, $query_approved_loans);
    $row_approved_loans = mysqli_fetch_assoc($result_approved_loans);
    $approved_loans = $row_approved_loans['approved_loans'];

    $query_borrowers = "SELECT COUNT(DISTINCT ID) AS borrowers FROM personalinformation";
    $result_borrowers = mysqli_query($conn, $query_borrowers);
    $row_borrowers = mysqli_fetch_assoc($result_borrowers);
    $borrowers = $row_borrowers['borrowers'];

    $query_pendingleads = "SELECT COUNT(DISTINCT ID) AS pendingleads FROM personalinformation where LeadStatus='New Lead'";
    $result_pendingleads = mysqli_query($conn, $query_pendingleads);
    $row_pendingleads = mysqli_fetch_assoc($result_pendingleads);
    $pendingleads = $row_pendingleads['pendingleads'];

    $query_Hot = "SELECT COUNT(DISTINCT ID) AS Hot 
FROM personalinformation 
WHERE LeadStatus = 'Hot Lead' 
  AND StepReached <> 'Disbursed' 
  AND LoanStatus <> 'Rejected';";
    $result_Hot = mysqli_query($conn, $query_Hot);
    $row_Hot = mysqli_fetch_assoc($result_Hot);
    $Hot = $row_Hot['Hot'];

    $query_Cold = "SELECT COUNT(DISTINCT ID) AS Cold FROM personalinformation where LeadStatus='Cold Lead'";
    $result_Cold = mysqli_query($conn, $query_Cold);
    $row_Cold = mysqli_fetch_assoc($result_Cold);
    $Cold = $row_Cold['Cold'];

    $query_Rejected = "SELECT COUNT(DISTINCT ID) AS Rejected FROM personalinformation where LeadStatus='Rejection' OR LoanStatus='Rejected'";
    $result_Rejected = mysqli_query($conn, $query_Rejected);
    $row_Rejected = mysqli_fetch_assoc($result_Rejected);
    $Rejected = $row_Rejected['Rejected'];
    //code for digital pending verifications 
    $query_hot_leads = "SELECT ID FROM personalinformation WHERE LeadStatus = 'Hot Lead' AND LoanStatus!='Rejected'";
    $result_hot_leads = mysqli_query($conn, $query_hot_leads);

    $hot_leads_ids = [];
    while ($row_hot_lead = mysqli_fetch_assoc($result_hot_leads)) {
        $hot_leads_ids[] = $row_hot_lead['ID'];
    }

    $pendingdigitalverifications = 0;

    if (!empty($hot_leads_ids)) {
        $hot_leads_ids_str = implode(',', $hot_leads_ids);

        // Query to count pending digital verifications
        $query_pendingdigitalverifications = "SELECT COUNT(DISTINCT LeadID) AS pendingdigitalverifications
                                                  FROM documentcollection
                                                  WHERE LeadID IN ($hot_leads_ids_str) AND (
                                                      Status1 IN ('Rejected', 'Pending') OR
                                                      Status2 IN ('Rejected', 'Pending') OR
                                                      Status3 IN ('Rejected', 'Pending') OR
                                                      Status4 IN ('Rejected', 'Pending') OR
                                                      Status5 IN ('Rejected', 'Pending') OR
                                                      Status6 IN ('Rejected', 'Pending') OR
                                                      Status7 IN ('Rejected', 'Pending') OR
                                                      Status8 IN ('Rejected', 'Pending') OR
                                                      Status9 IN ('Rejected', 'Pending') OR
                                                      Status10 IN ('Rejected', 'Pending') OR
                                                      Status11 IN ('Rejected', 'Pending') OR
                                                      Status12 IN ('Rejected', 'Pending') OR
                                                      Status13 IN ('Rejected', 'Pending')
                                                  )";

        $result_pendingdigitalverifications = mysqli_query($conn, $query_pendingdigitalverifications);
        $row_pendingdigitalverifications = mysqli_fetch_assoc($result_pendingdigitalverifications);
        $pendingdigitalverifications = $row_pendingdigitalverifications['pendingdigitalverifications'];

        // Query to count hot leads with no documentcollection entry
        $query_no_documentcollection = "SELECT COUNT(*) AS nodocuments
                                            FROM personalinformation
                                            WHERE LeadStatus = 'Hot Lead' AND LoanStatus<>'Disbursed' AND ID NOT IN (
                                                SELECT DISTINCT LeadID FROM documentcollection
                                            )";

        $result_no_documentcollection = mysqli_query($conn, $query_no_documentcollection);
        $row_no_documentcollection = mysqli_fetch_assoc($result_no_documentcollection);
        $no_documents = $row_no_documentcollection['nodocuments'];

        // Sum the counts
        $total_pendingdigitalverifications = $pendingdigitalverifications + $no_documents;
    } else {
        $total_pendingdigitalverifications = 0;
    }
    // end
    $query_pendingphyiscalverifications = "SELECT COUNT(DISTINCT pi.ID) as pendingphyiscalverifications, pi.LeadStatus
        FROM personalinformation pi
        LEFT JOIN VerificationForms vf ON pi.ID = vf.leadID
        WHERE vf.leadID IS NULL
        AND pi.LeadStatus = 'Hot Lead'
        AND pi.LoanStatus <> 'Rejected'
        GROUP BY pi.LeadStatus;
    ";
    $result_pendingphyiscalverifications = mysqli_query($conn, $query_pendingphyiscalverifications);
    $row_pendingphyiscalverifications = mysqli_fetch_assoc($result_pendingphyiscalverifications);
    $pendingphyiscalverifications = $row_pendingphyiscalverifications['pendingphyiscalverifications'];

    $query_pendingphyiscalverifications1 = "SELECT COUNT(DISTINCT pi.ID) as pendingphyiscalverifications1, pi.LeadStatus
FROM personalinformation pi
LEFT JOIN VerificationForms vf ON pi.ID = vf.leadID
WHERE (vf.leadID IS NULL 
       OR vf.verificationStatus_Home IN ('Pending', 'Rejected') 
       OR vf.verificationStatus_Business IN ('Pending', 'Rejected'))
  AND pi.LeadStatus = 'Hot Lead'
  AND pi.LoanStatus <> 'Rejected'
GROUP BY pi.LeadStatus;
        ";
    $result_pendingphyiscalverifications1 = mysqli_query($conn, $query_pendingphyiscalverifications1);
    $row_pendingphyiscalverifications1 = mysqli_fetch_assoc($result_pendingphyiscalverifications1);
    $pendingphyiscalverifications1 = $row_pendingphyiscalverifications1['pendingphyiscalverifications1'];




    $leadIDsQuery = "SELECT pi.ID as LeadID
                 FROM personalinformation pi
                 WHERE pi.LeadStatus='Hot Lead' AND pi.LoanPurpose='LAP' AND LoanStatus NOT IN ('Rejected','Disbursed')";
    $leadIDsResult = $conn->query($leadIDsQuery);

    $totalPendingLegalVerifications = 0;

    if ($leadIDsResult->num_rows > 0) {
        while ($row = $leadIDsResult->fetch_assoc()) {
            $leadID = $row["LeadID"];

            // Check if leadID is present in legal_evaluations table
            $legalQuery = "SELECT * FROM legal_evaluations WHERE lead_id = $leadID";
            $legalResult = $conn->query($legalQuery);

            if ($legalResult->num_rows > 0) {
                $legalRow = $legalResult->fetch_assoc();

                // Check statuses of documents
                if (
                    $legalRow["registree_status"] == 'Pending' || $legalRow["registree_status"] == 'Rejected' ||
                    $legalRow["fard_status"] == 'Pending' || $legalRow["fard_status"] == 'Rejected' ||
                    $legalRow["noc_status"] == 'Pending' || $legalRow["noc_status"] == 'Rejected' ||
                    $legalRow["old_registree_status"] == 'Pending' || $legalRow["old_registree_status"] == 'Rejected' ||
                    $legalRow["video_status"] == 'Pending' || $legalRow["video_status"] == 'Rejected'
                ) {
                    $totalPendingLegalVerifications++;
                }
            } else {
                // Increment for leads without any entry in legal_evaluations table
                $totalPendingLegalVerifications++;
            }
        }
    }

    // $query_approved_loans = "SELECT COUNT(DISTINCT LeadID) AS approved_loans FROM approval_information WHERE isDisbursed=1";
    // $result_approved_loans = mysqli_query($conn, $query_approved_loans);
    // $row_approved_loans = mysqli_fetch_assoc($result_approved_loans);
    // $approved_loans = $row_approved_loans['approved_loans'];

    $query_pendingrecovery = "SELECT COUNT(DISTINCT LeadID) AS pendingrecovery FROM emi_schedule WHERE overdue_days >= 10";
    $result_pendingrecovery = mysqli_query($conn, $query_pendingrecovery);
    $row_pendingrecovery = mysqli_fetch_assoc($result_pendingrecovery);
    $pendingrecovery = $row_pendingrecovery['pendingrecovery'];

    $query_dueemi = "SELECT COUNT(DISTINCT LeadID) AS dueemi FROM emi_schedule WHERE overdue_days > 5";
    $result_dueemi = mysqli_query($conn, $query_dueemi);
    $row_dueemi = mysqli_fetch_assoc($result_dueemi);
    $dueemi = $row_dueemi['dueemi'];


    $query_foreclosures = "SELECT COUNT(DISTINCT LeadID) AS foreclosures FROM foreclosure_requests WHERE Status='Pending'";
    $result_foreclosures = mysqli_query($conn, $query_foreclosures);
    $row_foreclosures = mysqli_fetch_assoc($result_foreclosures);
    $foreclosures = $row_foreclosures['foreclosures'];

    $query_closedloans = "SELECT COUNT(DISTINCT ID) AS closedloans FROM personalinformation WHERE LoanStatus=''";
    $result_closedloans = mysqli_query($conn, $query_closedloans);
    $row_closedloans = mysqli_fetch_assoc($result_closedloans);
    $closedloans = $row_closedloans['closedloans'];

    //     $query_pendingApprovals = "SELECT COUNT(p.ID) AS pendingApprovals
    // FROM personalinformation p
    // LEFT JOIN VerificationForms v ON p.ID = v.leadID
    // LEFT JOIN legal_evaluations le ON p.ID = le.lead_id
    // LEFT JOIN evaluation_reports er ON p.ID = er.lead_id
    // WHERE p.ID NOT IN (SELECT LeadID FROM approval_information)
    // AND (
    //     (v.verificationStatus_Home = 'Approved'
    //      AND v.verificationStatus_Business = 'Approved')
    //     AND (
    //         le.evaluation_needed = 0 
    //         OR (
    //             le.evaluation_needed = 1 
    //             AND (
    //                 SELECT status 
    //                 FROM evaluation_reports 
    //                 WHERE lead_id = p.ID 
    //                 ORDER BY created_at DESC 
    //                 LIMIT 1
    //             ) = 'Approved'
    //         )
    //     )
    //     AND (
    //         (le.registree_status = 'Approved'
    //          AND le.fard_status = 'Approved'
    //          AND le.noc_status = 'Approved'
    //          AND le.old_registree_status = 'Approved')
    //         OR le.registree_status IS NULL
    //     )
    // );
    // ";

    $query_pendingApprovals = "
SELECT COUNT(*) AS pendingApprovals
FROM approval_information ai
WHERE ai.isDisbursed <> 1
AND ai.IsApproved = 1
AND NOT EXISTS (
    SELECT 1 
    FROM approval_information ai_rejection 
    WHERE ai_rejection.LeadID = ai.LeadID
    AND ai_rejection.IsApproved = 0
)";

    $result_pendingApprovals = mysqli_query($conn, $query_pendingApprovals);
    $row_pendingApprovals = mysqli_fetch_assoc($result_pendingApprovals);
    $pendingApprovals = $row_pendingApprovals['pendingApprovals'];
}

?>
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



<!-- ============================================================== -->
<!-- Start right Content here -->
<!-- ============================================================== -->
<div class="main-content">

    <!---mandeep css-->
    <style>
        .footer {
            bottom: -99px;
        }

        body[data-sidebar-size=sm] .vertical-menu #sidebar-menu>ul>li {
            background-color: white !important;
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
                <!--approved Loans-->
                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-h-100">
                        <!-- card body -->
                        <div class="card-body" style="background: #bee6ef;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Approved Loans</span>
                                    <h4 class="mb-3">
                                        <a href="approved_details.php" style="text-decoration: none; color: inherit;">
                                            <span class="counter-value" data-target="354.5"><?php echo $approved_loans; ?></span>
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
                <!--Pending Approvals-->
                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-h-100">
                        <!-- card body -->
                        <div class="card-body" style="background: #a5d9ac;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Pending Disbursals</span>
                                    <h4 class="mb-3">
                                        <a href="approved_loans.php" style="text-decoration: none; color: inherit;">
                                            <span class="counter-value" data-target="354.5"><?php echo $pendingApprovals; ?></span>
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
                <!--Total Borrowers-->
                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-h-100">
                        <!-- card body -->
                        <div class="card-body" style="background: #becdef;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Borrowers</span>
                                    <h4 class="mb-3">
                                        <a href="borrower_details.php" style="text-decoration: none; color: inherit;">
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

                                    <h4 class="mb-3">
                                        <a href="pendingleads_details.php" style="text-decoration: none; color: inherit;">
                                            <span class="counter-value" data-target="7.54"><?php echo $pendingleads; ?></span>
                                        </a>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0 text-end dash-widget">
                                    <div id="mini-chart3" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                </div>
                            </div>
                        </div><!-- end card body 3-->
                    </div><!-- end card -->
                </div>
                <!-- end col -->
                <!--Hot Leads-->
                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-h-100">
                        <!-- card body -->
                        <div class="card-body" style="background: #c9e7db;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Hot Leads</span>
                                    <h4 class="mb-3">
                                        <a href="hotlead_details.php" style="text-decoration: none; color: inherit;">
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
                                        <a href="coldlead_details.php" style="text-decoration: none; color: inherit;">
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
                                        <a href="rejected_details.php" style="text-decoration: none; color: inherit;">
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

                <!--Pending Digital Verifications-->
                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-h-100">
                        <!-- card body -->
                        <div class="card-body" style="background: #87c7e5;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Pending Digital Verifications</span>
                                    <h4 class="mb-3">
                                        <a href="pendingdigitalverifications.php" style="text-decoration: none; color: inherit;">
                                            <span class="counter-value" data-target="7.54"><?php echo $total_pendingdigitalverifications; ?></span>
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

                <!--Pending Physical Verifications-->
                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-h-100">
                        <!-- card body -->
                        <div class="card-body" style="background: #f1f5d5;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Pending Physical Verifications</span>
                                    <h4 class="mb-3">
                                        <a href="pendingphysicalverifications.php" style="text-decoration: none; color: inherit;">
                                            <span class="counter-value" data-target="7.54"><?php echo $pendingphyiscalverifications1; ?></span>
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

                <!--Pending Legal Verifications-->
                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-h-100">
                        <!-- card body -->
                        <div class="card-body" style="background: #e4dfed;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Pending Legal Verifications</span>
                                    <h4 class="mb-3">
                                        <a href="pending_legal_verifications.php" style="text-decoration: none; color: inherit;">
                                            <span class="counter-value" data-target="7.54"><?php echo $totalPendingLegalVerifications; ?></span>
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



                <!--Pending For Recovery-->
                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-h-100">
                        <!-- card body -->
                        <div class="card-body" style="background: #f3dcc5;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Pending For Recovery</span>
                                    <h4 class="mb-3">
                                        <a href="totalrecoveries.php" style="text-decoration: none; color: inherit;">
                                            <span class="counter-value" data-target="7.54"><?php echo $pendingrecovery; ?></span>
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
                <!--Failed EMI's-->
                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-h-100">
                        <!-- card body -->
                        <div class="card-body" style="background: #d37b7b;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Due EMI's</span>
                                    <h4 class="mb-3">
                                        <a href="dashboardapproved_loans.php" style="text-decoration: none; color: inherit;">
                                            <span class="counter-value" data-target="7.54"><?php echo $dueemi; ?></span>
                                        </a>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0 text-end dash-widget">
                                    <div id="mini-chart3" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"></div>
                                </div>
                            </div>
                        </div><!-- end card body 3-->
                    </div><!-- end card -->
                </div>
                <!-- end col -->

                <!--Foreclosures-->
                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-h-100">
                        <!-- card body -->
                        <div class="card-body" style="background: #87c7e5;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Foreclosure Requests</span>
                                    <h4 class="mb-3">
                                        <a href="admin_foreclosure_requests.php" style="text-decoration: none; color: inherit;">
                                            <span class="counter-value" data-target="7.54"><?php echo $foreclosures; ?></span>
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
                    <script>
                        document.write(new Date().getFullYear())
                    </script> © Loan.
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