<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Loan Calculator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
        crossorigin="anonymous">
    <style>
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 2px solid deepskyblue;
            padding: 40px;
            border-radius: 20px;
            width: 30rem;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 100px;
        }

        .form-input {
            display: flex;
            align-items: center;
            border: 2px solid deepskyblue;
            width: 100%;
            height: 2.5rem;
            background: white;
            border-radius: 5px;
            margin: 10px 0;
            padding: 0 10px;
        }

        .input {
            height: 100%;
            width: 100%;
            border: none;
            outline: none;
            padding: 0 10px;
        }

        .input-icon {
            margin-right: 10px;
            color: deepskyblue;
        }

        .calculateBtn {
            height: 3rem;
            width: 100%;
            border: none;
            background: #1C84EE;
            color: white;
            font-size: 16px;
            border-radius: 7px;
            margin-top: 20px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .calculateBtn:hover {
            background: deepskyblue;
        }

        #result {
            margin-top: 20px;
            width: 100% !important;
            text-align: left;
            height: 150px !important;
        }

        #result h4 {
            margin: 5px 0;
        }

        @media (min-width: 1200px) {
            .container,
            .container-lg,
            .container-md,
            .container-sm,
            .container-xl,
            .container-xxl {
                max-width: 771px !important;
                margin-bottom: 50px !important;
            }
        }
    </style>
</head>

<body>
    <?php include 'leftside.php' ?>
    <div class="container">
        <h1>Loan Calculator</h1>
        <div class="form-input">
            <i class="fas fa-rupee-sign input-icon"></i>
            <input type="number" class="input" id="amount" placeholder="Loan Amount">
        </div>
        <div class="form-input">
            <i class="fas fa-percentage input-icon"></i>
            <input type="number" class="input" id="annual-interest" placeholder="Annual Interest Rate">
        </div>
        <div class="form-input">
            <i class="fas fa-percentage input-icon"></i>
            <input type="number" class="input" id="monthly-interest" placeholder="Monthly Interest Rate">
        </div>
        <div class="form-input">
            <i class="fas fa-calendar input-icon"></i>
            <input type="number" class="input" id="months" placeholder="Months to Repay">
        </div>
        <button class="calculateBtn" onclick="calculateLoan()">Calculate</button>
        <div id="result"></div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js"
        integrity="sha512-k6RqeWeci5ZR/Lv4MR0sA0FfDOMGz8rb92LFq4c5xz+1Jb8mlX0F1y8ry2Z4p6Ff1UPvCI0gzKLRSTdX3/a4lw=="
        crossorigin="anonymous"></script>
    <script>
        const calculateLoan = () => {
            const amount = parseFloat(document.getElementById("amount").value);
            const annualInterest = parseFloat(document.getElementById("annual-interest").value);
            const monthlyInterest = parseFloat(document.getElementById("monthly-interest").value);
            const months = parseFloat(document.getElementById("months").value);

            if (isNaN(amount) || isNaN(months)) {
                document.getElementById("result").innerHTML = "<h4>Please fill in the loan amount and months with valid numbers.</h4>";
                return;
            }

            if (isNaN(annualInterest) && isNaN(monthlyInterest)) {
                document.getElementById("result").innerHTML = "<h4>Please provide either the annual or monthly interest rate.</h4>";
                return;
            }

            let monthlyInterestRate;
            if (!isNaN(monthlyInterest) && monthlyInterest > 0) {
                monthlyInterestRate = monthlyInterest / 100;
            } else if (!isNaN(annualInterest) && annualInterest > 0) {
                monthlyInterestRate = annualInterest / 12 / 100;
            } else {
                document.getElementById("result").innerHTML = "<h4>Please provide a valid interest rate.</h4>";
                return;
            }

            const totalInterest = amount * monthlyInterestRate * months;
            const totalPayment = amount + totalInterest;
            const monthlyPayment = totalPayment / months;

            document.getElementById("result").innerHTML = `
                <h2>Results:</h2>
                <h4>Total Payment: ₹${totalPayment.toFixed(2)}</h4>
                <h4>Monthly Payment: ₹${monthlyPayment.toFixed(2)}</h4>
                <h4>Total Interest: ₹${totalInterest.toFixed(2)}</h4>
                <h4>Monthly Interest: ₹${(amount * monthlyInterestRate).toFixed(2)}</h4>`;
        };
    </script>
</body>

</html>
