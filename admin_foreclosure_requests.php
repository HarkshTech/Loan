<?php
// Database connection
include 'leftside.php';
include 'config.php';

// Handle form submission for approval or rejection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $requestID = $_POST['requestID'];
    $leadID = $_POST['leadID'];
    $action = $_POST['action'];
    $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : '';

    if ($action == 'approve') {
        $sql = "UPDATE foreclosure_requests SET Status='Approved' WHERE ID='$requestID' AND LeadID='$leadID'";
        
        $insertsql="UPDATE personalinformation SET LoanStatus='ForeClosed' WHERE ID=$leadID";
    } elseif ($action == 'reject') {
        $sql = "UPDATE foreclosure_requests SET Status='Rejected', Remarks='$remarks' WHERE ID='$requestID' AND LeadID='$leadID'";
    }

    if ($conn->query($sql) === TRUE) {
        if ($action == 'approve') {
            echo "Foreclosure request approved successfully";
        } elseif ($action == 'reject') {
            echo "Foreclosure request rejected successfully";
        }
        $conn->query($insertsql);
        exit; // Ensure no further output after successful processing
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
        exit; // Ensure no further output after error message
    }
}

// Fetch pending foreclosure requests
$sql = "SELECT fr.ID, fr.LeadID, pi.FullName, fr.ForeclosureCharges, fr.TotalForeclosureAmount, fr.RequestDate
        FROM foreclosure_requests fr
        JOIN personalinformation pi ON fr.LeadID = pi.ID
        WHERE fr.Status = 'Pending'";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Foreclosure Requests</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container" style="margin-top:100px;">
    <h1 class="mt-5">Admin Foreclosure Requests</h1>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Lead ID</th>
                <th>Full Name</th>
                <th>Foreclosure Charges</th>
                <th>Total Foreclosure Amount</th>
                <th>Request Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['ID']}</td>
                        <td>{$row['LeadID']}</td>
                        <td>{$row['FullName']}</td>
                        <td>{$row['ForeclosureCharges']}</td>
                        <td>{$row['TotalForeclosureAmount']}</td>
                        <td>{$row['RequestDate']}</td>
                        <td>
                            <button class='btn btn-success approve-btn' data-id='{$row['ID']}' data-leadid='{$row['LeadID']}'>Approve</button>
                            <button class='btn btn-danger reject-btn' data-id='{$row['ID']}' data-leadid='{$row['LeadID']}' data-toggle='modal' data-target='#rejectModal'>Reject</button>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No pending requests found</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="rejectForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Foreclosure Request</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rejectRequestID" name="requestID">
                    <input type="hidden" id="rejectLeadID" name="leadID">
                    <input type="hidden" name="action" value="reject">
                    <div class="form-group">
                        <label for="remarks">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.approve-btn').on('click', function() {
        var requestID = $(this).data('id');
        var leadID = $(this).data('leadid');
        $.post('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', { requestID: requestID, leadID: leadID, action: 'approve' }, function(response) {
            alert(response);
            location.reload();
        });
    });

    $('#rejectForm').on('submit', function(event) {
        event.preventDefault(); // Prevent default form submission
        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function(response) {
                alert(response);
                $('#rejectModal').modal('hide'); // Hide modal on success
                location.reload(); // Reload page to update table
            },
            error: function(xhr, status, error) {
                alert('Error: ' + xhr.responseText); // Show error message if any
            }
        });
    });
});
</script>

</body>
</html>
