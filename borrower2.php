<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Application</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
    </style>
</head>

<body style="background:white;">
<?php include 'leftbarsales.php'; ?>
    <div class="container">
        <h1 class="mb-4">Loan Application Form</h1>
        <form id="loanForm" method="POST" enctype="multipart/form-data" >
      <!-- Personal Information -->
      <fieldset>
        <legend>Personal Information</legend>
         <div class="form-row">
          <div class="form-group col-md-4">
             <label for="srno">Sr. No:</label>
          
            <input type="text" class="form-control" id="srno" name="srno" >
          </div>
          <div class="form-group col-md-4">
            <label for="datepicker">Date:</label>
             
            <input type="date" class="form-control" id="datepicker" name="datepicker" >
          </div>
           <div class="form-group col-md-4">
            <label for="case_of">Case Of:</label>
             
            <input type="text" class="form-control" id="caseof" name="case_of" >
          </div>
        </div>
        <div class="form-row">
        <div class="form-group col-md-6 "   >
              <label for="address">CS. APP (CIBIL/EXP/EQI/CRIF)</label>
               <input type="text" class="form-control" id="cs_app" name="cs_app" >
             </div>
        </div>
         <div class="form-row">
          <div class="form-group col-md-6">
            <label for="amount">Amount </label>
            <input type="text" class="form-control" id="loanAmount" name="loanAmount" >
          </div>
          <div class="form-group col-md-6">
            <label for="case">Type Of Case:- </label>
            <select class="form-control"  id="loanPurpose" name="loanPurpose" >
                        <option value="">Select...</option>
                        <option value="gold">Gold Loan</option>
                        <option value="home">Home Loan</option>
                        <option value="personal">Personal Loan</option>
                        <option value="lap">LAP </option>
                    </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-4">
            <label for="fullName"> Name</label>
            <input type="text" class="form-control" id="fullName" name="fullName" >
          </div>
           <div class="form-group col-md-4">
            <label for="fullName">Ref By</label>
            <input type="text" class="form-control" id="refby" name="refby" >
          </div>
          <div class="form-group col-md-4">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" >
          </div>
        </div>
        
        
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="phone">Phone Number</label>
            <input type="tel" class="form-control" id="phone" name="phone" >
          </div>
           <div class="form-group col-md-6">
            <label for="phone">Current Address</label>
            <input type="text" class="form-control" id="address" name="address" >
          </div>
          
          <!--<div class="form-group col-md-4">-->
          <!--  <label for="dob">Date of Birth</label>-->
          <!--  <input type="date" class="form-control" id="dob" name="dob" >-->
          <!--</div>-->
        </div>
        <div class="form-row">
          <div class="form-group col-md-12 "   >
        <label>Ownership Status(App.) :</label><br>
         
                    <input type="radio" id="co_owned" name="ownership_status" value="self_owned">
                    <label for="co_owned">Self Owned</label>
                    <input type="radio" id="co_rented" name="ownership_status" value="rented">
                    <label for="co_rented">Rented</label>
         </div>
        </div>
    </fieldset>
    
    <fieldset>
            <legend>CO App. Details</legend>
         <div class="form-row">
             
           <div class="form-group col-md-12 ">
                <label for="address">CO.App (CIBIL/EXP/EQI/CRIF)</label>
                <input type="text" class="form-control"  id="co_app" name="co_app" >
            </div>
        </div>
        
        
         <div class="form-row">
         <!--<div class="form-group col-md-6 "   >-->
         <!--  <label for="app_name">App. Name</label>-->
         <!--  <input type="text" class="form-control" id="app_name" name="app_name" >-->
         <!--</div>-->
           <div class="form-group col-md-12 "   >
        <label for="co_app_name">CO-App. Name:</label>
           <input type="text" class="form-control"   id="co_app_name" name="co_app_name" >
         
        </div>
        </div>
        
         
         <div class="form-row">
         <!--<div class="form-group col-md-6 "   >-->
         <!--  <label for="details1">Work Details</label>-->
         <!--  <input type="text" class="form-control" id="details1" name="details1" >-->
         <!--</div>-->
           <div class="form-group col-md-12 "   >
        <label for="details2">Work Details</label>
           <input type="text" class="form-control"   id="details2" name="details2" >
         
        </div>
        </div>
        
               
         <div class="form-row">
         <!--<div class="form-group col-md-6 "   >-->
         <!--  <label for="income1">Monthly Income</label>-->
         <!--  <input type="text" class="form-control" id="income1" name="income1" >-->
         <!--</div>-->
           <div class="form-group col-md-12 "   >
        <label for="income2">Monthly Income</label>
           <input type="text" class="form-control"   id="income2" name="income2" >
         
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
           <div class="form-group col-md-6 "   >
        <label>Ownership Status(CO App.) :</label><br>
         
                    <input type="radio" id="co_owned" name="co_ownership_status" value="self_owned">
                    <label for="co_owned">Self Owned</label>
                    <input type="radio" id="co_rented" name="co_ownership_status" value="rented">
                    <label for="co_rented">Rented</label>
         
        </div>
        </div>
        
        
           <div class="form-row">
         <div class="form-group col-md-12 "   >
          <label for="relation">Relation  between Applicant and Co-applicant</label>
            <input class="form-control"  type="text" id="relation" name="relation">
           
         </div>
</div>


      <div class="form-row">
         <div class="form-group col-md-12 "   >
        <label for="can_pay_installment">Can Pay Installment:</label>
            <input class="form-control"  type="text"id="can_pay_installment" name="can_pay_installment">
           
         </div>
</div>




      <div class="form-row">
         <div class="form-group col-md-12 "   >
          <label for="reason_of_loan">Reason Of Loan:</label>
            <input class="form-control"  type="text" id="reason_of_loan" name="reason_of_loan">
           
         </div>
</div>


      <div class="form-row">
         <div class="form-group col-md-12 "   >
         <label for="observation">Can Be Settled for (Observation):</label>
            <input class="form-control"  type="text" id="observation" name="observation">
           
         </div>
</div>
  <div class="form-row">
        
                <div class="col-md-6 ">
                    <div class="contact-box">
                         <h2 class="contact-heading">Contact Information</h2>
                        <label>1.</label><br>
                        <input class="form-control" type="text" id="contact1" name="contact1">
                    </div>
                    <div class="contact-box">
                        <label>2. </label><br>
                        <input class="form-control"  type="text" id="contact2" name="contact2">
                    </div>
                    <div class="contact-box">
                        <label>3. </label><br>
                        <input class="form-control" type="text" id="contact3" name="contact3">
                    </div>
                     <div class="contact-box">
                        <label>4. </label><br>
                        <input class="form-control" type="text" id="contact4" name="contact4">
                    </div>
                    
                     <div class="contact-box">
                        <label>5. </label><br>
                        <input class="form-control" type="text" id="contact5" name="contact5">
                    </div>
                </div>
               <div class="col-md-6 ">
                    <div class="contact-box">
                      <br><br>
                        <label>6.</label><br>
                        <input class="form-control" type="text" id="contact6" name="contact6">
                    </div>
                    <div class="contact-box">
                        <label>7. </label><br>
                        <input class="form-control"  type="text" id="contact7" name="contact7">
                    </div>
                    <div class="contact-box">
                        <label>8. </label><br>
                        <input class="form-control" type="text" id="contact8" name="contact8">
                    </div>
                     <div class="contact-box">
                        <label>9. </label><br>
                        <input class="form-control" type="text" id="contact9" name="contact9">
                    </div>
                    
                     <div class="contact-box">
                        <label>10. </label><br>
                        <input class="form-control" type="text" id="contact10" name="contact10">
                    </div>
                </div>
</div>
<br>

        <h2 class="contact-heading">Bank Account Information</h2>
                 <div class="form-row">
         <div class="form-group col-md-6 ">
          <label for="address">Bank Account no</label>
           <input type="text" class="form-control" id="accountno" name="accountno" >
         </div>
           <div class="form-group col-md-6 "   >
          <label for="address">IFSC Code</label>
           <input type="text" class="form-control" id="ifsc" name="ifsc" >
         
        </div>
        </div>
          <div class="form-row">
         <div class="form-group col-md-6 ">
          <label for="address">Bank Name</label>
           <input type="text" class="form-control" id="bname" name="bname">
         </div>
        </div>
      <!-- Employment Details -->
      <fieldset>
        <legend>Employment Details</legend>
        <div class="form-group">
          <label for="details1">Occupation</label>
          <input type="text" class="form-control" id="details1" name="details1" >
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="employer1">Employer</label>
            <input type="text" class="form-control" id="employer1" name="employer1" >
          </div>
          <div class="form-group col-md-6">
            <label for="income1">Monthly Income (INR)</label>
            <input type="number" class="form-control" id="income1" name="income1" >
          </div>
        </div>
      </fieldset>

      <!-- Loan Details -->
      <!--<fieldset>-->
      <!--  <legend>Loan Details</legend>-->
      <!--  <div class="form-group">-->
      <!--    <label for="loanAmount">Loan Amount (INR)</label>-->
      <!--    <input type="number" class="form-control" id="loanAmount" name="loanAmount" >-->
      <!--  </div>-->
      <!--  <div class="form-group">-->
      <!--    <label for="loanPurpose">Loan Purpose</label>-->
      <!--    <select class="form-control" id="loanPurpose" name="loanPurpose" >-->
      <!--      <option value="">Select...</option>-->
      <!--      <option value="Personal Loan">Personal Loan</option>-->
      <!--      <option value="Home Loan">Home Loan</option>-->
      <!--      <option value="LAP">LAP(Loan Against Property)</option>-->
      <!--      <option value="Gold Loan">Gold Loan</option>-->
      <!--    </select>-->
      <!--  </div>-->
      <!--</fieldset>-->

      <!-- Document Upload -->
      <fieldset>
        <legend>Document Upload</legend>
        <div class="form-group">
          <label for="documents">Upload Documents</label>
          <input type="file" class="form-control-file" id="documents" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.heic,.xls,.ppt" >
          <small class="form-text text-muted">Upload documents such as ID proof, address proof, income proof, etc.</small>
        </div>
      </fieldset>

      <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

  <script>
        // Example script for form submission handling (you can replace it with your own logic)
        $('#loanForm').submit(function (e) {
    e.preventDefault();
    const formData = new FormData(this);

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
            window.location.reload();
        } else {
            throw new Error('Network response was not ok.');
        }
    })
    .catch(error => {
        console.error('There was a problem with your fetch operation:', error);
        alert('An error occurred while submitting the form. Please try again later.');
    });
});

    </script>
</body>

</html>



