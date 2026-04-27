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
<title>Deposit</title>

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

<h2>Add Deposit</h2>

<?php include "../Includes/balance.php"; ?>

<div class="form-container">

<form id="depositForm">

<label>Amount</label>

<input
type="number"
id="amount"
min="1"
step="0.01"
required
>


<label>Date</label>

<input
type="date"
id="date"
required
>


<label>Note</label>

<input
type="text"
id="note"
placeholder="Optional"
>


<button
type="submit"
id="depositBtn"
>
+ Add Deposit
</button>

</form>

</div>

</main>
</div>


<script>

/* autofill date */

document.getElementById(
"date"
).value=
new Date()
.toISOString()
.split("T")[0];



const stripe=Stripe(
"pk_test_51TPcD92MAbMjSPP9kAcoA5qXY28e5BZMpKnzHqwrtxV60bmwwNkdagvvJwRWNJNeIQAFvsPLdGyAOoMG6ZLAm0VT00H3rqK6wk"
);



document
.getElementById(
"depositForm"
)
.addEventListener(
"submit",
async function(e){

e.preventDefault();

try{

document
.getElementById(
"depositBtn"
).disabled=true;



let amount=
document.getElementById(
"amount"
).value;

let date=
document.getElementById(
"date"
).value;

let note=
document.getElementById(
"note"
).value;



let response=
await fetch(
"../Config/create-checkout-session.php",
{
method:"POST",

headers:{
"Content-Type":
"application/json"
},

body:JSON.stringify({
amount:amount,
date:date,
note:note
})

}
);


let session=
await response.json();

console.log(session);



await stripe.redirectToCheckout({
sessionId:session.id
});

}
catch(err){

console.log(err);

alert(
"Stripe checkout failed"
);

document
.getElementById(
"depositBtn"
).disabled=false;

}

}
);

</script>

</body>
</html>