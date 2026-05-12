<?php

session_start();
include "../Includes/db.php";

require "../vendor/autoload.php";

/* LOGIN CHECK */
if(!isset($_SESSION['user_id'])){

header("Location: ../Config/login.php");
exit();

}

/* STRIPE SECRET */
\Stripe\Stripe::setApiKey(
$_ENV["STRIPE_SECRET_KEY"]
);

/* SESSION ID */
$session_id =
$_GET['session_id'] ?? null;

/* VALIDATION */
if(!$session_id){

die("Invalid Stripe session");

}

try{

/* RETRIEVE STRIPE SESSION */

$session =
\Stripe\Checkout\Session::retrieve(
$session_id
);

/* VERIFY PAYMENT */

if(
$session->payment_status !== "paid"
){

die("Payment verification failed");

}

/* VERIFY TYPE */

if(
$session->metadata->type !== "expense"
){

die("Invalid payment type");

}

/* GET USER */

$user_id =
$_SESSION['user_id'];

/* GET METADATA */

$amount =
(float)$session->metadata->amount;

$date =
htmlspecialchars(
$session->metadata->date
);

$category =
htmlspecialchars(
$session->metadata->category
);

$description =
htmlspecialchars(
$session->metadata->description
);

/* VALIDATE */

if($amount <= 0){

die("Invalid amount");

}

/* SAVE EXPENSE */

$stmt =
$conn->prepare(
"INSERT INTO expenses
(user_id,description,category,amount,date)
VALUES(?,?,?,?,?)"
);

$stmt->bind_param(
"issds",
$user_id,
$description,
$category,
$amount,
$date
);

$stmt->execute();

/* TRANSACTION */

$stmt2 =
$conn->prepare(
"INSERT INTO transactions
(user_id,type,amount,description)
VALUES (?,'expense',?,?)"
);

$stmt2->bind_param(
"ids",
$user_id,
$amount,
$description
);

$stmt2->execute();

}
catch(Exception $e){

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

<title>Expense Payment Success</title>

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
Expense Payment Successful
</h2>

<p>
Your expense payment has been verified and recorded successfully.
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