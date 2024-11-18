<?php
session_start();
include "connection.php";

// Retrieve the current user's information based on their session ID
$userId = intval($_SESSION['id']);  // Get user ID from session
$query = "SELECT username, email FROM users WHERE id = $userId";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$username = $user['username'];
$email = $user['email'];

// Retrieve the last payment details for the current user
$paymentQuery = "SELECT * FROM payments WHERE id = $userId ORDER BY id DESC LIMIT 1";
$paymentResult = mysqli_query($conn, $paymentQuery);
$currentPayment = mysqli_fetch_assoc($paymentResult);

if ($currentPayment) {
    $paymentPlan = $currentPayment['plan'];
    $paymentMethod = $currentPayment['payment_method'];
    $paymentStatus = "Successful"; // Assuming success if data is retrieved
} else {
    $paymentStatus = "No payment found.";
}

// Handle form submission
$paymentSuccess = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['payNow'])) {
    $plan = $_POST['plan'];
    $paymentMethod = $_POST['paymentMethod'];
    $cardNumber = $_POST['cardNumber'];
    $expiryDate = $_POST['expiryDate'];
    $cvv = $_POST['cvv'];

    // Insert payment data into the database using the user ID (from session)
    $query = "INSERT INTO payments (username, email, plan, payment_method, card_number, expiry_date, cvv)
          VALUES ('$username', '$email', '$plan', '$paymentMethod', '$cardNumber', '$expiryDate', '$cvv')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $paymentSuccess = true;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['plan'] = $plan;

        // Fetch the updated payment details
        $paymentQuery = "SELECT * FROM payments WHERE id = $userId ORDER BY id DESC LIMIT 1";
        $paymentResult = mysqli_query($conn, $paymentQuery);
        $currentPayment = mysqli_fetch_assoc($paymentResult);
    } else {
        echo "<p style='color: red; text-align: center;'>Payment failed. Please try again.</p>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Payment</title>
    <link rel="stylesheet" href="css/payment.css">
    <script>
    function updateSummary() {
        const plan = document.getElementById('plan').value;
        const summary = document.getElementById('summary');
        let price;

        if (plan === '1_month') price = "$10.00";
        else if (plan === '2_months') price = "$18.00";
        else if (plan === '1_year') price = "$100.00";

        summary.innerText = `Selected Plan: ${plan.replace('_', ' ')} | Price: ${price}`;
    }

    function confirmPayment() {
        return confirm("Are you sure you want to proceed with the payment?");
    }

    // Display the pop-up if payment was successful
    window.onload = function() {
        <?php if ($paymentSuccess): ?>
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('paymentPopup').style.display = 'block';
        <?php endif; ?>
    };

    // Close the pop-up
    function closePopup() {
        document.getElementById('overlay').style.display = 'none';
        document.getElementById('paymentPopup').style.display = 'none';
    }
    </script>
</head>
<body>

<a href="home.php" class="home-button">
   <img src="./images/logo.png" alt="Home" style="width: 50px; height: 50px;">
</a>


<div class="payment-container">
    <h2>Subscription Payment</h2>
    <p>Complete your payment to activate your subscription.</p>

    <!-- Display user information -->
    <div class="user-info">
        <strong>Username:</strong> <?php echo htmlspecialchars($username); ?><br>
        <strong>Email:</strong> <?php echo htmlspecialchars($email); ?>
    </div>

    <form action="payment.php" method="POST" onsubmit="return confirmPayment()">
        <div class="form-group">
            <label for="plan">Select a Payment Plan:</label>
            <select name="plan" id="plan" onchange="updateSummary()" required>
                <option value="1_month">1 Month - $10.00</option>
                <option value="2_months">2 Months - $18.00</option>
                <option value="1_year">1 Year - $100.00</option>
            </select>
        </div>

        <div class="form-group">
            <label for="paymentMethod">Payment Method:</label>
            <select name="paymentMethod" id="paymentMethod" required>
                <option value="credit_card">Credit Card</option>
                <option value="digital_wallet">Digital Wallet</option>
                <option value="bank_transfer">Bank Transfer</option>
            </select>
        </div>

        <div class="form-group">
            <label for="cardNumber">Card Number:</label>
            <input type="text" id="cardNumber" name="cardNumber" placeholder="XXXX XXXX XXXX XXXX" required>
        </div>

        <div class="form-group">
            <label for="expiryDate">Expiry Date:</label>
            <input type="text" id="expiryDate" name="expiryDate" placeholder="MM/YY" required>
        </div>

        <div class="form-group">
            <label for="cvv">CVV:</label>
            <input type="text" id="cvv" name="cvv" placeholder="XXX" required>
        </div>

        <div class="summary" id="summary">Selected Plan: 1 Month | Price: $10.00</div>

        <input type="submit" name="payNow" value="Pay Now" class="submit-button">
    </form>

    <!-- The success pop-up -->
    <div class="overlay" id="overlay"></div>
    <div class="popup" id="paymentPopup">
        <div class="popup-content">
            <h2>🎉 Payment Successful!</h2>
            <p>Thank you for your subscription.Go to profile to check your active subscription</p>
            <button onclick="closePopup()">Close</button>
        </div>
    </div>
</div>

</body>
</html>
