<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Information Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
            color: #1C84EE;
        }
        .table-wrapper {
            width: 100%;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table-wrapper table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-wrapper th, .table-wrapper td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
            color: #333;
        }
        .table-wrapper th {
            background-color: #1C84EE;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .table-wrapper tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .highlight-chips {
            text-align: center;
            margin-top: 20px;
        }
        .highlight-chip {
            display: inline-block;
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border-radius: 20px;
            margin-right: 10px;
        }
        .table-scroll {
            max-height: 400px;
            overflow-y: auto;
        }
        .search-bar {
            margin: 20px auto;
            max-width: 600px;
            padding: 10px;
        }
        .search-bar input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
    </style>
    <script>
        function fetchTableData(query) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'fetch_data_admin.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('data-table').innerHTML = xhr.responseText;
                }
            };
            xhr.send('query=' + encodeURIComponent(query));
        }

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('search-input');
            searchInput.addEventListener('input', () => {
                const query = searchInput.value;
                fetchTableData(query);
            });

            // Fetch initial data
            fetchTableData('');
        });
    </script>
</head>
<body>
    <h1>Personal Information Details</h1>

    <!-- Highlight chips -->
    <div class="highlight-chips">
        <?php
        // Include the database connection file
        include 'config.php';

        // Query to count different lead categories
        $query_counts = "SELECT 
                            COUNT(CASE WHEN LoanPurpose = 'Personal Loan' THEN ID END) AS PersonalLoanCount,
                            COUNT(CASE WHEN LoanPurpose = 'Gold Loan' THEN ID END) AS GoldLoanCount,
                            COUNT(CASE WHEN LoanPurpose = 'Home Loan' THEN ID END) AS HomeLoanCount,
                            COUNT(CASE WHEN LoanPurpose = 'Loan Against Property' THEN ID END) AS LAPCount,
                            COUNT(*) AS TotalLeadsCount
                         FROM personalinformation";
        $result_counts = mysqli_query($conn, $query_counts);
        $counts_data = mysqli_fetch_assoc($result_counts);

        // Display the counts as highlight chips
        echo '<div class="highlight-chip">Total Leads: ' . $counts_data['TotalLeadsCount'] . '</div>';
        echo '<div class="highlight-chip">Personal Loan: ' . $counts_data['PersonalLoanCount'] . '</div>';
        echo '<div class="highlight-chip">Gold Loan: ' . $counts_data['GoldLoanCount'] . '</div>';
        echo '<div class="highlight-chip">Home Loan: ' . $counts_data['HomeLoanCount'] . '</div>';
        echo '<div class="highlight-chip">Loan Against Property: ' . $counts_data['LAPCount'] . '</div>';

        // Close the database connection
        mysqli_close($conn);
        ?>
    </div>

    <!-- Search bar -->
    <div class="search-bar">
        <input type="text" id="search-input" placeholder="Search by ID...">
    </div>

    <!-- Table to display personal information details -->
    <div class="table-wrapper">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Step Reached</th>
                        <th>Loan Amount</th>
                        <th>Loan Purpose</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody id="data-table">
                    <!-- Data will be loaded here via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
