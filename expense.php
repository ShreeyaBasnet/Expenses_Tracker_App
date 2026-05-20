<?php
session_start();
include "../Includes/db.php";

/* LOGIN CHECK */
if(!isset($_SESSION['user_id'])){
header("Location: ../Config/login.php");
exit();
}

/* CSRF TOKEN */
if(empty($_SESSION['csrf_token'])){
$_SESSION['csrf_token'] =
bin2hex(random_bytes(32));
}

$user_id = $_SESSION['user_id'];
$error = "";

/* HANDLE WALLET PAYMENT */
if(
$_SERVER["REQUEST_METHOD"]=="POST" &&
$_POST["payment_method"]=="wallet"
){

/* CSRF VALIDATION */
if(
!isset($_POST['csrf_token']) ||
$_POST['csrf_token'] !== $_SESSION['csrf_token']
){

die("Invalid CSRF Token");

}

$amount =
floatval($_POST['amount']);

$date =
$_POST['date'];

$category =
htmlspecialchars($_POST['category']);

$description =
htmlspecialchars($_POST['description']);

/* BALANCE */

$stmt1 =
$conn->prepare(
"SELECT IFNULL(SUM(amount),0) total
FROM deposits
WHERE user_id=?"
);

$stmt1->bind_param(
"i",
$user_id
);

$stmt1->execute();

$totalDeposit =
$stmt1->get_result()
->fetch_assoc()['total'];



$stmt2 =
$conn->prepare(
"SELECT IFNULL(SUM(amount),0) total
FROM expenses
WHERE user_id=?"
);

$stmt2->bind_param(
"i",
$user_id
);

$stmt2->execute();

$totalExpense =
$stmt2->get_result()
->fetch_assoc()['total'];



$walletBalance =
$totalDeposit -
$totalExpense;


/* VALIDATION */

if($amount <= 0){

$error =
"Invalid amount";

}

elseif($amount > $walletBalance){

$error =
"Insufficient balance: $"
.
number_format(
$walletBalance,
2
);

}

else{

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

$stmt3 =
$conn->prepare(
"INSERT INTO transactions
(user_id,type,amount,description)
VALUES (?,'expense',?,?)"
);

$stmt3->bind_param(
"ids",
$user_id,
$amount,
$description
);

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

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
/>

<title>Pay Expense</title>

<link rel="stylesheet"
href="../Assets/global.css">

<link rel="stylesheet"
href="../Assets/sidebar.css">

<link rel="stylesheet"
href="../Assets/dashboard.css">

<link rel="stylesheet"
href="../Assets/balance.css">

<link rel="stylesheet"
href="../Assets/expense.css">

<script src="https://js.stripe.com/v3/"></script>

</head>

<body>

<div class="dashboard-layout">

<?php include "../Includes/sidebar.php"; ?>

<main class="dash-main">

<h2>Pay Expense</h2>

<?php include "../Includes/balance.php"; ?>

<?php if($error): ?>

<div class="form-error">
<?= $error ?>
</div>

<?php endif; ?>

<div class="form-wrapper">

<div class="form-card">

<h3>Add Expense</h3>

<form id="expenseForm">

<!-- CSRF -->
<input
type="hidden"
id="csrf_token"
value="<?= $_SESSION['csrf_token'] ?>"
>

<input
type="hidden"
name="payment_method"
id="payment_method"
>

<!-- QUICK AMOUNTS -->
<div class="quick-btns">

<button
type="button"
onclick="setAmount(100)"
>
+100
</button>

<button
type="button"
onclick="setAmount(500)"
>
+500
</button>

<button
type="button"
onclick="setAmount(1000)"
>
+1000
</button>

</div>

<label>Amount</label>

<input
type="number"
id="amount"
required
>

<label>Date</label>

<input
type="date"
id="date"
>

<label>Category</label>

<select id="category">

<option>Food</option>
<option>Transport</option>
<option>Shopping</option>
<option>Bills</option>
<option>Entertainment</option>

</select>

<label>Description</label>

<input
type="text"
id="description"
placeholder="Optional"
>

<button
type="button"
class="wallet-btn"
onclick="walletPay()"
>
Add Expenses
</button>

<button
type="button"
class="stripe-btn"
onclick="stripePay()"
>
Pay via Stripe
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

let amountInput =
document.getElementById("amount");

let currentAmount =
parseFloat(amountInput.value) || 0;

amountInput.value =
currentAmount + val;

}

/* STRIPE */
const stripe = Stripe(
"pk_test_51TPcD92MAbMjSPP9kAcoA5qXY28e5BZMpKnzHqwrtxV60bmwwNkdagvvJwRWNJNeIQAFvsPLdGyAOoMG6ZLAm0VT00H3rqK6wk"
);

/* WALLET PAYMENT */
function walletPay(){

let pin =
prompt("Enter Wallet PIN");

if(pin !== "1234"){

alert("Wrong PIN");
return;

}

submitForm("wallet");

}

/* STRIPE PAYMENT */
async function stripePay(){

let amount =
document.getElementById("amount").value;

if(!amount || amount <= 0){

alert("Enter valid amount");
return;

}

try{

let res =
await fetch(
"../Config/create-expense-session.php",
{
method:"POST",

headers:{
"Content-Type":"application/json"
},

body:JSON.stringify({

amount:
document.getElementById("amount").value,

date:
document.getElementById("date").value,

category:
document.getElementById("category").value,

description:
document.getElementById("description").value,

csrf_token:
document.getElementById("csrf_token").value

})

}
);

let data =
await res.json();

if(data.error){

alert(data.error);
return;

}

await stripe.redirectToCheckout({
sessionId:data.id
});

}
catch(err){

alert("Stripe payment failed");

}

}

/* SUBMIT WALLET FORM */
function submitForm(type){

let form =
document.createElement("form");

form.method = "POST";

function add(n,v){

let i =
document.createElement("input");

i.name = n;
i.value = v;

form.appendChild(i);

}

add(
"csrf_token",
document.getElementById("csrf_token").value
);

add("payment_method",type);

add(
"amount",
document.getElementById("amount").value
);

add(
"date",
document.getElementById("date").value
);

add(
"category",
document.getElementById("category").value
);

add(
"description",
document.getElementById("description").value
);

document.body.appendChild(form);

form.submit();

}

</script>

</body>
</html>