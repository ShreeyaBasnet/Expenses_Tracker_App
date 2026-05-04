<?php
require "../vendor/autoload.php";

\Stripe\Stripe::setApiKey(getenv("STRIPE_SECRET_KEY"));
$session_id = $_GET['session_id'] ?? null;

if (!$session_id) {
  die("Invalid session");
}

try {

  $session = \Stripe\Checkout\Session::retrieve($session_id);

  if ($session->payment_status !== "paid") {
    die("Payment not verified");
  }

} catch(Exception $e) {
  die("Error verifying payment: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Payment Success</title>

<link rel="stylesheet" href="../Assets/global.css">
<link rel="stylesheet" href="../Assets/dashboard.css">

<style>
.success-box {
  max-width: 450px;
  margin: 80px auto;
  background: white;
  padding: 25px;
  border-radius: 14px;
  text-align: center;
  border: 1px solid #eee;
}

.success-box h2 {
  color: #16a34a;
  margin-bottom: 10px;
}

.success-box p {
  color: #555;
  margin-bottom: 20px;
}

.success-box a {
  display: inline-block;
  padding: 10px 18px;
  background: var(--navy);
  color: white;
  border-radius: 8px;
  text-decoration: none;
}
</style>

</head>

<body>

<div class="success-box">

<h2>Payment Successful 🎉</h2>

<p>Your wallet will be updated shortly.</p>

<a href="dashboard.php">Go to Dashboard</a>

</div>

</body>
</html>