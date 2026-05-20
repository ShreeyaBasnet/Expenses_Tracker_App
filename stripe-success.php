<?php

session_start();

require "../vendor/autoload.php";
include "../Includes/db.php";

/* LOGIN CHECK */
if(!isset($_SESSION['user_id'])){

header("Location: ../Config/login.php");
exit();

}

/* STRIPE SECRET */
\Stripe\Stripe::setApiKey(
$_ENV["STRIPE_SECRET_KEY"]
);

/* GET SESSION ID */
$session_id =
$_GET['session_id'] ?? null;

/* VALIDATION */
if (!$session_id) {

die("Invalid Stripe session");

}

try {

/* RETRIEVE SESSION */

$session =
\Stripe\Checkout\Session::retrieve(
$session_id
);

/* VERIFY PAYMENT */

if (
$session->payment_status !== "paid"
) {

die("Payment not verified");

}

/* OPTIONAL EXTRA SECURITY */

if (
$session->metadata->type !== "deposit"
) {

die("Invalid payment type");

}

}
catch(Exception $e) {

die(
"Stripe verification failed: "
.
$e->getMessage()
);

}

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Payment Success</title>

<link rel="stylesheet"
href="../Assets/global.css">

<link rel="stylesheet"
href="../Assets/dashboard.css">

<style>

body{
background:#f5f7fb;
font-family:Arial,sans-serif;
}

.success-wrapper{
display:flex;
justify-content:center;
align-items:center;
min-height:100vh;
padding:20px;
}

.success-box{
width:100%;
max-width:450px;
background:white;
padding:35px 30px;
border-radius:18px;
text-align:center;
border:1px solid #eee;
box-shadow:0 6px 18px rgba(0,0,0,0.05);
}

.success-icon{
font-size:55px;
margin-bottom:10px;
}

.success-box h2{
color:#16a34a;
font-size:28px;
margin-bottom:10px;
}

.success-box p{
color:#666;
font-size:15px;
line-height:1.6;
margin-bottom:25px;
}

.success-btn{
display:inline-block;
padding:12px 22px;
background:var(--navy);
color:white;
border-radius:10px;
text-decoration:none;
font-weight:600;
transition:0.2s ease;
}

.success-btn:hover{
opacity:0.95;
transform:translateY(-1px);
}

</style>

</head>

<body>

<div class="success-wrapper">

<div class="success-box">

<div class="success-icon">
✅
</div>

<h2>
Payment Successful
</h2>

<p>
Your wallet top-up has been verified successfully through Stripe.
</p>

<a
href="dashboard.php"
class="success-btn"
>
Go to Dashboard
</a>

</div>

</div>

</body>
</html>