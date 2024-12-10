<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Management System</title>
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        /* Primary Styles */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

      

        h1 {
            font-size: 1.4em;
        }

        em {
            font-style: normal;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Layout */
        .unique-layout {
            display: flex;
            /*width: 100%;*/
            /*min-height: 100vh;*/
        }

        .unique-content {
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
        }

        /* Sidebar */
        .unique-trigger {
            z-index: 2;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 4em;
            background: #192b3c;
        }

        .unique-trigger>i {
            display: inline-block;
            margin: 1.5em 0 0 1.5em;
            color:#007bff;
        }

        .unique-nav {
            position: fixed;
            top: 0;
            left: -15em;
            overflow: hidden;
            transition: all .3s ease-in;
            width: 15em;
            height: 100%;
            background: black;
            color: rgba(255, 255, 255, 0.7);
            z-index: 1; /* Ensure sidebar is above content */
        }

        .unique-nav:hover,
        .unique-nav:focus,
        .unique-trigger:focus+.unique-nav,
        .unique-trigger:hover+.unique-nav {
            left: 0;
        }

        .unique-nav ul {
            position: absolute;
            top: 4em;
            left: 0;
            margin: 0;
            padding: 0;
            width: 15em;
        }

        .unique-nav ul li {
            width: 100%;
        }

        .unique-nav-link {
            position: relative;
            display: block;
            width: 100%;
            height: 4em;
            padding: 15px;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.7);
        }

        .unique-nav-link em {
            position: absolute;
            top: 50%;
            left: 4em;
            transform: translateY(-50%);
        }

        .unique-nav-link:hover {
            background: #4d6276;
            color: #fff;
        }

        .unique-nav-link>i {
            position: absolute;
            top: 0;
            left: 0;
            display: inline-block;
            width: 4em;
            height: 4em;
            text-align: center;
            line-height: 4em;
            color: #007bff;
        }

        .unique-nav-link>i::before {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Mobile First */
        @media (min-width: 42em) {
            .unique-content {
                margin-left: 4em;
            }

            /* Sidebar */
            .unique-trigger {
                width: 4em;
            }

            .unique-nav {
                width: 4em;
                left: 0;
            }

            .unique-nav:hover,
            .unique-nav:focus,
            .unique-trigger:hover+.unique-nav,
            .unique-trigger:focus+.unique-nav {
                width: 15em;
            }
        }

        @media (min-width: 68em) {
            .unique-content {
                margin-left: 15em;
            }

            /* Sidebar */
            .unique-trigger {
                display: none
            }

            .unique-nav {
                width: 15em;
            }

            .unique-nav ul {
                top: 1.3em;
            }
        }
    </style>
</head>

<body>
    <div class="unique-layout">
        <!-- Sidebar -->
        <div class="unique-layout__sidebar">
            <a class="unique-trigger" href="#0">
                <i class="fa fa-bars"></i>
            </a>

            <nav class="unique-nav">
                <ul>
                    <li>
                        <a class="unique-nav-link" href="index.php">
                            <i class="fa fa-home"></i><em>Home</em>
                        </a>
                    </li>
                    <li>
                        <a class="unique-nav-link" href="lead.php">
                            <i class="fa fa-user"></i><em>Leads</em>
                        </a>
                    </li>
                    <li>
                        <a class="unique-nav-link" href="hot_leads.php">
                            <i class="fa fa-user"></i><em>Hot Leads</em>
                        </a>
                    </li>
                  
                   <li>
                        <a class="unique-nav-link" href="verify.php">
                            <i class="fa fa-user"></i><em>Verification</em>
                        </a>
                    </li>
                    <li>
                        <a class="unique-nav-link" href="salesstatus.php">
                            <i class="fa fa-user"></i><em>Sales Department</em>
                        </a>
                    </li>
                     <li>
                        <a class="unique-nav-link" href="accounts.php">
                            <i class="fa fa-user"></i><em> Validators </em>
                        </a>
                    </li>
                      <li>
                        <a class="unique-nav-link" href="approved_loans.php">
                            <i class="fa fa-user"></i><em> Approved Loans </em>
                        </a>
                    </li>
                    <li>
                        <a class="unique-nav-link" href="calculator.php">
                            <i class="fa fa-user"></i><em> Calculator </em>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Include JavaScript libraries -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // JavaScript code here
    </script>
</body>

</html>
