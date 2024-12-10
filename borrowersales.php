<?php
    session_start();
    $username=$_SESSION['username'];
    $role=$_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Application</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .-label::after {
                content: " *";
                color: red;
            }
        .container {
            max-width: 1140px !important;
            margin: 50px auto;
        }
        .content {
            margin-left: 250px; /* Same as sidebar width */
            padding: 20px;
        }
        @media (min-width: 992px){
            .container, .container-lg, .container-md, .container-sm {
                 max-width: 650px; 
                 margin-top:10%;
            }
            /* Add this CSS for the  star */
        
    </style>
</head>

<body style="background:white;">

<?php
session_start();

include 'leftsidesales.php';
include 'config.php';

$lastIDQuery = "SELECT MAX(id) as last_id FROM personalinformation";
$result = mysqli_query($conn, $lastIDQuery);
$row = mysqli_fetch_assoc($result);
$lastID = isset($row['last_id']) ? $row['last_id'] + 1 : 1; // If no record, start with 1

// Output the  variables as JavaScript
echo "<script>var lastID = {$lastID};</script>";

?>
    <div class="container">
        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Applicant Form !</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="dashboardsales.php">Dashboard</a></li>
                                            <li class="breadcrumb-item active">Application Form</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
        <h1 class="mb-4">Loan Application Form</h1>
        <form id="loanForm" method="POST" enctype="multipart/form-data" >
      <!-- Personal Information -->
      <fieldset>
        <legend>Personal Information (Applicant Details) </legend>
         <div class="form-row">
          <div class="form-group col-md-4">
             <label for="srno">Sr. No:</label>
          
            <input type="text" class="form-control" id="srno" name="srno" readonly>
          </div>
          <div class="form-group col-md-4">
            <label for="datepicker">Date:</label>
             
            <input type="date" class="form-control" id="datepicker" name="datepicker" readonly>
          </div>
           <div class="form-group col-md-4">
            <label for="caseof" class="-label">Case Of:</label>
             
            <input type="text" class="form-control" id="caseof" name="case_of" required >
          </div>
        </div>
        
        
        
        
        
        <div class="form-row">
          <div class="form-group col-md-4">
            <label for="fullName" class="-label"> Name</label>
            <input type="text" class="form-control" id="fullName" name="fullName" required >
          </div>
          <div class="form-group col-md-4">
            <label for="address" class="-label">Current Address</label>
            <input type="text" class="form-control" id="address" name="address" required >
          </div>
         <div class="form-group col-md-4">
            <label for="phone" class="-label">Phone Number</label>
            <input type="tel" class="form-control" id="phone" name="phone" pattern="^\d{10}$"  required >
          </div>
        </div>
        
        
          <!-- Employment Details -->
      <fieldset>
        <!--<legend>Employment Details</legend>-->
         <div class="form-row">
        <div class="form-group col-md-4">
          <label for="details1" class="-label">Occupation</label>
          <input type="text" class="form-control" id="details1" name="details1" required  >
        </div>
         <div class="form-group col-md-4">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email">
          </div>
          <div class="form-group col-md-2">
            <label for="case" class="-label">Company:- </label>
            <select class="form-control"  id="loancompany" name="loancompany"  required >
                        <option value="">Select...</option>
                        <option value="CIBIL">CIBIL</option>
                        <option value="CRIF">CRIF</option>
                        <option value="EXPERIAN">EXPERIAN</option>
                        <option value="EQUIFAX">EQUIFAX</option>
                        <option value="HIGHMARK">HIGHMARK</option>
            </select>
          </div>
           <div class="form-group col-md-2"   >
              <label for="cs_app" class="-label">CIBIL Score Applicant</label>
               <input type="text" class="form-control" id="cs_app" name="cs_app"  required >
             </div>
          </div>
          
        <div class="form-row">
          <div class="form-group col-md-4">
            <label for="employer1" class="-label">Employer</label>
            <input type="text" class="form-control" id="employer1" name="employer1"  required >
          </div>
          <div class="form-group col-md-4">
            <label for="income1" class="-label">Monthly Income (INR)</label>
            <input type="number" class="form-control" id="income1" name="income1" required >
          </div>
            <div class="form-group col-md-4">
        <label class="-label"> Income Type:</label><br>
         
                    <input type="radio" id="cash" name="incometype" value="cash"  required >
                    <label for="cash">Cash</label>
                    <input type="radio" id="accounts" name="incometype" value="account" required >
                    <label for="accounts">Account</label>
         </div>
        </div>
      </fieldset>
        
        
        <div class="form-row">
       
        </div>
         <div class="form-row">
          <div class="form-group col-md-6">
            <label for="amount" class="-label">Required Loan Amount </label>
            <input type="text" class="form-control" id="loanAmount" name="loanAmount" required  >
          </div>
          <div class="form-group col-md-6">
            <label for="case" class="-label">Loan Type:- </label>
            <select class="form-control"  id="loanPurpose" name="loanPurpose"  required >
                        <option value="">Select...</option>
                        <option value="Gold Loan">Gold Loan</option>
                        <option value="Home Loan">Home Loan</option>
                        <option value="Personal Loan">Personal Loan</option>
                        <option value="LAP">LAP(Loan Against Property) </option>
                    </select>
          </div>
        </div>
        
        
        
      
        <div class="form-row">
          <div class="form-group col-md-12 ">
        <label class="-label">Ownership Status(Applicant) :</label><br>
         
                    <input type="radio" id="co_owned" name="ownership_status" value="self_owned"  required >
                    <label for="co_owned">Self Owned</label>
                    <input type="radio" id="co_rented" name="ownership_status" value="rented" required >
                    <label for="co_rented">Rented</label>
         </div>
        </div>
        
        
        
           <div class="form-row">
          <div class="form-group col-md-6">
            <label for="aadhar1" class="-label"> Aadhar Number (Applicant)</label>
            <input type="text" class="form-control" id="aadhar1" name="aadhar1" required  >
          </div>
          <div class="form-group col-md-6">
            <label for="pan1" class="-label">PAN Number (Applicant)</label>
            <input type="text" class="form-control" id="pan1" name="pan1" required  >
          </div>
        
        </div>
    </fieldset>
    
    <fieldset>
            <legend>CO-Applicant Details</legend>
     
        
        
         <div class="form-row">
         <!--<div class="form-group col-md-6 "   >-->
         <!--  <label for="app_name">App. Name</label>-->
         <!--  <input type="text" class="form-control" id="app_name" name="app_name" >-->
         <!--</div>-->
           <div class="form-group col-md-12 "   >
        <label for="co_app_name" class="-label">CO-Applicant Name:</label>
           <input type="text" class="form-control"   id="co_app_name" name="co_app_name" required  >
         
        </div>
        </div>
        
         
         <div class="form-row">
         <!--<div class="form-group col-md-6 "   >-->
         <!--  <label for="details1">Work Details</label>-->
         <!--  <input type="text" class="form-control" id="details1" name="details1" >-->
         <!--</div>-->
           <div class="form-group col-md-12 "   >
        <label for="details2" class="-label">Work Details</label>
           <input type="text" class="form-control"   id="details2" name="details2" required  >
         
        </div>
        
        
        
        <div class="form-group col-md-6">
            <label for="case" class="-label">Company:- </label>
            <select class="form-control"  id="loancompany2" name="loancompany2"  required >
                        <option value="">Select...</option>
                        <option value="CIBIL">CIBIL</option>
                        <option value="CRIF">CRIF</option>
                        <option value="EXPERIAN">EXPERIAN</option>
                        <option value="EQUIFAX">EQUIFAX</option>
                        <option value="HIGHMARK">HIGHMARK</option>
            </select>
            
          </div>
           <div class="form-group col-md-6 ">
                <label for="co_app" class="-label">CO-Applicant Score </label>
                <input type="text" class="form-control"  id="co_app" name="co_app" required  >
            </div>
        </div>
        
         <div class="form-row">
         <!--<div class="form-group col-md-6 "   >-->
         <!--  <label for="income1">Monthly Income</label>-->
         <!--  <input type="text" class="form-control" id="income1" name="income1" >-->
         <!--</div>-->
           <div class="form-group col-md-12 "   >
        <label for="income2" class="-label">Monthly Income</label>
           <input type="text" class="form-control"   id="income2" name="income2"  required >
         
        </div>
        </div>
        
          <div class="form-row">
        <!-- <div class="form-group col-md-6 "   >-->
        <!--<label>Ownership Status(App.) :</label><br>-->
         
        <!--            <input type="radio" id="co_owned" name="ownership_status" value="self_owned">-->
        <!--            <label for="co_owned">Self Owned</label>-->
        <!--            <input type="radio" id="co_rented" name="ownership_status" value="rented">-->
        <!--            <label for="co_rented">Rented</label>-->
        <!-- </div>-->
           <div class="form-group col-md-6 ">
        <label class="-label">Ownership Status(CO App.) :</label><br>
         
                    <input type="radio" id="co_owned1" name="co_ownership_status" value="self_owned" >
                    <label for="co_owned1">Self Owned</label>
                    <input type="radio" id="co_rented1" name="co_ownership_status" value="rented"  >
                    <label for="co_rented1">Rented</label>
         
        </div>
        </div>
        
        
        
        
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="aadhar2" class="-label"> Aadhar Number (Co-Applicant)</label>
            <input type="text" class="form-control" id="aadhar2" name="aadhar2" required >
          </div>
          <div class="form-group col-md-6">
            <label for="pan2" class="-label">PAN Number (Co-Applicant)</label>
            <input type="text" class="form-control" id="pan2" name="pan2" required >
          </div>
        
        </div>
        
        
        
        
           <div class="form-row">
         <div class="form-group col-md-12 ">
          <label for="relation" class="-label">Relation  between Applicant and Co-applicant</label>
            <input class="form-control"  type="text" id="relation" name="relation" required >
           
         </div>
</div>

      <div class="form-row">
         <div class="form-group col-md-12 "   >
          <label for="reason_of_loan" class="-label">Reason Of Loan:</label>
            <input class="form-control"  type="text" id="reason_of_loan" name="reason_of_loan" required >
           
         </div>
</div>


    
  <div class="form-row">
                <div class="col-md-12 ">
                   <div class="contact-box">
    <h2 class="contact-heading">Contact Information</h2>
    <div class="row">
           <label >1. </label><br>
        <div class="col-md-4">
          
            <label for="name1" class="-label">Name:</label>
            <input class="form-control" type="text" id="name1" name="name1" required  >
        </div>
        <div class="col-md-4">
            <label for="relation1" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation1" name="relation1" required >
        </div>
        <div class="col-md-4">
            <label for="contact1" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact1" name="contact1" required >
        </div>
    </div>
</div>

                 <div class="contact-box">
   
    <div class="row">
           <label>2. </label><br>
        <div class="col-md-4">
          
            <label for="name2" class="-label">Name:</label>
            <input class="form-control" type="text" id="name2" name="name2" required >
        </div>
        <div class="col-md-4">
            <label for="relation2" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation2" name="relation2" required >
        </div>
        <div class="col-md-4">
            <label for="contact2" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact2" name="contact2"  required >
        </div>
    </div>
</div>
                 <div class="contact-box">
    
    <div class="row">
           <label >3. </label><br>
        <div class="col-md-4">
          
            <label for="name3" class="-label">Name:</label>
            <input class="form-control" type="text" id="name3" name="name3" required >
        </div>
        <div class="col-md-4">
            <label for="relation3" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation3" name="relation3" required >
        </div>
        <div class="col-md-4">
            <label for="contact3" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact3" name="contact3" required >
        </div>
    </div>
</div>
                    <div class="contact-box">
    
    <div class="row">
           <label >4. </label><br>
        <div class="col-md-4">
          
            <label for="name4" class="-label">Name:</label>
            <input class="form-control" type="text" id="name4" name="name4" required >
        </div>
        <div class="col-md-4">
            <label for="relation4" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation4" name="relation4" required >
        </div>
        <div class="col-md-4">
            <label for="contact4" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact4" name="contact4" required >
        </div>
    </div>
</div>
                    <div class="contact-box">
   
    <div class="row">
           <label >5. </label><br>
        <div class="col-md-4">
          
            <label for="name5" class="-label">Name:</label>
            <input class="form-control" type="text" id="name5" name="name5" required >
        </div>
        <div class="col-md-4">
            <label for="relation5" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation5" name="relation5" required  >
        </div>
        <div class="col-md-4">
            <label for="contact5" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact5" name="contact5" required >
        </div>
    </div>
</div>               </div>
               <div class="col-md-12 ">
                  
                    <div class="contact-box">
   
    <div class="row">
           <label >6. </label><br>
        <div class="col-md-4">
          
            <label for="name6" class="-label">Name:</label>
            <input class="form-control" type="text" id="name6" name="name6" required >
        </div>
        <div class="col-md-4">
            <label for="relation6" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation6" name="relation6" required >
        </div>
        <div class="col-md-4">
            <label for="contact6" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact6" name="contact6" required >
        </div>
    </div>
</div>
                   <div class="contact-box">

    <div class="row">
           <label >7. </label><br>
        <div class="col-md-4">
          
            <label for="name7">Name:</label>
            <input class="form-control" type="text" id="name7" name="name7" >
        </div>
        <div class="col-md-4">
            <label for="relation7">Relation:</label>
            <input class="form-control" type="text" id="relation7" name="relation7" >
        </div>
        <div class="col-md-4">
            <label for="contact7">Contact Number:</label>
            <input class="form-control" type="text" id="contact7" name="contact7" >
        </div>
    </div>
</div>
                   <div class="contact-box">
   
    <div class="row">
           <label >8. </label><br>
        <div class="col-md-4">
          
            <label for="name8">Name:</label>
            <input class="form-control" type="text" id="name8" name="name8" >
        </div>
        <div class="col-md-4">
            <label for="relation8">Relation:</label>
            <input class="form-control" type="text" id="relation8" name="relation8" >
        </div>
        <div class="col-md-4">
            <label for="contact8">Contact Number:</label>
            <input class="form-control" type="text" id="contact8" name="contact8" >
        </div>
    </div>
</div>
                    <div class="contact-box">
    
    <div class="row">
           <label >9. </label><br>
        <div class="col-md-4">
          
            <label for="name9">Name:</label>
            <input class="form-control" type="text" id="name9" name="name9" >
        </div>
        <div class="col-md-4">
            <label for="relation9">Relation:</label>
            <input class="form-control" type="text" id="relation9" name="relation9" >
        </div>
        <div class="col-md-4">
            <label for="contact9">Contact Number:</label>
            <input class="form-control" type="text" id="contact9" name="contact9" >
        </div>
    </div>
</div>
                    
                     <div class="contact-box">
   
    <div class="row">
           <label >10. </label><br>
        <div class="col-md-4">
          
            <label for="name10">Name:</label>
            <input class="form-control" type="text" id="name10" name="name10" >
        </div>
        <div class="col-md-4">
            <label for="relation10">Relation:</label>
            <input class="form-control" type="text" id="relation10" name="relation10" >
        </div>
        <div class="col-md-4">
            <label for="contact10">Contact Number:</label>
            <input class="form-control" type="text" id="contact10" name="contact10" >
        </div>
    </div>
</div>
                </div>
</div>
<br>

        <h2 class="contact-heading">Bank Account Information</h2>
                 <div class="form-row">
         <div class="form-group col-md-6 ">
          <label for="accountno" class="-label">Bank Account no</label>
           <input type="text" class="form-control" id="accountno" name="accountno"  required >
         </div>
           <div class="form-group col-md-6 "   >
          <label for="ifsc" class="-label">IFSC Code</label>
           <input type="text" class="form-control" id="ifsc" name="ifsc" required  >
         
        </div>
        </div>
          <div class="form-row">
         <div class="form-group col-md-6 ">
          <label for="bname" class="-label">Bank Name</label>
           <input type="text" class="form-control" id="bname" name="bname" required  >
         </div>
        </div>
    
   <h2 class="contact-heading">Remarks</h2>
       <div class="form-row">
         <div class="form-group col-md-12 ">
           <textarea  class="form-control" id="remarks" name="remarks" rows="4" cols="50"  ></textarea>
         </div>
          
        </div>
      
      <!-- Document Upload -->
      <fieldset>
        <legend>Document Upload</legend>
        <div class="form-group">
            <label for="documents" class="-label">Upload Documents</label>
            <input type="file" class="form-control-file" id="documents" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.heic,.xls,.ppt" capture="environment" style="display: none;">
            <small class="form-text text-muted">Upload documents such as ID proof, address proof, income proof, etc.</small>
            <div class="button-group">
                <button id="start-camera">Start Camera</button>
                <button id="capture-image" style="display: none;">Capture Image</button>
            </div>
            <div class="video-container">
                <video id="video" autoplay></video>
            </div>
        </div>
    </fieldset>

      <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
    <!--camera capture script starts-->
    <script>
        const startCameraButton = document.getElementById('start-camera');
        const captureImageButton = document.getElementById('capture-image');
        const videoElement = document.getElementById('video');
        const fileInput = document.getElementById('documents');
        let stream;

        startCameraButton.addEventListener('click', async () => {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            videoElement.srcObject = stream;
            captureImageButton.style.display = 'block';
        });

        captureImageButton.addEventListener('click', () => {
            const canvas = document.createElement('canvas');
            canvas.width = videoElement.videoWidth;
            canvas.height = videoElement.videoHeight;
            canvas.getContext('2d').drawImage(videoElement, 0, 0);

            canvas.toBlob(blob => {
                const file = new File([blob], 'document.jpg', { type: 'image/jpeg' });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;

                // Stop the video stream
                stream.getTracks().forEach(track => track.stop());

                // Optionally, you can submit the form or perform any other action here
                console.log('File captured and added to input:', fileInput.files);
            });
        });
    </script>
    <!--camera capture script ends-->
    
    <script>
                document.addEventListener('DOMContentLoaded', (event) => {
    // Set the current date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('datepicker').value = today;

    // Set the last ID
    document.getElementById('srno').value = lastID;
});

$('#loanForm').submit(function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const phoneInput = document.getElementById('phone').value;
    const aadharInput = document.getElementById('aadhar1').value;
    const panInput = document.getElementById('pan1').value;

    // Custom phone number validation
    if (!/^\d{10}$/.test(phoneInput)) {
        alert('Please enter a valid 10-digit phone number.');
        return;
    }

    // Collect and validate contact numbers
    const contactNumbers = [];
    for (let i = 1; i <= 10; i++) {
        const contactField = document.getElementById(`contact${i}`);
        const contactValue = contactField.value;
        if (contactValue) {
            if (!/^\d{10}$/.test(contactValue)) {
                alert(`Contact number ${i} is not a valid 10-digit mobile number.`);
                contactField.focus();
                return;
            }
            contactNumbers.push(contactValue);
        }
    }

    // Check for duplicate contact numbers
    const uniqueContacts = new Set(contactNumbers);
    if (uniqueContacts.size !== contactNumbers.length) {
        alert('Please ensure all contact numbers are unique.');
        return;
    }

    // Validate Aadhar and PAN uniqueness
    $.ajax({
        url: 'validate.php',
        type: 'POST',
        data: { aadhar: aadharInput, pan: panInput },
        dataType: 'json',
        success: function(response) {
            if (response.exists) {
                alert('Aadhar or PAN already exists in the database. Please enter unique values.');
            } else {
                // Log the captured form data
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }

                fetch('process_form2.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        alert('Form submitted successfully!');
                        window.location.href = 'document_collection.php?id=' + lastID;
                    } else {
                        throw new Error('Network response was not ok.');
                    }
                })
                .catch(error => {
                    console.error('There was a problem with your fetch operation:', error);
                    alert('An error occurred while submitting the form. Please try again later.');
                });
            }
        },
        error: function() {
            alert('An error occurred while checking Aadhar and PAN uniqueness. Please try again later.');
        }
    });
});

    </script>

</body>

</html>