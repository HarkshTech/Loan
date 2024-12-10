<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Retrieval</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .accept-btn {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .reject-btn {
            background-color: #dc3545;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php
    // Include the configuration file
    include 'config.php';

    // Modified SQL query with JOIN clause and specific columns
    $sql = "SELECT VerificationForms.*, documentcollection.ID, documentcollection.LeadID,
            documentcollection.Document1, documentcollection.Status1,
            documentcollection.Document2, documentcollection.Status2,
            documentcollection.Document3, documentcollection.Status3,
            documentcollection.Document4, documentcollection.Status4,
            documentcollection.Document5, documentcollection.Status5,
            documentcollection.Document6, documentcollection.Status6,
            documentcollection.Document7, documentcollection.Status7,
            documentcollection.Document8, documentcollection.Status8,
            documentcollection.Document9, documentcollection.Status9,
            documentcollection.Document10, documentcollection.Status10
            FROM VerificationForms 
            INNER JOIN documentcollection 
            ON VerificationForms.leadID = documentcollection.LeadID";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr>
        <th> leadID</th>
                  <th> ID</th>
                  <th>Verifier Name (Home)</th>
                  <th>Verification Status (Home)</th>
                  <th>Verifier Name (Business)</th>
                  <th>Verification Status (Business)</th>
                   
                  <th>Lead ID</th>
                  <th>Document 1</th>
                  <th>Status 1</th>
                  <th>Document 2</th>
                  <th>Status 2</th>
                  <th>Document 3</th>
                  <th>Status 3</th>
                  <th>Document 4</th>
                  <th>Status 4</th>
                  <th>Document 5</th>
                  <th>Status 5</th>
                  <th>Document 6</th>
                  <th>Status 6</th>
                  <th>Document 7</th>
                  <th>Status 7</th>
                  <th>Document 8</th>
                  <th>Status 8</th>
                  <th>Document 9</th>
                  <th>Status 9</th>
                  <th>Document 10</th>
                  <th>Status 10</th>
                  <th>Action</th>
              </tr>";
      while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
       echo "<td>{$row['leadID']}</td>";
    echo "<td>{$row['verifierName_Home']}</td>";
    echo "<td>{$row['verificationStatus_Home']}</td>";
    echo "<td>{$row['verifierName_Business']}</td>";
    echo "<td>{$row['verificationStatus_Business']}</td>";

    echo "<td>{$row['LeadID']}</td>";
    echo "<td>{$row['Document1']}</td>";
    echo "<td>{$row['Status1']}</td>";
    echo "<td>{$row['Document2']}</td>";
    echo "<td>{$row['Status2']}</td>";
    echo "<td>{$row['Document3']}</td>";
    echo "<td>{$row['Status3']}</td>";
    echo "<td>{$row['Document4']}</td>";
    echo "<td>{$row['Status4']}</td>";
    echo "<td>{$row['Document5']}</td>";
    echo "<td>{$row['Status5']}</td>";
    echo "<td>{$row['Document6']}</td>";
    echo "<td>{$row['Status6']}</td>";
    echo "<td>{$row['Document7']}</td>";
    echo "<td>{$row['Status7']}</td>";
    echo "<td>{$row['Document8']}</td>";
    echo "<td>{$row['Status8']}</td>";
    echo "<td>{$row['Document9']}</td>";
    echo "<td>{$row['Status9']}</td>";
    echo "<td>{$row['Document10']}</td>";
    echo "<td>{$row['Status10']}</td>";
    
    echo "<td>
            <button onclick='acceptFunction({$row['id']})' class='accept-btn'>Accept</button>
            <button onclick='rejectFunction({$row['id']})' class='reject-btn'>Reject</button>
         </td>";

    echo "</tr>";
}

        echo "</table>";
    } else {
        echo "No data found";
    }

    $conn->close();
    ?>
</body>
</html>
