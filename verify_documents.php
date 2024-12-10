<?php
    session_start();
    if (!isset($_SESSION['username']) || !$_SESSION['role']) {
        // Redirect to the login page or another appropriate page
        header("Location: indexs.php");
        exit();
    }
    $username=$_SESSION['username'];
    $role=$_SESSION['role'];
    
    $redirecturl1='';
    $redirecturl2='';
    if($role=='admin'){
        $redirecturl1='dashboard.php';
        $redirecturl2='verify.php';
    }
    if($role=='branchmanager'){
        $redirecturl1='branchmanager.php';
        $redirecturl2='digitalverificationsbm.php';
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Review</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <style>
        /* Custom CSS */
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .container {
            margin: auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        div#sidebar-menu {
            position: fixed;
        }

        h2 {
            color: #333;
        }

        .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card img {
            border-bottom: 1px solid #ddd;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            height: 200px;
            object-fit: cover;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: #555;
            margin-bottom: 10px;
        }

        .form-control {
            margin-top: 10px;
            font-size: 0.9rem;
            border-radius: 5px;
        }

        .btn {
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Welcome !</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="<?php echo $redirecturl1; ?>">Dashboard</a></li>
                                            <li class="breadcrumb-item"><a href="<?php echo $redirecturl2; ?>">Verifications</a></li>
                                            <li class="breadcrumb-item active">Digital Verifications</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
    <?php
    include 'config.php';

    if (isset($_GET['id'])) {
        $leadID = $_GET['id'];
        $personQuery = $conn->query("SELECT FullName FROM personalinformation WHERE ID = $leadID");

        if ($personQuery && $personQuery->num_rows > 0) {
            $personData = $personQuery->fetch_assoc();
            $personName = $personData['FullName'];

            $documentQuery = $conn->query("SELECT * FROM documentcollection WHERE LeadID = $leadID");

            if ($documentQuery && $documentQuery->num_rows > 0) {
                $document = $documentQuery->fetch_assoc();

                $documentDisplayNames = [
                    'Document1' => 'Aadhar Card (Applicant)',
                    'Document2' => 'Pan Card (Applicant)',
                    'Document3' => '3 Cheque (Applicant)',
                    'Document4' => 'Aadhar Card (Nominee)',
                    'Document5' => 'Pan Card (Nominee)',
                    'Document6' => '3 Cheque (Nominee)',
                    'Document7' => 'Registree',
                    'Document8' => 'Fard',
                    'Document9' => 'Stamp Paper',
                    'Document10' => 'A/C Statement',
                    'Document11' => 'Old Registree',
                    'Document12' => 'Electricity Bill',
                    'Document13' => 'CIBIL Report',
                ];

                $documentColumns = [];
                for ($i = 1; $i <= 13; $i++) {
                    $columnName = 'Document' . $i;
                    if (!empty($document[$columnName])) {
                        $imagePath = 'uploads/' . basename($document[$columnName]);
                        $status = $document["Status$i"] ?? '';
                        $displayName = $documentDisplayNames[$columnName];

                        $documentColumns[$columnName] = [
                            'imagePath' => $imagePath,
                            'status' => $status,
                            'columnName' => $displayName,
                        ];
                    }
                }

                if (!empty($documentColumns)) {
                    echo '<h2 class="display-4 mb-4 text-center">Documents for ' . $personName . ' (Lead ID: ' . $leadID . ')</h2>';
                    echo '<form method="POST" action="success.php">';
                    echo '<div class="row">';
                    foreach ($documentColumns as $column => $data) {
                        echo '<div class="col-md-4 mb-4">';
                        echo '<div class="card shadow-sm">';
                        echo '<a href="' . $data['imagePath'] . '" target="_blank">';
                        echo '<img src="' . $data['imagePath'] . '" class="card-img-top img-fluid" alt="' . $column . '">';
                        echo '</a>';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">' . $data['columnName'] . '</h5>';
                        echo '<select name="status[' . $column . ']" class="form-control" onchange="handleStatusChange(this, \'' . $column . '\')">';
                        echo '<option value="Accepted"' . ($data['status'] == 'Accepted' ? ' selected' : '') . '>Accepted</option>';
                        echo '<option value="Rejected"' . ($data['status'] == 'Rejected' ? ' selected' : '') . '>Rejected</option>';
                        echo '<option value="Pending"' . ($data['status'] == 'Pending' ? ' selected' : '') . '>Pending</option>';
                        echo '</select>';
                        echo '<textarea name="remarks[' . $column . ']" class="form-control mt-2" style="display: none;" placeholder="Enter remarks for rejection"></textarea>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '<input type="hidden" name="leadID" value="' . $leadID . '">';
                    echo '<button type="submit" class="btn btn-success btn-lg btn-block">Submit</button>';
                    echo '</form>';
                } else {
                    echo '<div class="alert alert-warning text-center">No documents found for ID ' . $leadID . '</div>';
                }
            } else {
                echo '<div class="alert alert-danger text-center">No document found for ID ' . $leadID . '</div>';
            }
        } else {
            echo '<div class="alert alert-danger text-center">No person found for ID ' . $leadID . '</div>';
        }
    } else {
        echo '<div class="alert alert-danger text-center">No ID parameter specified</div>';
    }
    
    

    $conn->close();
    ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
function handleStatusChange(selectElement, column) {
    const remarksTextarea = selectElement.nextElementSibling;
    if (selectElement.value === 'Rejected') {
        remarksTextarea.style.display = 'block';
    } else {
        remarksTextarea.style.display = 'none';
        remarksTextarea.value = ''; // Clear the textarea if not rejected
    }
}
</script>
</body>
</html>
