<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

function checkUniqueness($conn, $userId, $field, $value) {
    $query = "SELECT * FROM personalinformation WHERE $field = ? AND ID != ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $value, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->num_rows > 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['id'];
    include 'config.php';

    // Mapping of form fields to DB fields
    $fieldMapping = [
        'srno' => 'sno',
        'datepicker' => 'date',
        'case_of' => 'caseof',
        'fullName' => 'FullName',
        'phone' => 'PhoneNumber',
        'address' => 'Address',
        'details1' => 'details1',
        'email' => 'Email',
        'loancompany' => 'loancompanyapp',
        'loancompanycoapp' => 'loancompanycoapp',
        'cs_app' => 'cs-app',
        'employer1' => 'employer1',
        'income1' => 'income1',
        'loanAmount' => 'LoanAmount',
        'loanPurpose' => 'LoanPurpose',
        'ownership_status' => 'status1',  // Applicant Ownership Status
        'incometype' => 'incometype', // New Income Type field
        'aadhar1' => 'aadharapplicant',
        'pan1' => 'panapplicant',
        'co_app_name' => 'co-app_name',
        'details2' => 'details2',
        'co_app' => 'co-app',
        'income2' => 'income2',
        'co_ownership_status' => 'status2',  // Co-Applicant Ownership Status
        'co_app_aadhar' => 'aadharcoapplicant',
        'co_app_pan' => 'pancoapplicant',
        'relation' => 'relation',
        'reason_of_loan' => 'reason',
        'name1' => 'name1',
        'relation1' => 'relation1',
        'contact1' => 'contact1',
        'name2' => 'name2',
        'relation2' => 'relation2',
        'contact2' => 'contact2',
        'name3' => 'name3',
        'relation3' => 'relation3',
        'contact3' => 'contact3',
        'name4' => 'name4',
        'relation4' => 'relation4',
        'contact4' => 'contact4',
        'name5' => 'name5',
        'relation5' => 'relation5',
        'contact5' => 'contact5',
        'name6' => 'name6',
        'relation6' => 'relation6',
        'contact6' => 'contact6',
        'name7' => 'name7',
        'relation7' => 'relation7',
        'contact7' => 'contact7',
        'name8' => 'name8',
        'relation8' => 'relation8',
        'contact8' => 'contact8',
        'name9' => 'name9',
        'relation9' => 'relation9',
        'contact9' => 'contact9',
        'name10' => 'name10',
        'relation10' => 'relation10',
        'contact10' => 'contact10',
        'accountno' => 'bank_account_no',
        'ifsc' => 'ifsc',
        'bname' => 'bank_name',
        'remarks' => 'remarks',
    ];

    // Initialize response variables
    $status = '';
    $message = '';

    // Check if a file was uploaded
    if (!empty($_FILES['document']['name'])) {
        if ($_FILES['document']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            $originalFileName = basename($_FILES['document']['name']);
            $tempFileName = uniqid('upload_', true) . '_' . $originalFileName;
            $targetFilePath = $uploadDir . $tempFileName;

            if (move_uploaded_file($_FILES['document']['tmp_name'], $targetFilePath)) {
                $documentPath = $targetFilePath;
                $checkQuery = "SELECT * FROM documentuploads WHERE LoanApplicationID = ?";
                $stmt = $conn->prepare($checkQuery);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $updateQuery = "UPDATE documentuploads SET DocumentPath = ? WHERE LoanApplicationID = ?";
                    $stmt = $conn->prepare($updateQuery);
                    $stmt->bind_param("si", $documentPath, $userId);
                    if ($stmt->execute()) {
                        $status = 'success';
                        $message = 'File updated successfully.';
                    } else {
                        $status = 'error';
                        $message = 'Database error: ' . $conn->error;
                    }
                } else {
                    $insertQuery = "INSERT INTO documentuploads (LoanApplicationID, DocumentPath) VALUES (?, ?)";
                    $stmt = $conn->prepare($insertQuery);
                    $stmt->bind_param("is", $userId, $documentPath);
                    if ($stmt->execute()) {
                        $status = 'success';
                        $message = 'File uploaded successfully.';
                    } else {
                        $status = 'error';
                        $message = 'Database error: ' . $conn->error;
                    }
                }
            } else {
                $status = 'error';
                $message = 'Error uploading file.';
            }
        } else {
            $status = 'error';
            $message = 'File upload error: ' . $_FILES['document']['error'];
        }
    }

    if (!empty($message)) {
        echo "<script>";
        echo "alert('" . addslashes($message) . "');";
        echo "window.history.back();";
        echo "</script>";
        exit;
    }

    // Fetch current data from the database
    $sql = "SELECT * FROM personalinformation WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentData = $result->fetch_assoc();
    $stmt->close();

    // Check if any relevant field was edited
    $fieldsEdited = [];
    $fieldsToCheck = ['phone', 'pan1', 'aadhar1'];
    foreach ($fieldsToCheck as $field) {
        if (isset($_POST[$field]) && $_POST[$field] != $currentData[$fieldMapping[$field]]) {
            $fieldsEdited[$fieldMapping[$field]] = $_POST[$field];
        }
    }

    if (!empty($fieldsEdited)) {
        foreach ($fieldsEdited as $dbField => $value) {
            if (checkUniqueness($conn, $userId, $dbField, $value)) {
                echo "<script>";
                echo "alert('The $dbField value already exists for another user. Please check your inputs.');";
                echo "window.history.back();";
                echo "</script>";
                $conn->close();
                exit;
            }
        }
    }

    $fields = [];
    $params = [];
    $types = '';

    foreach ($_POST as $formField => $newValue) {
        if ($formField != 'id' && isset($fieldMapping[$formField])) {
            $dbField = $fieldMapping[$formField];
            if ($newValue != $currentData[$dbField]) {
                $fields[] = "`$dbField` = ?";
                $params[] = $newValue;
                $types .= 's';
            }
        }
    }

    if (count($fields) > 0) {
        $params[] = $userId;
        $types .= 'i';

        $sql = "UPDATE personalinformation SET " . implode(', ', $fields) . " WHERE ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        if ($stmt->errno) {
            echo "<script>";
            echo "alert('Error updating record: " . htmlspecialchars($stmt->error) . "');";
            echo "window.history.back();";
            echo "</script>";
            $stmt->close();
            $conn->close();
            exit;
        }

        echo "<script>";
        echo "alert('Record updated successfully');";
        echo "window.history.back();";
        echo "</script>";
        $stmt->close();
    } else {
        echo "<script>";
        echo "alert('No changes were made.');";
        echo "window.history.back();";
        echo "</script>";
    }

    $conn->close();
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
    include 'config.php';
    if($_SESSION['username']=='admin')
        {
            include 'leftside.php';
        }
        else{
            include 'leftsidebranch.php';
        }
    
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $sql = "SELECT * FROM personalinformation WHERE ID = $id";
        $result = mysqli_query($conn, $sql);
        
        //new
             // Assuming you get this ID from somewhere
        
            $query = "SELECT DocumentPath FROM documentuploads WHERE LoanApplicationID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($documentPath);
            $stmt->fetch();
        //new
    
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
    
    // mysqli_close($conn);
?>

<div class="container">
    <h1 class="mb-4">Loan Application Form</h1>
    <form id="updateloanForm" action="update_form.php" method="POST" enctype="multipart/form-data">
        <!-- Personal Information -->
        <fieldset>
            <legend>Personal Information (Applicant Details)</legend>
            <div class="form-row">
                <input type="hidden" name="id" value="<?php echo $data['ID']; ?>">
                <div class="form-group col-md-4">
                    <label for="srno">Sr. No:</label>
                    <input type="text" class="form-control" id="srno" name="srno" value="<?php echo $data['sno']; ?>"  readonly>
                </div>
                <div class="form-group col-md-4">
                    <label for="datepicker">Date:</label>
                    <input type="date" class="form-control" id="datepicker" name="datepicker" value="<?php echo $data['date']; ?>"  >
                </div>
                <div class="form-group col-md-4">
                    <label for="case_of" class="-label">Case Of:</label>
                    <input type="text" class="form-control" id="caseof" name="case_of" value="<?php echo $data['caseof']; ?>"   >
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="fullName" class="-label">Name</label>
                    <input type="text" class="form-control" id="fullName" name="fullName" value="<?php echo $data['FullName']; ?>"   >
                </div>
                
                <div class="form-group col-md-6">
                    <label for="phone" class="-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" pattern="^\d{10}$" value="<?php echo $data['PhoneNumber']; ?>"   >
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12">
                        <label for="address" class="-label">Current Address</label>
                        <input type="text" class="form-control" id="address" name="address" value="<?php echo $data['Address']; ?>"   >
                    </div>  
            </div>
            <!-- Employment Details -->
            <fieldset>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="details1" class="-label">Occupation</label>
                        <input type="text" class="form-control" id="details1" name="details1" value="<?php echo $data['details1']; ?>"   >
                    </div>
                    <div class="form-group col-md-5">
                        <label for="email" class="-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $data['Email']; ?>"  >
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="loancompany" class="-label">Company:- </label>
                        <select class="form-control" id="loancompany" name="loancompany" required>
    <option value="">Select...</option>
    <option value="CIBIL" <?php echo $data['loancompanyapp'] == 'CIBIL' ? 'selected' : ''; ?>>CIBIL</option>
    <option value="CRIF" <?php echo $data['loancompanyapp'] == 'CRIF' ? 'selected' : ''; ?>>CRIF</option>
    <option value="EXPERIAN" <?php echo $data['loancompanyapp'] == 'EXPERIAN' ? 'selected' : ''; ?>>EXPERIAN</option>
    <option value="EQUIFAX" <?php echo $data['loancompanyapp'] == 'EQUIFAX' ? 'selected' : ''; ?>>EQUIFAX</option>
    <option value="HIGHMARK" <?php echo $data['loancompanyapp'] == 'HIGHMARK' ? 'selected' : ''; ?>>HIGHMARK</option>
</select>

                    </div>
                    <div class="form-group col-md-6">
                        <label for="cs_app" class="-label">CS. APP (CIBIL/EXP/EQI/CRIF)</label>
                        <input type="text" class="form-control" id="cs_app" name="cs_app" value="<?php echo $data['cs-app']; ?>"   >
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="employer1" class="-label">Employer</label>
                        <input type="text" class="form-control" id="employer1" name="employer1" value="<?php echo $data['employer1']; ?>"   >
                    </div>
                    <div class="form-group col-md-4">
                        <label for="income1" class="-label">Monthly Income (INR)</label>
                        <input type="number" class="form-control" id="income1" name="income1" value="<?php echo $data['income1']; ?>"   >
                    </div>
                    <div class="form-group col-md-4">
                        <label class="-label">Income Type:</label><br>
                        <input type="radio" id="cash" name="incometype" value="cash" <?php echo $data['incometype'] == 'cash' ? 'checked' : ''; ?> >
                        <label for="cash">Cash</label>
                        <input type="radio" id="accounts" name="incometype" value="accounts" <?php echo $data['incometype'] == 'account' ? 'checked' : ''; ?> >
                        <label for="accounts">Accounts</label>
                    </div>
                </div>
            </fieldset>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="loanAmount" class="-label">Amount</label>
                    <input type="text" class="form-control" id="loanAmount" name="loanAmount" value="<?php echo $data['LoanAmount']; ?>"   >
                </div>
              <div class="form-group col-md-6">
    <label for="loanPurpose" class="-label">Type Of Case</label>
    <select class="form-control" id="loanPurpose" name="loanPurpose">
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
                    <input type="radio" id="self_owned" name="ownership_status" value="self_owned" <?php echo $data['status1'] == 'self_owned' ? 'checked' : ''; ?> >
                    <label for="self_owned">Self Owned</label>
                    <input type="radio" id="rented" name="ownership_status" value="rented" <?php echo $data['status1'] == 'rented' ? 'checked' : ''; ?> >
                    <label for="rented">Rented</label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="aadhar1" class="-label">Aadhar Number (Applicant)</label>
                    <input type="text" class="form-control" id="aadhar1" name="aadhar1" value="<?php echo $data['aadharapplicant']; ?>"   >
                </div>
                <div class="form-group col-md-6">
                    <label for="pan1" class="-label">PAN Number (Applicant)</label>
                    <input type="text" class="form-control" id="pan1" name="pan1" value="<?php echo $data['panapplicant']; ?>"   >
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>CO-Applicant Details</legend>
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="co_app_name" class="-label">CO-Applicant Name</label>
                    <input type="text" class="form-control" id="co_app_name" name="co_app_name" value="<?php echo $data['co-app_name']; ?>"   >
                </div>
                  <div class="form-row">
           <div class="form-group col-md-12 "   >
        <label for="details2" class="-label">Work Details</label>
           <input type="text" class="form-control"   id="details2" name="details2"  value="<?php echo $data['details2']; ?>"   >
         
        </div>
            <div class="form-group col-md-4">
                        <label for="loancompanycoapp" class="-label">Company(CO-App.):- </label>
                        <select class="form-control" id="loancompanycoapp" name="loancompanycoapp" required>
    <option value="">Select...</option>
    <option value="CIBIL" <?php echo $data['loancompanycoapp'] == 'CIBIL' ? 'selected' : ''; ?>>CIBIL</option>
    <option value="CRIF" <?php echo $data['loancompanycoapp'] == 'CRIF' ? 'selected' : ''; ?>>CRIF</option>
    <option value="EXPERIAN" <?php echo $data['loancompanycoapp'] == 'EXPERIAN' ? 'selected' : ''; ?>>EXPERIAN</option>
    <option value="EQUIFAX" <?php echo $data['loancompanycoapp'] == 'EQUIFAX' ? 'selected' : ''; ?>>EQUIFAX</option>
    <option value="HIGHMARK" <?php echo $data['loancompanycoapp'] == 'HIGHMARK' ? 'selected' : ''; ?>>HIGHMARK</option>
</select>

            </div>   
           <div class="form-group col-md-4 ">
                <label for="address" class="-label">CO-Applicant (CIBIL/EXP/EQI/CRIF)</label>
                <input type="text" class="form-control"  id="co_app" name="co_app" value="<?php echo $data['co-app']; ?>"    >
            </div>
        <div class="form-group col-md-4 "   >
        <label for="income2" class="-label">Monthly Income</label>
           <input type="text" class="form-control"   id="income2" name="income2"  value="<?php echo $data['income2']; ?>"  >
        </div>
        <!--</div>-->
            <div class="form-group col-md-12 ">
        <label class="-label">Ownership Status(CO App.) :</label><br>
                    <input type="radio" id="co_owned" name="co_ownership_status" value="self_owned" <?php echo $data['status2'] == 'self_owned' ? 'checked' : ''; ?> >
                    <label for="co_owned">Self Owned</label>
                    <input type="radio" id="co_rented" name="co_ownership_status" value="rented"  <?php echo $data['status2'] == 'rented' ? 'checked' : ''; ?>  >
                    <label for="co_rented">Rented</label>
        </div>
        </div>
         <div class="form-group col-md-6">
                    <label for="co_app_aadhar" class="-label">CO-Applicant Aadhar Number</label>
                    <input type="text" class="form-control" id="co_app_aadhar" name="co_app_aadhar" value="<?php echo $data['aadharcoapplicant']; ?>"   >
                </div>
                <div class="form-group col-md-6">
                    <label for="co_app_pan" class="-label">CO-Applicant PAN Number</label>
                    <input type="text" class="form-control" id="co_app_pan" name="co_app_pan" value="<?php echo $data['pancoapplicant']; ?>"   >
                </div>
                <div class="form-group col-md-12">
                    <label for="relation" class="-label">Relation</label>
                    <input type="text" class="form-control" id="relation" name="relation" value="<?php echo $data['relation']; ?>"   >
                </div>
                   <!--<div class="form-row">-->
                             <div class="form-group col-md-12 ">
                              <label for="reason_of_loan" class="-label">Reason Of Loan:</label>
                                <input class="form-control"  type="text" id="reason_of_loan" name="reason_of_loan" value="<?php echo $data['reason']; ?>"    >
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
            <input class="form-control" type="text" id="name1" name="name1"  value="<?php echo $data['name1']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="relation1" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation1" name="relation1"  value="<?php echo $data['relation1']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="contact1" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact1" name="contact1" value="<?php echo $data['contact1']; ?>"    >
        </div>
    </div>
</div>

                 <div class="contact-box">
   
    <div class="row">
           <label>2. </label><br>
        <div class="col-md-4">
          
            <label for="name1" class="-label">Name:</label>
            <input class="form-control" type="text" id="name2" name="name2" value="<?php echo $data['name2']; ?>"    >
        </div>
        <div class="col-md-4">
            <label for="relation1" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation2" name="relation2" value="<?php echo $data['relation2']; ?>"    >
        </div>
        <div class="col-md-4">
            <label for="contact1" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact2" name="contact2" value="<?php echo $data['contact2']; ?>"    >
        </div>
    </div>
</div>
                 <div class="contact-box">
    
    <div class="row">
           <label >3. </label><br>
        <div class="col-md-4">
          
            <label for="name1" class="-label">Name:</label>
            <input class="form-control" type="text" id="name3" name="name3"  value="<?php echo $data['name3']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="relation1" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation3" name="relation3"  value="<?php echo $data['relation3']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="contact1" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact3" name="contact3"  value="<?php echo $data['contact3']; ?>"   >
        </div>
    </div>
</div>
                    <div class="contact-box">
    
    <div class="row">
           <label >4. </label><br>
        <div class="col-md-4">
            <label for="name1" class="-label">Name:</label>
            <input class="form-control" type="text" id="name4" name="name4"  value="<?php echo $data['name4']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="relation1" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation4" name="relation4"  value="<?php echo $data['relation4']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="contact1" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact4" name="contact4"  value="<?php echo $data['contact4']; ?>"   >
        </div>
    </div>
</div>
                    <div class="contact-box">
    <div class="row">
           <label >5. </label><br>
        <div class="col-md-4">
          
            <label for="name1" class="-label">Name:</label>
            <input class="form-control" type="text" id="name5" name="name5"  value="<?php echo $data['name5']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="relation1" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation5" name="relation5" value="<?php echo $data['relation5']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="contact1" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact5" name="contact5" value="<?php echo $data['contact5']; ?>"    >
        </div>
    </div>
</div>               </div>
               <div class="col-md-12 ">
                    <div class="contact-box">
    <div class="row">
           <label >6. </label><br>
        <div class="col-md-4">
            <label for="name1" class="-label">Name:</label>
            <input class="form-control" type="text" id="name6" name="name6" value="<?php echo $data['name6']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="relation1" class="-label">Relation:</label>
            <input class="form-control" type="text" id="relation6" name="relation6" value="<?php echo $data['relation6']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="contact1" class="-label">Contact Number:</label>
            <input class="form-control" type="text" id="contact6" name="contact6" value="<?php echo $data['contact6']; ?>"    >
        </div>
    </div>
</div>
                   <div class="contact-box">
    <div class="row">
           <label >7. </label><br>
        <div class="col-md-4">
          
            <label for="name1">Name:</label>
            <input class="form-control" type="text" id="name7" name="name7"  value="<?php echo $data['name7']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="relation1">Relation:</label>
            <input class="form-control" type="text" id="relation7" name="relation7" value="<?php echo $data['relation7']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="contact1">Contact Number:</label>
            <input class="form-control" type="text" id="contact7" name="contact7" value="<?php echo $data['contact7']; ?>"   >
        </div>
    </div>
</div>
                   <div class="contact-box">
   
    <div class="row">
           <label >8. </label><br>
        <div class="col-md-4">
          
            <label for="name1">Name:</label>
            <input class="form-control" type="text" id="name8" name="name8" value="<?php echo $data['name8']; ?>"    >
        </div>
        <div class="col-md-4">
            <label for="relation1">Relation:</label>
            <input class="form-control" type="text" id="relation8" name="relation8"  value="<?php echo $data['relation8']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="contact1">Contact Number:</label>
            <input class="form-control" type="text" id="contact8" name="contact8"  value="<?php echo $data['contact8']; ?>"   >
        </div>
    </div>
</div>
                    <div class="contact-box">
    <div class="row">
           <label >9. </label><br>
        <div class="col-md-4">
            <label for="name1">Name:</label>
            <input class="form-control" type="text" id="name9" name="name9"  value="<?php echo $data['name9']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="relation1">Relation:</label>
            <input class="form-control" type="text" id="relation9" name="relation9" value="<?php echo $data['relation9']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="contact1">Contact Number:</label>
            <input class="form-control" type="text" id="contact9" name="contact9"  value="<?php echo $data['contact9']; ?>"   >
        </div>
    </div>
</div>
                     <div class="contact-box">
    <div class="row">
           <label >10. </label><br>
        <div class="col-md-4">
          
            <label for="name1">Name:</label>
            <input class="form-control" type="text" id="name10" name="name10" value="<?php echo $data['name10']; ?>"    >
        </div>
        <div class="col-md-4">
            <label for="relation1">Relation:</label>
            <input class="form-control" type="text" id="relation10" name="relation10" value="<?php echo $data['relation10']; ?>"   >
        </div>
        <div class="col-md-4">
            <label for="contact1">Contact Number:</label>
            <input class="form-control" type="text" id="contact10" name="contact10" value="<?php echo $data['contact10']; ?>"   >
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
           <input type="text" class="form-control" id="accountno" name="accountno" value="<?php echo $data['bank_account_no']; ?>"    >
         </div>
           <div class="form-group col-md-6 "   >
          <label for="address" class="-label">IFSC Code</label>
           <input type="text" class="form-control" id="ifsc" name="ifsc"  value="<?php echo $data['ifsc']; ?>"   >
        </div>
        </div>
          <div class="form-row">
         <div class="form-group col-md-6 ">
          <label for="address" class="-label">Bank Name</label>
           <input type="text" class="form-control" id="bname" name="bname"  value="<?php echo $data['bank_name']; ?>"   >
         </div>
        </div>
         <h2 class="contact-heading">Remarks</h2>
       <div class="form-row">
            <div class="form-group col-md-12">
                <textarea class="form-control" id="remarks" name="remarks" rows="4" cols="50"  ><?php echo $data['remarks']; ?></textarea>
            </div>
        </div>
        
        <!-- Display existing document link -->
        <?php if ($documentPath): ?>
            <p>View document: <a href="<?php echo $documentPath; ?>" target="_blank">Document Link</a></p>
        <?php endif; ?>
        
        <fieldset>
        <legend>Document Upload</legend>
        <div class="form-group">
          <label for="document" class="-label">Upload Documents</label>
          <input type="file" class="form-control-file" id="document" name="document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.heic,.xls,.ppt" >
          <small class="form-text text-muted">Upload documents such as ID proof, address proof, income proof, etc.</small>
        </div>
      </fieldset>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
<!--<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>-->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('form').on('submit', function(event) {
        event.preventDefault();
        
        $.ajax({
            type: 'POST',
            url: 'update_form.php',
            data: new FormData(this),  // Use FormData for file uploads
            contentType: false,
            cache: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    alert('Profile Update successful');
                    // Redirect to viewapplicantdetailsbm.php
                    // window.location.href = 'viewapplicantdetailsbm.php';
                } else {
                    alert(response.message); // Handle other statuses if needed
                }
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            }
        });
    });
});


<script>
    document.querySelector('form').addEventListener('submit', function (event) {
        event.preventDefault();

        var formData = new FormData(this);

        fetch('update_form.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    });
</script>
</script>

</body>
</html>