<?php 
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Borrowers</title>
    <style>
      .box {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    body{
        width:100%;
        margin:0 auto;
        padding-top:30px;
    }

    .col {
        flex-basis: calc(50% - 10px);
        margin-bottom: 15px;
    }

    .col:nth-child(2n) {
        margin-left: 20px;
    }
        .container {
            padding: 0 20px; 
        }

        .row {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between; 
        }

        .col {
            flex-basis: calc(50% - 10px); 
            margin-bottom: 15px;
        }

        .col:nth-child(2n) {
            margin-left: 20px; 
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="date"],
        input[type="file"],
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="radio"] {
            margin-right: 5px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #1C84EE;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #1C84EE;
        }

        .divider {
            width: 100%;
            height: 1px;
            background-color: #ccc;
            margin-bottom: 15px;
        }

        .contact-box {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 15px;
        }

        .contact-heading {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .risk-assessment label {
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .col {
                flex-basis: 100%; 
                margin-left: 0; 
            }
        }
    </style>
</head>
<body>
   
<div class="container">
      <form action="formdata.php" method="POST" style="">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <label for="srno">Sr. No:</label>
                    <input type="text" id="srno" name="srno">
                </div>
                <div class="col-lg-6">
                    <label for="datepicker">Date:</label>
                    <input type="date" id="datepicker" name="datepicker">
                </div>
                <div class="col">
                    <label for="photo">Upload Photo:</label>
                    <input type="file" id="photo" name="photo">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="amount">Amount Required:</label>
                    <input type="text" id="amount" name="amount">
                </div>
                <div class="col">
                    <label for="type">Type of Case:</label>
                    <select id="type" name="type">
                        <option value="gold">Gold Loan</option>
                        <option value="home">Home Loan</option>
                        <option value="personal">Personal Loan</option>
                        <option value="lap">LAP </option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name">
                </div>
                <div class="col">
                    <label for="refby">Ref By:</label>
                    <input type="text" id="refby" name="refby">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="address">Current Address:</label>
                    <input type="text" id="address" name="address">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="cs_app">CS.App (CIBIL/EXP/EQI/CRIF):</label>
                    <input type="text" id="cs_app" name="cs_app">
                </div>
                <div class="col">
                    <label for="co_app">CO.App (CIBIL/EXP/EQI/CRIF):</label>
                    <input type="text" id="co_app" name="co_app">
                </div>
            </div>
               <div class="row">
                <div class="col">
                    <label for="app_name">App. Name</label>
                    <input type="text" id="app_name" name="app_name">
                </div>
                <div class="col">
                    <label for="co_app_name">CO-App. Name:</label>
                    <input type="text" id="co_app_name" name="co_app_name">
                </div>
            </div>
            <div class="divider"></div>
            <div class="row">
                <div class="col">
                    <label for="work_details">Work Details:</label>
                    <input type="text" id="work_details" name="work_details">
                </div>
                <div class="col">
                    <label for="co_work_details">Work Details (CO-Applicant):</label>
                    <input type="text" id="co_work_details" name="co_work_details">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label>Ownership Status:</label><br>
                    <input type="radio" id="owned" name="ownership_status" value="self_owned">
                    <label for="owned">Self Owned</label>
                    <input type="radio" id="rented" name="ownership_status" value="rented">
                    <label for="rented">Rented</label>
                </div>
                <div class="col">
                    <label>Ownership Status (CO-Applicant):</label><br>
                    <input type="radio" id="co_owned" name="co_ownership_status" value="self_owned">
                    <label for="co_owned">Self Owned</label>
                    <input type="radio" id="co_rented" name="co_ownership_status" value="rented">
                    <label for="co_rented">Rented</label>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="relation">Relation between Applicant and CO-Applicant:</label>
                    <input type="text" id="relation" name="relation">
                </div>
                <div class="col">
                    <label for="monthly_income">Monthly Income (CO-Applicant):</label>
                    <input type="text" id="monthly_income" name="monthly_income">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="can_pay_installment">Can Pay Installment:</label>
                    <input type="text" id="can_pay_installment" name="can_pay_installment">
                </div>
                <div class="col">
                    <label for="reason_of_loan">Reason Of Loan:</label>
                    <input type="text" id="reason_of_loan" name="reason_of_loan">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="observation">Can Be Settled for (Observation):</label>
                    <input type="text" id="observation" name="observation">
                </div>
            </div>
            <div class="divider"></div>
            <!-- Contact Information heading -->
            <div class="row">
                <div class="col">
                    <h2 class="contact-heading">Contact Information</h2>
                </div>
            </div>
          
            <!-- Remaining contact boxes -->
            <div class="row">
                <div class="col">
                    <div class="contact-box">
                        <label>1.</label><br>
                        <input type="text" id="contact1" name="contact1">
                    </div>
                    <div class="contact-box">
                        <label>2. </label><br>
                        <input type="text" id="contact2" name="contact2">
                    </div>
                    <div class="contact-box">
                        <label>3. </label><br>
                        <input type="text" id="contact3" name="contact3">
                    </div>
                </div>
                <div class="col">
                    <div class="contact-box">
                        <label>4. </label><br>
                        <input type="text" id="contact4" name="contact4">
                    </div>
                    <div class="contact-box">
                        <label>5. </label><br>
                        <input type="text" id="contact5" name="contact5">
                    </div>
                    <div class="contact-box">
                        <label>6. </label><br>
                        <input type="text" id="contact6" name="contact6">
                    </div>
                </div>
            </div>
            
            
              <!-- Risk Assessment radio buttons -->
            <div class="row risk-assessment">
                <div class="col">
                    <label>Risk Assessment:</label><br>
                    <input type="radio" id="low-risk" name="risk_assessment" value="low">
                    <label for="low-risk">Low</label>
                    <input type="radio" id="medium-risk" name="risk_assessment" value="medium">
                    <label for="medium-risk">Medium</label>
                    <input type="radio" id="high-risk" name="risk_assessment" value="high">
                    <label for="high-risk">High</label>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h3>CHECKLIST:</h3>
                    <ul>
                    <input type="checkbox" id="adhar_a" name="adhar_a"><label for="adhar_a">&nbsp;&nbsp;&nbsp;AADHAR CARD (A)</label><br>
                       <input type="checkbox" id="pan_a" name="pan_a"><label for="pan_a"> &nbsp;&nbsp;&nbsp;PAN CARD (A)</label><br>
                       <input type="checkbox" id="cheque_a" name="cheque_a"><label for="cheque_a">&nbsp;&nbsp;&nbsp; 3 CHEQUE (A)</label><br>
                        <input type="checkbox" id="adhar_n" name="adhar_n"><label for="adhar_n"> &nbsp;&nbsp;&nbsp;AADHAR CARD (N)</label><br>
                        <input type="checkbox" id="pan_n" name="pan_n"><label for="pan_n"> &nbsp;&nbsp;&nbsp; PAN CARD (N)</label><br>
                        <input type="checkbox" id="cheque_n" name="cheque_n"><label for="cheque_n"> &nbsp;&nbsp;&nbsp; 3 CHEQUE (N)</label><br>
                       <input type="checkbox" id="registree" name="registree"><label for="registree"> &nbsp;&nbsp;&nbsp; REGISTREE</label><br>
                       <input type="checkbox" id="fard" name="fard"><label for="fard">&nbsp;&nbsp;&nbsp; FARD</label><br>
                       <input type="checkbox" id="stamp_paper" name="stamp_paper"><label for="stamp_paper">&nbsp;&nbsp;&nbsp; STAMP PAPER</label><br>
                       <input type="checkbox" id="ac_statement" name="ac_statement"><label for="ac_statement"> &nbsp;&nbsp;&nbsp;A/C STATEMENT</label><br>
                        <input type="checkbox" id="old_registree" name="old_registree"><label for="old_registree"> &nbsp;&nbsp;&nbsp;OLD REGISTREE</label><br>
                       <input type="checkbox" id="electricity_bill" name="electricity_bill"><label for="electricity_bill"> &nbsp;&nbsp;&nbsp;ELECTRICITY BILL</label><br>
                    </ul>
                </div>
                <div class="col">
                    <h3>Verification Details:Physical/legal/valuation</h3>
                    <table>
                        <h3>Physical:</h3><br>
                        <tr>
                            <th></th>
                            <th>Home</th>
                            <th>Business</th>
                            <th>Changes</th>
                        </tr>
                      <tr>
    <td>Date of Verification</td>
    <td><input type="date" id="home_verification_date" name="home_verification_date"></td>
    <td><input type="date" id="business_verification_date" name="business_verification_date"></td>
    <td><input type="date" id="changes_verification_date" name="changes_verification_date"></td>
</tr>

                        <tr>
                            <td>Name of Verification Officer</td>
                            <td><input type="text" id="home_verification_officer" name="home_verification_officer1"></td>
                            <td><input type="text" id="business_verification_officer" name="business_verification_officer1"></td>
                            <td><input type="text" id="changes_verification_officer" name="changes_verification_officer1"></td>
                        </tr>
                    </table><br>
                    <table>
                        <h3>Legal:</h3><br>
                        <tr>
                            <th></th>
                            <th>Home</th>
                            <th>Business</th>
                            <th>Changes</th>
                        </tr>
                        <tr>
                            <td>Date of Verification</td>
                            <td><input type="date" id="home_verification_date" name="home_verification_date2"></td>
                            <td><input type="date" id="business_verification_date" name="business_verification_date2"></td>
                            <td><input type="date" id="changes_verification_date" name="changes_verification_date2"></td>
                        </tr>
                        <tr>
                            <td>Name of Verification Officer</td>
                            <td><input type="text" id="home_verification_officer" name="home_verification_officer2"></td>
                            <td><input type="text" id="business_verification_officer" name="business_verification_officer2"></td>
                            <td><input type="text" id="changes_verification_officer" name="changes_verification_officer2"></td>
                        </tr>
                    </table>
                    <table>
                        <h3>Valuation:</h3><br>
                        <tr>
                            <th></th>
                            <th>Home</th>
                            <th>Business</th>
                            <th>Changes</th>
                        </tr>
                        <tr>
                            <td>Date of Verification</td>
                            <td><input type="date" id="home_verification_date" name="home_verification_date3"></td>
                            <td><input type="date" id="business_verification_date" name="business_verification_date3"></td>
                            <td><input type="date" id="changes_verification_date" name="changes_verification_date3"></td>
                        </tr>
                        <tr>
                            <td>Name of Verification Officer</td>
                            <td><input type="text" id="home_verification_officer" name="home_verification_officer3"></td>
                            <td><input type="text" id="business_verification_officer" name="business_verification_officer3"></td>
                            <td><input type="text" id="changes_verification_officer" name="changes_verification_officer3"></td>
                        </tr>
                    </table>
                </div>
            </div>
      <div class="row">
    <div class="col">
        <label for="remarks">Remarks (Office Use):</label>
        <div class="box">
            <textarea id="remarks" name="remarks" rows="4" style="width: 100%;height: 520px;"></textarea>
        </div>
    </div>
    <div class="col">
        <div class="box">
            <div class="row">
                <div class="col">
                    <label for="additional_details">Additional Details of or by Customer:</label>
                    <div class="box">
            <textarea id="remarks" name="remarks2" rows="4" style="width: 100%;height: 150px;"></textarea>
        </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="installment_amount">Details of Installment and Tenure Finalized:</label>
                    <div class="contact-box">
                        <label for="installment_amount">Installment Amount:</label>
                        <input type="text" id="installment_amount" name="installment_amount">
                    </div>
                    <div class="contact-box">
                        <label for="tenure_finalized">Tenure Finalized:</label>
                        <input type="text" id="tenure_finalized" name="tenure_finalized">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



            <input type="submit" value="Submit">
             
      <button onclick="window.print();return false;"  style="background:#1C84EE;border-radius:5px;color:white;width:5%;border:2px solid #1C84EE;">Print</button>  
        
    </div>
        
        
    </form>
</div>
    
</body>
</html>
