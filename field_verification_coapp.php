<?php
session_start();
if (!isset($_SESSION['username']) || !$_SESSION['role']) {
    // Redirect to the login page or another appropriate page
    header("Location: index.php");
    exit();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

date_default_timezone_set('Asia/Kolkata');

// Set the time zone to Asia/Kolkata in MySQL
$conn->query("SET time_zone = '+05:30'");

$username = $_SESSION['username'];
$role = $_SESSION['role'];

$redirecturl1 = '';
$redirecturl2 = '';
if ($role == 'admin') {
    $redirecturl1 = 'dashboard.php';
    $redirecturl2 = 'verify.php';
} elseif ($role == 'branchmanager') {
    $redirecturl1 = 'branchmanager.php';
    $redirecturl2 = 'digitalverificationsbm.php';
} elseif ($role == 'verifier') {
    $redirecturl1 = 'dashboardverifier.php';
    $redirecturl2 = 'verify.php';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Form</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #007bff;
            color: #fff;
            border-radius: 10px 10px 0 0;
        }

        .card-title {
            margin-bottom: 0;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .form-control {
            border-radius: 5px;
        }

        .form-group label {
            font-weight: bold;
        }

        .upload__box {
            padding: 40px;
        }

        .upload__inputfile {
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            position: absolute;
            z-index: -1;
        }

        .upload__btn {
            display: inline-block;
            font-weight: 600;
            color: #fff;
            text-align: center;
            min-width: 116px;
            padding: 5px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid;
            background-color: #4045ba;
            border-color: #4045ba;
            border-radius: 10px;
            line-height: 26px;
            font-size: 14px;
        }

        .upload__btn:hover {
            background-color: unset;
            color: #4045ba;
            transition: all 0.3s ease;
        }

        .upload__btn-box {
            margin-bottom: 10px;
        }

        .upload__img-wrap {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }

        .upload__img-box {
            width: 200px;
            padding: 0 10px;
            margin-bottom: 12px;
        }

        .upload__img-close {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: rgba(0, 0, 0, 0.5);
            position: absolute;
            top: 10px;
            right: 10px;
            text-align: center;
            line-height: 24px;
            z-index: 1;
            cursor: pointer;
        }

        .upload__img-close:after {
            content: '\2716';
            font-size: 14px;
            color: white;
        }

        .img-bg {
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            position: relative;
            padding-bottom: 100%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo $redirecturl1; ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo $redirecturl2; ?>">Verifications</a></li>
                            <li class="breadcrumb-item active">Field Verifications</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <h1 class="my-4">Field Verification Form</h1>

        <?php
        if (isset($_GET['id'])) {
            $leadID = $_GET['id'];
            $personQuery = $conn->query("SELECT FullName FROM personalinformation WHERE ID = $leadID");

            if ($personQuery && $personQuery->num_rows > 0) {
                $personData = $personQuery->fetch_assoc();
                $personName = $personData['FullName'];
        ?>
                <form method="POST" action="field_verification_coapp_php.php" id="verificationForm" enctype="multipart/form-data">
                    <input type="hidden" name="leadID" id="leadID" value="<?php echo htmlspecialchars($leadID); ?>">
                    <h4>Name: <?php echo htmlspecialchars($personName); ?></h4>
                    <h4>Lead ID: <?php echo htmlspecialchars($leadID); ?></h4>

                    <div class="card my-4">
                        <div class="card-body">
                            <h3 class="card-title">Home Verification(CO-APP)</h3>
                            <input type="hidden" name="verificationType_Home_COAPP" value="Home_COAPP">

                            <div class="form-group">
                                <label for="verifierName_Home_COAPP">Verifier Name (CO-APP):</label>
                                <input type="text" class="form-control" name="verifierName_Home_COAPP" id="verifierName_Home_COAPP">
                            </div>

                            <div class="form-group">
                                <label for="verificationStatus_Home_COAPP">Verification Status (CO-APP):</label>
                                <select class="form-control" name="verificationStatus_Home_COAPP" id="verificationStatus_Home_COAPP">
                                    <option value="Pending">Pending</option>
                                    <option value="Completed">Completed</option>
                                    <!--<option value="Rejected">Rejected</option>-->
                                </select>
                            </div>

                            <label for="electricity_bill_home_COAPP">Electricity Bill (Home(CO-APP)):</label>
                            <input type="file" id="electricity_bill_home_COAPP" name="electricity_bill_home_COAPP"><br>

                            <label for="electricity_meter_home_COAPP">Electricity Meter (Home(CO-APP)):</label>
                            <input type="file" id="electricity_meter_home_COAPP" name="electricity_meter_home_COAPP"><br>

                            <div class="upload__box">
    <!-- Upload Images -->
    <div class="upload__btn-box">
        <label class="upload__btn">
            <p>Upload Images</p>
            <input type="file" name="home_images_COAPP[]" id="home_images_COAPP" multiple data-max_length="20" class="upload__inputfile" accept="image/*">
        </label>
    </div>
    <div class="upload__img-wrap" id="img_wrap_COAPP"></div>

    <!-- Upload Videos -->
    <div class="upload__btn-box">
        <label class="upload__btn">
            <p>Upload Videos</p>
            <input type="file" name="home_videos_COAPP[]" id="home_videos_COAPP" multiple data-max_length="20" class="upload__inputfile" accept="video/*">
        </label>
    </div>
    <div class="upload__video-wrap" id="video_wrap_COAPP"></div>
</div>


                            <!--RADIO BUTTONS FOR RESIDENCE STATUS OBSERVATION-->
                            <label>Home Residence Status (CO-APP):</label><br>
                            <input type="radio" id="option1_COAPP" name="choice_COAPP" value="self_owned">
                            <label for="option1_COAPP">Self Owned</label><br>
                            <input type="radio" id="option2_COAPP" name="choice_COAPP" value="rented">
                            <label for="option2_COAPP">Rented</label><br>
                            <!---->

                            <div class="form-group">
                                <label for="verificationNotes_Home_COAPP">Remarks (Home(CO-APP)):</label>
                                <textarea class="form-control" name="verificationNotes_Home_COAPP" id="verificationNotes_Home_COAPP" rows="4" cols="50"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="verificationGeolocation_Home_COAPP">Geolocation:</label>
                                <input type="text" class="form-control" name="verificationGeolocation_Home_COAPP" id="verificationGeolocation_Home_COAPP" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="card my-4">
                        <div class="card-body">
                            <h3 class="card-title">Business Verification</h3>
                            <input type="hidden" name="verificationType_Business_COAPP" value="Business_COAPP">

                            <div class="form-group">
                                <label for="verifierName_Business_COAPP">Verifier Name (CO-APP):</label>
                                <input type="text" class="form-control" name="verifierName_Business_COAPP" id="verifierName_Business_COAPP">
                            </div>

                            <div class="form-group">
                                <label for="verificationStatus_Business_COAPP">Verification Status (CO-APP):</label>
                                <select class="form-control" name="verificationStatus_Business_COAPP" id="verificationStatus_Business_COAPP">
                                    <option value="Pending">Pending</option>
                                    <option value="Completed">Completed</option>
                                    <!--<option value="Rejected">Rejected</option>-->
                                </select>
                            </div>

                            <label for="electricity_bill_business_COAPP">Electricity Bill (Business) (CO-APP):</label>
                            <input type="file" id="electricity_bill_business_COAPP" name="electricity_bill_business_COAPP"><br>

                            <label for="electricity_meter_business_COAPP">Electricity Meter (Business) (CO-APP):</label>
                            <input type="file" id="electricity_meter_business_COAPP" name="electricity_meter_business_COAPP"><br>

                            <!-- Upload Images -->
                            <div class="upload__box">
                                <div class="upload__btn-box">
                                    <label class="upload__btn">
                                        <p>Upload Images</p>
                                        <input type="file" name="business_images_COAPP[]" multiple data-max_length="20" class="upload__inputfile" accept="image/*">
                                    </label>
                                </div>
                                <div class="upload__img-wrap"></div>
                            </div>

                            <!-- Upload Videos -->
                            <div class="upload__box">
                                <div class="upload__btn-box">
                                    <label class="upload__btn">
                                        <p>Upload Videos</p>
                                        <input type="file" name="business_videos_COAPP[]" multiple data-max_length="20" class="upload__inputfile" accept="video/*">
                                    </label>
                                </div>
                                <div class="upload__video-wrap"></div>
                            </div>


                            <!--RADIO BUTTONS FOR BUSINESS PLACE STATUS OBSERVATION-->
                            <label>Business Place Status (CO-APP):</label><br>
                            <input type="radio" id="option3_COAPP" name="choice2_COAPP" value="self_owned">
                            <label for="option3_COAPP">Self Owned</label><br>
                            <input type="radio" id="option4_COAPP" name="choice2_COAPP" value="rented">
                            <label for="option4_COAPP">Rented</label><br>
                            <!---->

                            <div class="form-group">
                                <label for="businessVerificationNotes_COAPP">Remarks (Business) (CO-APP):</label>
                                <textarea class="form-control" name="businessVerificationNotes_COAPP" id="businessVerificationNotes_COAPP" rows="4" cols="50"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="verificationGeolocation_Business_COAPP">Geolocation:</label>
                                <input type="text" class="form-control" name="verificationGeolocation_Business_COAPP" id="verificationGeolocation_Business_COAPP" readonly>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
        <?php
            } else {
                echo '<div class="alert alert-danger">No person found for ID ' . htmlspecialchars($leadID) . '</div>';
            }
        } else {
            echo '<div class="alert alert-danger">No ID parameter specified</div>';
        }
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                console.log("Geolocation is not supported by this browser.");
            }
        }

        function showPosition(position) {
            document.getElementById("verificationGeolocation_Home_COAPP").value = "Latitude: " + position.coords.latitude + " Longitude: " + position.coords.longitude;
            document.getElementById("verificationGeolocation_Business_COAPP").value = "Latitude: " + position.coords.latitude + " Longitude: " + position.coords.longitude;
        }

        getLocation();
    </script>

    <script>
        $(document).ready(function() {
            // Function to handle file input changes and preview images/videos
            function handleFileInputChange(e) {
                var input = $(e.target);
                var imgWrap = input.closest('.upload__box').find('.upload__img-wrap');
                var maxLength = parseInt(input.attr('data-max_length'));
                var files = e.target.files;
                var filesArr = Array.prototype.slice.call(files);

                filesArr.forEach(function(f) {
                    if (!f.type.match('image.*') && !f.type.match('video.*')) {
                        return; // Skip non-image/video files
                    }
                    if (imgWrap.find('.upload__img-box').length >= maxLength) {
                        return; // Limit exceeded
                    }
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var html = "<div class='upload__img-box'><div style='background-image: url(" + e.target.result + ")' data-file='" + f.name + "' class='img-bg'><div class='upload__img-close'></div></div>";
                        // For video files, add a video tag instead of background image
                        if (f.type.match('video.*')) {
                            html = "<div class='upload__img-box'><video controls src='" + e.target.result + "' style='width: 100%; height: auto;'></video><div class='upload__img-close'></div></div>";
                        }
                        imgWrap.append(html);
                    }
                    reader.readAsDataURL(f);
                });
            }

            // Bind change event to file inputs
            $('input.upload__inputfile').on('change', handleFileInputChange);

            // Handle remove file from preview
            $('body').on('click', '.upload__img-close', function() {
                $(this).closest('.upload__img-box').remove();
            });
        });
    </script>


</body>

</html>

<?php
$conn->close();
?>