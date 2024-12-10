<?php
// Include database configuration file
include 'config.php'; // Assuming this file contains database connection logic

$query = isset($_GET['query']) ? $_GET['query'] : '';

// Fetch evaluation reports based on the search query
$searchQuery = $conn->prepare("SELECT er.report_id, er.lead_id, er.evaluator_name, er.report_file, er.status, er.remarks, pi.FullName 
                               FROM evaluation_reports er
                               JOIN personalinformation pi ON er.lead_id = pi.ID
                               WHERE er.evaluator_name LIKE ? OR pi.FullName LIKE ? OR er.lead_id LIKE ? OR er.report_id LIKE ?
                               ORDER BY er.created_at DESC");
$searchTerm = "%$query%";
$searchQuery->bind_param('ssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$searchQuery->execute();
$result = $searchQuery->get_result();

$evaluationReports = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $evaluationReports[] = $row;
    }
}
$searchQuery->close();

$conn->close();
?>

<?php if (!empty($evaluationReports)) : ?>
    <?php foreach ($evaluationReports as $report) : ?>
        <div class="list-group-item">
            <!-- General Details -->
            <div class="lead-info">
                <strong>ID:</strong> <?php echo $report['report_id']; ?>
            </div>
            <div class="lead-info">
                <strong>Lead ID:</strong> <?php echo $report['lead_id']; ?>
            </div>
            <div class="lead-info">
                <strong>Person Name:</strong> <?php echo $report['FullName']; ?>
            </div>
            
            <div class="row">
                <!-- Applicant Details -->
                <div class="col-md-12">
                    <div class="lead-info">
                        <strong>Evaluator Name:</strong> <?php echo $report['evaluator_name']; ?>
                    </div>
                    <div class="lead-info">
                        <strong>Evaluation Report:</strong> <a href="<?php echo $report['report_file']; ?>" target="_blank">View Report</a>
                    </div>
                    <div class="lead-info">
                        <strong>Status:</strong> <?php echo $report['status']; ?>
                    </div>
                    <?php if ($report['status'] == 'Rejected') : ?>
                        <div class="lead-info">
                            <strong>Remarks:</strong> <?php echo $report['remarks']; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Update Status Form for Applicant -->
                    <form method="post" class="mt-2">
                        <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                        <div class="form-group">
                            <label for="status_<?php echo $report['report_id']; ?>">Status</label>
                            <select class="form-control" id="status_<?php echo $report['report_id']; ?>" name="status" onchange="toggleRemarks(this, <?php echo $report['report_id']; ?>)">
                                <option value="Pending" <?php echo $report['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Approved" <?php echo $report['status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="Rejected" <?php echo $report['status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="form-group" id="remarks_group_<?php echo $report['report_id']; ?>" style="display: <?php echo $report['status'] == 'Rejected' ? 'block' : 'none'; ?>;">
                            <label for="remarks_<?php echo $report['report_id']; ?>">Remarks</label>
                            <textarea class="form-control" id="remarks_<?php echo $report['report_id']; ?>" name="remarks"><?php echo $report['remarks']; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else : ?>
    <div class="alert alert-info" role="alert">No evaluation reports found.</div>
<?php endif; ?>
