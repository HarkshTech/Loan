<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Loan Application Form</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">Loan Application Form</h2>
    <form id="myForm" method="post" enctype="multipart/form-data" action="insert_data.php">

        <div class="card">
            <div class="card-body">
                <!--1st row-->
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="sr_no">Sr. No:</label>
                        <input type="text" class="form-control" id="sr_no" name="sr_no">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="date">Date:</label>
                        <input type="date" class="form-control" id="date" name="date">
                    </div>
                   <!-- Updated photo upload field with accept attribute and max file size -->
<div class="form-group col-md-4">
    <label for="photo">Upload Photo:</label>
    <div class="custom-file">
        <input type="file" class="custom-file-input" id="photo" name="photo" accept="image/*" onchange="previewImage(event)">
        <label class="custom-file-label" for="photo">Choose file</label>
        <button type="button" class="btn btn-secondary btn-sm" onclick="clearFile()">Clear</button>
    </div>
    <small class="form-text text-muted">Select Images (.jpg, .png, .bmp files)</small>
</div>
<div id="imagePreview" class="col-md-4"></div>

<script>
// Function to preview selected image
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById('imagePreview');
        output.innerHTML = '<img src="' + reader.result + '" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">';
    };
    reader.readAsDataURL(event.target.files[0]);
}

// Function to clear selected file
function clearFile() {
    var fileInput = document.getElementById('photo');
    fileInput.value = ''; // Clear input value
    var output = document.getElementById('imagePreview');
    output.innerHTML = ''; // Clear preview
}
</script>

  
                </div>

                <!--2nd row-->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="amount_reqd">Amount Required:</label>
                        <input type="text" class="form-control" id="amount_reqd" name="amount_reqd">
                    </div>
                    <div class="form-group col-md-6">
    <label for="case_type">Type of Case:</label>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="case_type" id="pl" value="PL">
        <label class="form-check-label" for="pl">PL</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="case_type" id="lap" value="LAP">
        <label class="form-check-label" for="lap">LAP</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="case_type" id="home" value="Home">
        <label class="form-check-label" for="home">Home</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="case_type" id="gold" value="Gold">
        <label class="form-check-label" for="gold">Gold</label>
    </div>
    <!-- Add other options similarly -->
</div>

                </div>

                <!--3rd row-->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="name">Name:</label>
                        <input type="text" class="form-control" id="name" name="name">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="ref_by">Referred by:</label>
                        <input type="text" class="form-control" id="ref_by" name="ref_by">
                    </div>
                </div>

                <!--4th row-->
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="current_address">Current Address:</label>
                        <input type="text" class="form-control" id="current_address" name="current_address">
                    </div>
                </div>

                <!--5th row-->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="cs_app">CS:APP (CIBIL/EXP/EQI/CRIF):</label>
                        <input type="text" class="form-control" id="cs_app" name="cs_app">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="co_app">CO:APP (CIBIL/EXP/EQI/CRIF):</label>
                        <input type="text" class="form-control" id="co_app" name="co_app">
                    </div>
                </div>

                <!--6th row-->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="app_name">APP. Name:</label>
                        <input type="text" class="form-control" id="app_name" name="app_name">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="co-app">CO-APP. Name:</label>
                        <input type="text" class="form-control" id="co_app" name="co_app">
                    </div>
                </div>
                 <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="details1">Work Details:</label>
                        <input type="text" class="form-control" id="details1" name="details1">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="details1">Work Details:</label>
                        <input type="text" class="form-control" id="details2" name="details2">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="income">MONTHLY INCOME:</label>
                        <input type="text" class="form-control" id="income" name="income">
                    </div>
                  
                      <div class="form-group col-md-6">
                        <label for="income2">MONTHLY INCOME:</label>
                        <input type="text" class="form-control" id="income2" name="income2">
                    </div>
                    </div>
                </div>

                <!--7th row-->
                
                <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="ownership_status">OWNERSHIP STATUS:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ownership_status" id="self2" value="SELF_OWNED">
                            <label class="form-check-label" for="self2">Self Owned</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ownership_status2" id="rented2" value="RENTED">
                            <label class="form-check-label" for="rented2">Rented</label>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="ownership_status2">OWNERSHIP STATUS:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ownership_status2" id="self2" value="SELF_OWNED">
                            <label class="form-check-label" for="self2">Self Owned</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ownership_status2" id="rented2" value="RENTED">
                            <label class="form-check-label" for="rented2">Rented</label>
                        </div>
                    </div>
                </div>

                <!--8th row-->
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label for="relation">RELATION BETWEEN APPLICANT AND CO-APPLICANT:</label>
                        <input type="text" class="form-control" id="relation" name="relation">
                    </div>
                    
                </div>

                <!--9th row-->
               

                <!--10th row-->
                <h4 class="col-md-12">CONTACT INFORMATION:-</h4>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="contact1">1.</label>
                        <input type="text" class="form-control" id="contact1" name="contact1">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="contact2">2.</label>
                        <input type="text" class="form-control" id="contact2" name="contact2">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="contact3">3.</label>
                        <input type="text" class="form-control" id="contact3" name="contact3">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="contact4">4.</label>
                        <input type="text" class="form-control" id="contact4" name="contact4">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="contact5">5.</label>
                        <input type="text" class="form-control" id="contact5" name="contact5">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="contact6">6.</label>
                        <input type="text" class="form-control" id="contact6" name="contact6">
                    </div>
                </div>

                <!--11th row-->
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="risk_assessment">RISK ASSESSMENT:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="risk_assessment" id="low" value="low">
                            <label class="form-check-label" for="low">LOW</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="risk_assessment" id="medium" value="medium">
                            <label class="form-check-label" for="medium">MEDIUM</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="risk_assessment" id="high" value="high">
                            <label class="form-check-label" for="high">HIGH</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <button type="submit" class="btn btn-primary btn-block">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
