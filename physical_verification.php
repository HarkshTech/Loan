<?php
include 'config.php';


// Check if the ID parameter is set in the URL
if (isset($_GET['ID'])) {
    $leadID = $_GET['ID'];

    // Fetch the corresponding row from the database
    $documentQuery = $conn->query("SELECT * FROM documentcollection WHERE LeadID = $leadID");

    // Check if the query was successful and fetch the row
    if ($documentQuery && $documentQuery->num_rows > 0) {
        $document = $documentQuery->fetch_assoc();

        // Now, dynamically get the document column based on the LeadID
        $documentColumns = [];
        for ($i = 1; $i <= 10; $i++) {
            $columnName = 'Document' . $i;
            if (!empty($document[$columnName])) {
                // Construct the file path to the image in the uploads folder
                $imagePath = 'uploads/' . basename($document[$columnName]);
                $documentColumns[$columnName] = $imagePath;
            }
        }

        // Display the documents retrieved as images with dropdowns for acceptance/rejection
        if (!empty($documentColumns)) {
            echo '<h2 style="font-size: 24px;">Documents for Lead ID ' . $leadID . '</h2>';
            echo '<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">';
            foreach ($documentColumns as $column => $value) {
                echo '<div style="margin-bottom: 10px;">';
                echo '<img src="' . $value . '" alt="' . $column . '" style="max-width: 200px; max-height: 200px; border: 1px solid #ccc; border-radius: 5px; margin-right: 10px;">'; // Set your desired max-width and max-height values
                echo '<select name="status[' . $column . ']" style="padding: 5px;">';
                echo '<option value="Accepted">Accepted</option>';
                echo '<option value="Rejected">Rejected</option>';
                echo '</select>';
                echo '</div>';
            }
            echo '<input type="hidden" name="leadID" value="' . $leadID . '">';
            echo '<button type="submit" class="btn btn-primary" name="submit">Submit</button>';
            echo '</form>';

            // Process form submission
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['status']) && isset($_POST['leadID'])) {
                $leadID = $_POST['leadID'];
                $statuses = $_POST['status'];

                // Update verification_data table with the status values
                $updateSql = "UPDATE verification_data SET ";
                foreach ($statuses as $column => $status) {
                    $updateSql .= "$column = '$status', ";
                }
                // Remove the trailing comma and space
                $updateSql = rtrim($updateSql, ', ');

                $updateSql .= " WHERE leadID = $leadID";

                if ($conn->query($updateSql) === TRUE) {
                    echo "<script>alert('Data updated successfully!');</script>";
                } else {
                    echo "<script>alert('Error updating data: " . $conn->error . "');</script>";
                }
            }
        } else {
            echo 'No documents found for ID ' . $leadID;
        }
    } else {
        echo 'No document found for ID ' . $leadID;
    }
} else {
    echo 'No ID parameter specified';
}

$conn->close();
?>
