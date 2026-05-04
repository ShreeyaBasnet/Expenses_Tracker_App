<?php
session_start();
include "../Includes/db.php";

if(!isset($_SESSION['user_id'])){
header("Location: ../Config/login.php");
exit();
}
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Wallet Top-Up</title>

<link rel="stylesheet" href="../Assets/global.css">
<link rel="stylesheet" href="../Assets/sidebar.css">
<link rel="stylesheet" href="../Assets/dashboard.css">
<link rel="stylesheet" href="../Assets/balance.css">
<link rel="stylesheet" href="../Assets/deposit.css">

<script src="https://js.stripe.com/v3/"></script>

</head>

<body>

<div class="dashboard-layout">

<?php include "../Includes/sidebar.php"; ?>

<main class="dash-main">

<h2 class="page-title">Top Up Wallet</h2>

<?php include "../Includes/balance.php"; ?>

<!-- CENTER WRAPPER -->
<div class="deposit-layout">

<div class="deposit-card">

<h3 class="form-title">Add Funds</h3>

<form id="depositForm">

<!-- QUICK AMOUNTS -->
<div class="quick-amounts">
<button type="button" onclick="setAmount(100)">+100</button>
<button type="button" onclick="setAmount(500)">+500</button>
<button type="button" onclick="setAmount(1000)">+1000</button>
</div>

<label>Amount</label>
<div class="input-group">
<span>$</span>
<input type="number" id="amount" min="1" step="0.01" required>
</div>

<label>Date</label>
<input type="date" id="date" required>

<label>Note</label>
<input type="text" id="note" placeholder="Optional">

<button type="submit" id="depositBtn">
💳 Add Money via Stripe
</button>

</form>

</div>
</div>

</main>
</div>


<script>

/* AUTO DATE */
document.getElementById("date").value =
new Date().toISOString().split("T")[0];

/* QUICK AMOUNT */
function setAmount(val){
document.getElementById("amount").value = val;
}

/* STRIPE */
const stripe = Stripe("pk_test_51TPcD92MAbMjSPP9kAcoA5qXY28e5BZMpKnzHqwrtxV60bmwwNkdagvvJwRWNJNeIQAFvsPLdGyAOoMG6ZLAm0VT00H3rqK6wk");

document.getElementById("depositForm").addEventListener("submit", async function(e){

e.preventDefault();

let amount = document.getElementById("amount").value;
let date = document.getElementById("date").value;
let note = document.getElementById("note").value;

if(amount<=0){
alert("Enter valid amount");
return;
}

document.getElementById("depositBtn").disabled = true;

try{

let response = await fetch("../Config/create-checkout-session.php",{
method:"POST",
headers:{"Content-Type":"application/json"},
body:JSON.stringify({ amount, date, note })
});

let data = await response.json();

if(data.error){
alert(data.error);
return;
}

await stripe.redirectToCheckout({ sessionId:data.id });

}
catch(err){
alert("Payment failed");
document.getElementById("depositBtn").disabled = false;
}

});

</script>

</body>
</html>