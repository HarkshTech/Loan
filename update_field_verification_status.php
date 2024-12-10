<?php
// Include database connection
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the form data
    $leadID = $_POST['leadID'];
    
    // Check which form is submitted and update accordingly
    if (isset($_POST['home_status_applicant'])) {
        // Applicant Form Submitted
        $homeStatusApplicant = $_POST['home_status_applicant'];
        $homeVerificationNotesApplicant = $_POST['home_verification_notes_applicant'];
        $businessStatusApplicant = $_POST['business_status_applicant'];
        $businessVerificationNotesApplicant = $_POST['business_verification_notes_applicant'];

        // Update Applicant Data
        $sql = "UPDATE VerificationForms SET 
                    verificationStatus_Home = ?, 
                    verificationNotes_Home = ?, 
                    verificationStatus_Business = ?, 
                    businessVerificationNotes = ?
                WHERE leadID = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Bind the parameters
            $stmt->bind_param("ssssi", $homeStatusApplicant, $homeVerificationNotesApplicant, $businessStatusApplicant, $businessVerificationNotesApplicant, $leadID);

            // Execute the query
            if ($stmt->execute()) {
                echo "Applicant status updated successfully!";
            } else {
                echo "Error updating Applicant status: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement for Applicant: " . $conn->error;
        }
    }

    if (isset($_POST['home_status_coapp'])) {
        // Co-Applicant Form Submitted
        $homeStatusCoApp = $_POST['home_status_coapp'];
        $homeVerificationNotesCoApp = $_POST['home_verification_notes_coapp'];
        $businessStatusCoApp = $_POST['business_status_coapp'];
        $businessVerificationNotesCoApp = $_POST['business_verification_notes_coapp'];

        // Update Co-Applicant Data
        $sql = "UPDATE VerificationForms SET 
                    verificationStatus_Home_COAPP = ?, 
                    verificationNotes_Home_COAPP = ?, 
                    verificationStatus_Business_COAPP = ?, 
                    businessVerificationNotes_COAPP = ?
                WHERE leadID = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Bind the parameters
            $stmt->bind_param("ssssi", $homeStatusCoApp, $homeVerificationNotesCoApp, $businessStatusCoApp, $businessVerificationNotesCoApp, $leadID);

            // Execute the query
            if ($stmt->execute()) {
                echo "Co-Applicant status updated successfully!";
            } else {
                echo "Error updating Co-Applicant status: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement for Co-Applicant: " . $conn->error;
        }
    }

    // Redirect back to the page after successful update
    header("Location: {$_SERVER['HTTP_REFERER']}");
    exit();
}
?>
