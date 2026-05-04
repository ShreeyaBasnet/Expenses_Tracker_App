<?php
session_start();
include "../Includes/db.php";

if(!isset($_SESSION['user_id'])){
header("Location: ../Config/login.php");
exit();
}

$user_id = $_SESSION['user_id'];
$error = "";

/* HANDLE WALLET PAYMENT */
if($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["payment_method"]=="wallet"){

$amount = floatval($_POST['amount']);
$date = $_POST['date'];
$category = $_POST['category'];
$description = htmlspecialchars($_POST['description']);

/* BALANCE */

$stmt1=$conn->prepare("SELECT IFNULL(SUM(amount),0) total FROM deposits WHERE user_id=?");
$stmt1->bind_param("i",$user_id);
$stmt1->execute();
$totalDeposit=$stmt1->get_result()->fetch_assoc()['total'];

$stmt2=$conn->prepare("SELECT IFNULL(SUM(amount),0) total FROM expenses WHERE user_id=?");
$stmt2->bind_param("i",$user_id);
$stmt2->execute();
$totalExpense=$stmt2->get_result()->fetch_assoc()['total'];

$walletBalance=$totalDeposit-$totalExpense;

/* VALIDATION */

if($amount<=0){
$error="Invalid amount";
}
elseif($amount>$walletBalance){
$error="Insufficient balance: $".number_format($walletBalance,2);
}
else{

$stmt=$conn->prepare("
INSERT INTO expenses (user_id,description,category,amount,date)
VALUES(?,?,?,?,?)
");

$stmt->bind_param("issds",$user_id,$description,$category,$amount,$date);
$stmt->execute();

$stmt3=$conn->prepare("
INSERT INTO transactions (user_id,type,amount,description)
VALUES (?,'expense',?,?)
");

$stmt3->bind_param("ids",$user_id,$amount,$description);
$stmt3->execute();

header("Location: dashboard.php");
exit();
}
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Pay Expense</title>

<link rel="stylesheet" href="../Assets/global.css">
<link rel="stylesheet" href="../Assets/sidebar.css">
<link rel="stylesheet" href="../Assets/dashboard.css">
<link rel="stylesheet" href="../Assets/balance.css">
<link rel="stylesheet" href="../Assets/expense.css">


<script src="https://js.stripe.com/v3/"></script>



</head>

<body>

<div class="dashboard-layout">

<?php include "../Includes/sidebar.php"; ?>

<main class="dash-main">

<h2>Pay Expense</h2>

<?php include "../Includes/balance.php"; ?>

<?php if($error): ?>
<div class="form-error"><?= $error ?></div>
<?php endif; ?>

<div class="form-card">

<h3>Add Expense</h3>

<form id="expenseForm">

<input type="hidden" name="payment_method" id="payment_method">

<label>Amount</label>
<input type="number" id="amount" required>

<label>Date</label>
<input type="date" id="date">

<label>Category</label>
<select id="category">
<option>Food</option>
<option>Transport</option>
<option>Shopping</option>
<option>Bills</option>
<option>Entertainment</option>
</select>

<label>Description</label>
<input type="text" id="description" placeholder="Optional">

<button type="button" class="wallet-btn" onclick="walletPay()">💰 Pay from Wallet</button>

<button type="button" class="stripe-btn" onclick="stripePay()">💳 Pay via Stripe</button>

</form>

</div>

</main>
</div>

<script>

/* auto date */
document.getElementById("date").value =
new Date().toISOString().split("T")[0];

const stripe = Stripe("YOUR_PUBLIC_KEY");

/* WALLET */
function walletPay(){

let pin = prompt("Enter Wallet PIN");

if(pin!=="1234"){
alert("Wrong PIN");
return;
}

submitForm("wallet");
}

/* STRIPE */
async function stripePay(){

let res = await fetch("../Config/create-expense-session.php",{
method:"POST",
headers:{"Content-Type":"application/json"},
body:JSON.stringify({
amount:document.getElementById("amount").value,
date:document.getElementById("date").value,
category:document.getElementById("category").value,
description:document.getElementById("description").value
})
});

let data = await res.json();

stripe.redirectToCheckout({
sessionId:data.id
});
}

/* SUBMIT */
function submitForm(type){

let form = document.createElement("form");
form.method="POST";

function add(n,v){
let i=document.createElement("input");
i.name=n;
i.value=v;
form.appendChild(i);
}

add("payment_method",type);
add("amount",document.getElementById("amount").value);
add("date",document.getElementById("date").value);
add("category",document.getElementById("category").value);
add("description",document.getElementById("description").value);

document.body.appendChild(form);
form.submit();
}

</script>

</body>
</html>