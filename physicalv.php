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
// $conn->query("SET time_zone = '+05:30'");

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
                <form method="POST" action="verification_data.php" id="verificationForm" enctype="multipart/form-data">
                    <input type="hidden" name="leadID" id="leadID" value="<?php echo htmlspecialchars($leadID); ?>">
                    <h4>Name: <?php echo htmlspecialchars($personName); ?></h4>
                    <h4>Lead ID: <?php echo htmlspecialchars($leadID); ?></h4>

                    <div class="card my-4">
                        <div class="card-body">
                            <h3 class="card-title">Home Verification</h3>
                            <input type="hidden" name="verificationType_Home" value="Home">

                            <div class="form-group">
                                <label for="verifierName_Home">Verifier Name:</label>
                                <input type="text" class="form-control" name="verifierName_Home" id="verifierName_Home">
                            </div>

                            <div class="form-group">
                                <label for="verificationStatus_Home">Verification Status:</label>
                                <select class="form-control" name="verificationStatus_Home" id="verificationStatus_Home">
                                    <option value="Pending">Pending</option>
                                    <option value="Completed">Completed</option>
                                    <!--<option value="Rejected">Rejected</option>-->
                                </select>
                            </div>

                            <label for="electricity_bill_home">Electricity Bill (Home):</label>
                            <input type="file" id="electricity_bill_home" name="electricity_bill_home"><br>

                            <label for="electricity_meter_home">Electricity Meter (Home):</label>
                            <input type="file" id="electricity_meter_home" name="electricity_meter_home"><br>

                            <div class="upload__box">
                                <!-- Image Upload Section -->
                                <div class="upload__btn-box">
                                    <label class="upload__btn">
                                        <p>Upload Images</p>
                                        <input type="file" name="home_images[]" multiple data-max_length="20" class="upload__inputfile" accept="image/*">
                                    </label>
                                </div>
                                <div class="upload__img-wrap"></div>
                            </div>

                            <div class="upload__box">
                                <!-- Video Upload Section -->
                                <div class="upload__btn-box">
                                    <label class="upload__btn">
                                        <p>Upload Videos</p>
                                        <input type="file" name="home_videos[]" multiple data-max_length="20" class="upload__inputfile" accept="video/*">
                                    </label>
                                </div>
                                <div class="upload__img-wrap"></div>
                            </div>



                            <!--RADIO BUTTONS FOR RESIDENCE STATUS OBSERVATION-->
                            <label>Home Residence Status:</label><br>
                            <input type="radio" id="option1" name="choice" value="self_owned">
                            <label for="option1">Self Owned</label><br>

                            <input type="radio" id="option2" name="choice" value="rented">
                            <label for="option2">Rented</label><br>
                            <!---->



                            <div class="form-group">
                                <label for="verificationNotes_Home">Remarks (Home):</label>
                                <textarea class="form-control" name="verificationNotes_Home" id="verificationNotes_Home" rows="4" cols="50"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="verificationGeolocation_Home">Geolocation:</label>
                                <input type="text" class="form-control" name="verificationGeolocation_Home" id="verificationGeolocation_Home" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="card my-4">
                        <div class="card-body">
                            <h3 class="card-title">Business Verification</h3>
                            <input type="hidden" name="verificationType_Business" value="Business">

                            <div class="form-group">
                                <label for="verifierName_Business">Verifier Name:</label>
                                <input type="text" class="form-control" name="verifierName_Business" id="verifierName_Business">
                            </div>

                            <div class="form-group">
                                <label for="verificationStatus_Business">Verification Status:</label>
                                <select class="form-control" name="verificationStatus_Business" id="verificationStatus_Business">
                                    <option value="Pending">Pending</option>
                                    <option value="Completed">Completed</option>
                                    <!--<option value="Rejected">Rejected</option>-->
                                </select>
                            </div>

                            <label for="electricity_bill_business">Electricity Bill (Business):</label>
                            <input type="file" id="electricity_bill_business" name="electricity_bill_business"><br>

                            <label for="electricity_meter_business">Electricity Meter (Business):</label>
                            <input type="file" id="electricity_meter_business" name="electricity_meter_business"><br>

                            <div class="upload__box">
                                <!-- Image Upload Section -->
                                <div class="upload__btn-box">
                                    <label class="upload__btn">
                                        <p>Upload Images</p>
                                        <input type="file" name="business_images[]" multiple data-max_length="20" class="upload__inputfile" accept="image/*">
                                    </label>
                                </div>
                                <div class="upload__img-wrap"></div>
                            </div>

                            <div class="upload__box">
                                <!-- Video Upload Section -->
                                <div class="upload__btn-box">
                                    <label class="upload__btn">
                                        <p>Upload Videos</p>
                                        <input type="file" name="business_videos[]" multiple data-max_length="20" class="upload__inputfile" accept="video/*">
                                    </label>
                                </div>
                                <div class="upload__img-wrap"></div>
                            </div>


                            <!--RADIO BUTTONS FOR BUSINESS PLACE STATUS OBSERVATION-->
                            <label>Business Place Status:</label><br>
                            <input type="radio" id="option3" name="choice2" value="self_owned">
                            <label for="option3">Self Owned</label><br>

                            <input type="radio" id="option4" name="choice2" value="rented">
                            <label for="option4">Rented</label><br>
                            <!---->


                            <div class="form-group">
                                <label for="businessVerificationNotes">Remarks (Business):</label>
                                <textarea class="form-control" name="businessVerificationNotes" id="businessVerificationNotes" rows="4" cols="50"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="verificationGeolocation_Business">Geolocation:</label>
                                <input type="text" class="form-control" name="verificationGeolocation_Business" id="verificationGeolocation_Business" readonly>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
        <?php
            } else {
                echo '<div class="alert alert-danger">No person found for ID ' . $leadID . '</div>';
            }
        } else {
            echo '<div class="alert alert-danger">No ID parameter specified</div>';
        }
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.10.2/dist/ffmpeg.min.js"></script>


    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                console.log("Geolocation is not supported by this browser.");
            }
        }

        function showPosition(position) {
            document.getElementById("verificationGeolocation_Home").value = "Latitude: " + position.coords.latitude + " Longitude: " + position.coords.longitude;
            document.getElementById("verificationGeolocation_Business").value = "Latitude: " + position.coords.latitude + " Longitude: " + position.coords.longitude;
        }

        getLocation();
    </script>
    <script>
        $(document).ready(function() {
            $('.upload__inputfile').on('change', function(e) {
                var imgWrap = $(this).closest('.upload__box').find('.upload__img-wrap');
                var maxLength = parseInt($(this).attr('data-max_length'));
                var files = e.target.files;
                var filesArr = Array.prototype.slice.call(files);
                filesArr.forEach(function(f) {
                    if (!f.type.match('image.*') && !f.type.match('video.*')) {
                        return;
                    }
                    if (imgWrap.find('.upload__img-box').length >= maxLength) {
                        return;
                    }
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var html;
                        if (f.type.match('image.*')) {
                            html = "<div class='upload__img-box'><div style='background-image: url(" + e.target.result + ")' data-file='" + f.name + "' class='img-bg'><div class='upload__img-close'></div></div></div>";
                        } else if (f.type.match('video.*')) {
                            html = "<div class='upload__img-box'><div data-file='" + f.name + "' class='img-bg'><video width='100%' controls><source src='" + e.target.result + "' type='" + f.type + "'></video><div class='upload__img-close'></div></div></div>";
                        }
                        imgWrap.append(html);
                    }
                    reader.readAsDataURL(f);
                });
            });

            $('#uploadForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log(response);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });

            $('body').on('click', ".upload__img-close", function(e) {
                $(this).parent().parent().remove();
            });
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>