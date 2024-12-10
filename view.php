<?php
session_start(); // Start the session
error_reporting(E_ALL);
ini_set('display_errors', 1);
    $loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
    if($role=='admin'){
        include 'leftside.php';
    }
    elseif($role=='branchmanager'){
        include 'leftsidebranch.php';
    }
include 'config.php';


if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM personalinformation WHERE ID = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Fetch data and pre-fill the form fields
        $fullName = $row["FullName"];
        $caseof = $row["caseof"];
        $date = $row["date"];
        $address = $row["Address"];
         
        
    } else {
        echo "No record found for ID: $id";
    }
} else {
    echo "ID parameter missing in the URL.";
}
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
        @media (min-width: 992px) {
            .container, .container-lg, .container-md, .container-sm {
                max-width: 650px;
                margin-top: 10%;
            }
        }
    </style>
</head>
<body style="background:white;">

<?php 
// include 'leftside.php';
include 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM personalinformation WHERE ID = $id";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
    } else {
        echo "<div class='container'><div class='alert alert-danger'>No record found for ID: $id</div></div>";
        exit();
    }
} else {
    echo "<div class='container'><div class='alert alert-danger'>ID parameter is missing</div></div>";
    exit();
}

mysqli_close($conn);
?>

<div class="container">
    <h1 class="mb-4">Loan Application Form</h1>
    <form id="loanForm" method="POST" enctype="multipart/form-data">
        <!-- Personal Information -->
        <fieldset>
            <legend>Personal Information (Applicant Details)</legend>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="srno">Sr. No:</label>
                    <input type="text" class="form-control" id="srno" name="srno" value="<?php echo $data['sno']; ?>" readonly>
                </div>
                <div class="form-group col-md-4">
                    <label for="datepicker">Date:</label>
                    <input type="date" class="form-control" id="datepicker" name="datepicker" value="<?php echo $data['date']; ?>" readonly>
                </div>
                <div class="form-group col-md-4">
                    <label for="case_of" class="-label">Case Of:</label>
                    <input type="text" class="form-control" id="caseof" name="case_of" value="<?php echo $data['caseof']; ?>"  readonly>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="fullName" class="-label">Name</label>
                    <input type="text" class="form-control" id="fullName" name="fullName" value="<?php echo $data['FullName']; ?>"  readonly>
                </div>
                
                <div class="form-group col-md-6">
                    <label for="phone" class="-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" pattern="^\d{10}$" value="<?php echo $data['PhoneNumber']; ?>"  readonly>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12">
                        <label for="address" class="-label">Current Address</label>
                        <input type="text" class="form-control" id="address" name="address" value="<?php echo $data['Address']; ?>"  readonly>
                    </div>  
            </div>
            <!-- Employment Details -->
            <fieldset>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="details1" class="-label">Occupation</label>
                        <input type="text" class="form-control" id="details1" name="details1" value="<?php echo $data['details1']; ?>"  readonly>
                    </div>
                    <div class="form-group col-md-5">
                        <label for="email" class="-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $data['Email']; ?>" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="loancompany" class="-label">Company:- </label>
                        <select class="form-control" id="loancompany" name="loancompany" required readonly>
                            <option value="<?php echo $data['loancompanyapp']; ?>">
                                <?php echo $data['loancompanyapp']; ?>
                            </option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="cs_app" class="-label">CS. APP (CIBIL/EXP/EQI/CRIF)</label>
                        <input type="text" class="form-control" id="cs_app" name="cs_app" value="<?php echo $data['cs-app']; ?>"  readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="employer1" class="-label">Employer</label>
                        <input type="text" class="form-control" id="employer1" name="employer1" value="<?php echo $data['employer1']; ?>"  readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="income1" class="-label">Monthly Income (INR)</label>
                        <input type="number" class="form-control" id="income1" name="income1" value="<?php echo $data['income1']; ?>"  readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="-label">Income Type:</label><br>
                        <input type="radio" id="cash" name="incometype" value="cash" <?php echo $data['incometype'] == 'cash' ? 'checked' : ''; ?> disabled>
                        <label for="cash">Cash</label>
                        <input type="radio" id="accounts" name="incometype" value="accounts" <?php echo $data['incometype'] == 'account' ? 'checked' : ''; ?> disabled>
                        <label for="accounts">Accounts</label>
                    </div>
                </div>
            </fieldset>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="loanAmount" class="-label">Amount</label>
                    <input type="text" class="form-control" id="loanAmount" name="loanAmount" value="<?php echo $data['LoanAmount']; ?>"  readonly>
                </div>
              <div class="form-group col-md-6">
    <label for="loanPurpose" class="-label">Type Of Case</label>
    <select class="form-control" id="loanPurpose" name="loanPurpose" disabled>
        <option value="" >Select...</option>
        <option value="Gold Loan" <?php echo $data['LoanPurpose'] == 'Gold Loan' ? 'selected' : ''; ?> >Gold Loan</option>
        <option value="Home Loan" <?php echo $data['LoanPurpose'] == 'Home Loan' ? 'selected' : ''; ?>>Home Loan</option>
        <option value="Personal Loan" <?php echo $data['LoanPurpose'] == 'Personal Loan' ? 'selected' : ''; ?>>Personal Loan</option>
        <option value="LAP" <?php echo $data['LoanPurpose'] == 'LAP' ? 'selected' : ''; ?>>LAP (Loan Against Property)</option>
    </select>
</div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label class="-label">Ownership Status (Applicant)</label><br>
                    <input type="radio" id="self_owned" name="ownership_status" value="self_owned" <?php echo $data['status1'] == 'self_owned' ? 'checked' : ''; ?> disabled>
                    <label for="self_owned">Self Owned</label>
                    <input type="radio" id="rented" name="ownership_status" value="rented" <?php echo $data['status1'] == 'rented' ? 'checked' : ''; ?> disabled>
                    <label for="rented">Rented</label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="aadhar1" class="-label">Aadhar Number (Applicant)</label>
                    <input type="text" class="form-control" id="aadhar1" name="aadhar1" value="<?php echo $data['aadharapplicant']; ?>"  readonly>
                </div>
                <div class="form-group col-md-6">
                    <label for="pan1" class="-label">PAN Number (Applicant)</label>
                    <input type="text" class="form-control" id="pan1" name="pan1" value="<?php echo $data['panapplicant']; ?>"  readonly>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>CO-Applicant Details</legend>
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="co_app_name" class="-label">CO-Applicant Name</label>
                    <input type="text" class="form-control" id="co_app_name" name="co_app_name" value="<?php echo $data['co-app_name']; ?>"  readonly>
                </div>
                  <div class="form-row">
           <div class="form-group col-md-12 "   >
        <label for="details2" class="-label">Work Details</label>
           <input type="text" class="form-control"   id="details2" name="details2"  value="<?php echo $data['details2']; ?>"  readonly>
         
        </div>
            <div class="form-group col-md-4">
                        <label for="loancompany" class="-label">Company:- </label>
                        <select class="form-control" id="loancompany" name="loancompany" required readonly>
                            <option value="<?php echo $data['loancompanyapp']; ?>">
                                <?php echo $data['loancompanyapp']; ?>
                            </option>
                        </select>
            </div>   
           <div class="form-group col-md-4 ">
                <label for="address" class="-label">CO-Applicant (CIBIL/EXP/EQI/CRIF)</label>
                <input type="text" class="form-control"  id="co_app" name="co_app" value="<?php echo $data['co-app']; ?>"   readonly>
            </div>
        <div class="form-group col-md-4 "   >
        <label for="income2" class="-label">Monthly Income</label>
           <input type="text" class="form-control"   id="income2" name="income2"  value="<?php echo $data['income2']; ?>" readonly>
        </div>
        <!--</div>-->
            <div class="form-group col-md-12 ">
        <label class="-label">Ownership Status(CO App.) :</label><br>
                    <input type="radio" id="co_owned" name="co_ownership_status" value="self_owned" <?php echo $data['status2'] == 'self_owned' ? 'checked' : ''; ?> disabled>
                    <label for="co_owned">Self Owned</label>
                    <input type="radio" id="co_rented" name="co_ownership_status" value="rented"  <?php echo $data['status2'] == 'rented' ? 'checked' : ''; ?> disabled >
                    <label for="co_rented">Rented</label>
        </div>
        </div>
         <div class="form-group col-md-6">
                    <label for="co_app_aadhar" class="-label">CO-Applicant Aadhar Number</label>
                    <input type="text" class="form-control" id="co_app_aadhar" name="co_app_aadhar" value="<?php echo $data['aadharcoapplicant']; ?>"  readonly>
                </div>
                <div class="form-group col-md-6">
                    <label for="co_app_pan" class="-label">CO-Applicant PAN Number</label>
                    <input type="text" class="form-control" id="co_app_pan" name="co_app_pan" value="<?php echo $data['pancoapplicant']; ?>"  readonly>
                </div>
                <div class="form-group col-md-12">
                    <label for="relation" class="-label">Relation</label>
                    <input type="text" class="form-control" id="relation" name="relation" value="<?php echo $data['relation']; ?>"  readonly>
                </div>
                   <!--<div class="form-row">-->
                             <div class="form-group col-md-12 ">
                              <label for="reason_of_loan" class="-label">Reason Of Loan:</label>
                                <input class="form-control"  type="text" id="reason_of_loan" name="reason_of_loan" value="<?php echo $data['reason']; ?>"  readonly >
                             </div>
                    <!--</div>-->
            </div>
        </fieldset>
         <div class="form-row">
                <div class="col-md-12 ">
                   <div class="contact-box">
    <h2 class="contact-heading">Contact Information</h2>
    <div class="row">
           <label >1. </label><br>
        <div class="col-md-4">
          
            <label for="name1" class="-label">Name:</label>
            <input class="form-control" type="text" id="name1" name="name1"  value="<?php echo $data['name1']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="relation1" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation1" name="relation1"  value="<?php echo $data['relation1']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="contact1" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact1" name="contact1" value="<?php echo $data['contact1']; ?>"  readonly >
        </div>
    </div>
</div>

                 <div class="contact-box">
   
    <div class="row">
           <label>2. </label><br>
        <div class="col-md-4">
          
            <label for="name1" class="-label">Name:</label>
            <input class="form-control" type="text" id="name2" name="name2" value="<?php echo $data['name2']; ?>"  readonly >
        </div>
        <div class="col-md-4">
            <label for="relation1" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation2" name="relation2" value="<?php echo $data['relation2']; ?>"  readonly >
        </div>
        <div class="col-md-4">
            <label for="contact1" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact2" name="contact2" value="<?php echo $data['contact2']; ?>"  readonly >
        </div>
    </div>
</div>
                 <div class="contact-box">
    
    <div class="row">
           <label >3. </label><br>
        <div class="col-md-4">
          
            <label for="name1" class="-label">Name:</label>
            <input class="form-control" type="text" id="name3" name="name3"  value="<?php echo $data['name3']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="relation1" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation3" name="relation3"  value="<?php echo $data['relation3']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="contact1" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact3" name="contact3"  value="<?php echo $data['contact3']; ?>"  readonly>
        </div>
    </div>
</div>
                    <div class="contact-box">
    
    <div class="row">
           <label >4. </label><br>
        <div class="col-md-4">
            <label for="name1" class="-label">Name:</label>
            <input class="form-control" type="text" id="name4" name="name4"  value="<?php echo $data['name4']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="relation1" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation4" name="relation4"  value="<?php echo $data['relation4']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="contact1" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact4" name="contact4"  value="<?php echo $data['contact4']; ?>"  readonly>
        </div>
    </div>
</div>
                    <div class="contact-box">
    <div class="row">
           <label >5. </label><br>
        <div class="col-md-4">
          
            <label for="name1" class="-label">Name:</label>
            <input class="form-control" type="text" id="name5" name="name5"  value="<?php echo $data['name5']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="relation1" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation5" name="relation5" value="<?php echo $data['relation5']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="contact1" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact5" name="contact5" value="<?php echo $data['contact5']; ?>"  readonly >
        </div>
    </div>
</div>               </div>
               <div class="col-md-12 ">
                    <div class="contact-box">
    <div class="row">
           <label >6. </label><br>
        <div class="col-md-4">
            <label for="name1" class="-label">Name:</label>
            <input class="form-control" type="text" id="name6" name="name6" value="<?php echo $data['name6']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="relation1" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation6" name="relation6" value="<?php echo $data['relation6']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="contact1" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact6" name="contact6" value="<?php echo $data['contact6']; ?>"  readonly >
        </div>
    </div>
</div>
                   <div class="contact-box">
    <div class="row">
           <label >7. </label><br>
        <div class="col-md-4">
          
            <label for="name1">Name:</label>
            <input class="form-control" type="text" id="name7" name="name7"  value="<?php echo $data['name7']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="relation1">Relation:</label>
            <input class="form-control" type="text" id="relation7" name="relation7" value="<?php echo $data['relation7']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="contact1">Contact Number:</label>
            <input class="form-control" type="text" id="contact7" name="contact7" value="<?php echo $data['contact7']; ?>"  readonly>
        </div>
    </div>
</div>
                   <div class="contact-box">
   
    <div class="row">
           <label >8. </label><br>
        <div class="col-md-4">
          
            <label for="name1">Name:</label>
            <input class="form-control" type="text" id="name8" name="name8" value="<?php echo $data['name8']; ?>"  readonly >
        </div>
        <div class="col-md-4">
            <label for="relation1">Relation:</label>
            <input class="form-control" type="text" id="relation8" name="relation8"  value="<?php echo $data['relation8']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="contact1">Contact Number:</label>
            <input class="form-control" type="text" id="contact8" name="contact8"  value="<?php echo $data['contact8']; ?>"  readonly>
        </div>
    </div>
</div>
                    <div class="contact-box">
    <div class="row">
           <label >9. </label><br>
        <div class="col-md-4">
            <label for="name1">Name:</label>
            <input class="form-control" type="text" id="name9" name="name9"  value="<?php echo $data['name9']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="relation1">Relation:</label>
            <input class="form-control" type="text" id="relation9" name="relation9" value="<?php echo $data['relation9']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="contact1">Contact Number:</label>
            <input class="form-control" type="text" id="contact9" name="contact9"  value="<?php echo $data['contact9']; ?>"  readonly>
        </div>
    </div>
</div>
                     <div class="contact-box">
    <div class="row">
           <label >10. </label><br>
        <div class="col-md-4">
          
            <label for="name1">Name:</label>
            <input class="form-control" type="text" id="name10" name="name10" value="<?php echo $data['name10']; ?>"  readonly >
        </div>
        <div class="col-md-4">
            <label for="relation1">Relation:</label>
            <input class="form-control" type="text" id="relation10" name="relation10" value="<?php echo $data['relation10']; ?>"  readonly>
        </div>
        <div class="col-md-4">
            <label for="contact1">Contact Number:</label>
            <input class="form-control" type="text" id="contact10" name="contact10" value="<?php echo $data['contact10']; ?>"  readonly>
        </div>
    </div>
</div>
                </div>
</div>
        <br>
        <h2 class="contact-heading">Bank Account Information</h2>
            <div class="form-row">
         <div class="form-group col-md-6 ">
          <label for="address" class="-label">Bank Account no</label>
           <input type="text" class="form-control" id="accountno" name="accountno" value="<?php echo $data['bank_account_no']; ?>"  readonly >
         </div>
           <div class="form-group col-md-6 "   >
          <label for="address" class="-label">IFSC Code</label>
           <input type="text" class="form-control" id="ifsc" name="ifsc"  value="<?php echo $data['ifsc']; ?>"  readonly>
        </div>
        </div>
          <div class="form-row">
         <div class="form-group col-md-6 ">
          <label for="address" class="-label">Bank Name</label>
           <input type="text" class="form-control" id="bname" name="bname"  value="<?php echo $data['bank_name']; ?>"  readonly>
         </div>
        </div>
         <h2 class="contact-heading">Remarks</h2>
       <div class="form-row">
    <div class="form-group col-md-12">
        <textarea class="form-control" id="remarks" name="remarks" rows="4" cols="50" readonly><?php echo $data['remarks']; ?></textarea>
    </div>
</div>
      <button onclick="window.print();return false;"  style="background:#1C84EE;border-radius:5px;color:white;width:5%;border:2px solid #1C84EE;">Print</button>  
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>