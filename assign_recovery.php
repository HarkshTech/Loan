<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emi_id = $_POST['emi_id'];
    $recovery_officer = $_POST['recovery_officer'];
    $assigned_date = date('Y-m-d');
    $case_status = 'Pending';

    // Get LeadID from emi_schedule based on emi_id
    $sqlGetLeadID = "SELECT LeadID FROM emi_schedule WHERE ID = ?";
    $stmt = $conn->prepare($sqlGetLeadID);
    if ($stmt) {
        $stmt->bind_param("i", $emi_id);
        $stmt->execute();
        $stmt->bind_result($leadID);
        $stmt->fetch();
        $stmt->close();

        // Check if recovery data already exists for this LeadID
        $sqlCheckRecovery = "SELECT ID FROM recovery_data WHERE LeadID = ?";
        $stmtCheck = $conn->prepare($sqlCheckRecovery);
        if ($stmtCheck) {
            $stmtCheck->bind_param("i", $leadID);
            $stmtCheck->execute();
            $stmtCheck->store_result();
            
            if ($stmtCheck->num_rows > 0) {
                // If recovery exists, update the AssignedTo column
                $sqlUpdateRecovery = "UPDATE recovery_data 
                                      SET AssignedTo = ?, AssignedDate = ?, CaseStatus = ? 
                                      WHERE LeadID = ?";
                $stmtUpdate = $conn->prepare($sqlUpdateRecovery);
                if ($stmtUpdate) {
                    $stmtUpdate->bind_param("sssi", $recovery_officer, $assigned_date, $case_status, $leadID);
                    if ($stmtUpdate->execute()) {
                        // Update RecoveryAssigning in emi_schedule
                        $sqlUpdateOverdue = "UPDATE emi_schedule 
                                             SET RecoveryAssigning = 'yes' 
                                             WHERE LeadID = ?";
                        $stmtUpdateOverdue = $conn->prepare($sqlUpdateOverdue);
                        $stmtUpdateOverdue->bind_param("i", $leadID);
                        $stmtUpdateOverdue->execute();
                        
                        header('Location: recovery_assigning.php?status=success');
                    } else {
                        $error = $stmtUpdate->error;
                        header("Location: recovery_assigning.php?status=error&message=" . urlencode($error));
                    }
                    $stmtUpdate->close();
                } else {
                    $error = $conn->error;
                    header("Location: recovery_assigning.php?status=error&message=" . urlencode($error));
                }
            } else {
                // If recovery does not exist, insert a new row
                $sqlInsertRecovery = "INSERT INTO recovery_data (LeadID, Overdue_days, No_of_EMIs_bounced, AssignedTo, AssignedDate, VisitNeeded, CaseStatus, NextSteps, Remarks)
                                      SELECT LeadID, overdue_days, (TotalEMIs - PaidEMIs) as No_of_EMIs_bounced, ?, ?, '', ?, '', ''
                                      FROM emi_schedule
                                      WHERE ID = ?";
                $stmtInsert = $conn->prepare($sqlInsertRecovery);
                if ($stmtInsert) {
                    $stmtInsert->bind_param("sssi", $recovery_officer, $assigned_date, $case_status, $emi_id);
                    if ($stmtInsert->execute()) {
                        // Update RecoveryAssigning in emi_schedule
                        $sqlUpdateOverdue = "UPDATE emi_schedule 
                                             SET RecoveryAssigning = 'yes' 
                                             WHERE LeadID = ?";
                        $stmtUpdateOverdue = $conn->prepare($sqlUpdateOverdue);
                        $stmtUpdateOverdue->bind_param("i", $leadID);
                        $stmtUpdateOverdue->execute();

                        header('Location: recovery_assigning.php?status=success');
                    } else {
                        $error = $stmtInsert->error;
                        header("Location: recovery_assigning.php?status=error&message=" . urlencode($error));
                    }
                    $stmtInsert->close();
                } else {
                    $error = $conn->error;
                    header("Location: recovery_assigning.php?status=error&message=" . urlencode($error));
                }
            }
            $stmtCheck->close();
        } else {
            $error = $conn->error;
            header("Location: recovery_assigning.php?status=error&message=" . urlencode($error));
        }
    } else {
        $error = $conn->error;
        header("Location: recovery_assigning.php?status=error&message=" . urlencode($error));
    }
}

$conn->close();
?>
