<?php
session_start();
include "../Includes/db.php";

if(!isset($_SESSION['user_id'])){
header("Location: ../Config/login.php");
exit();
}

$user_id=$_SESSION['user_id'];
$error="";


if($_SERVER["REQUEST_METHOD"]=="POST"){

$amount=
floatval(
$_POST['amount']
);

$date=
$_POST['date'];

$category=
$_POST['category'];

$description=
htmlspecialchars(
$_POST['description']
);



/* WALLET DEPOSITS */

$stmt1=
$conn->prepare(
"SELECT IFNULL(
SUM(amount),0
) total
FROM deposits
WHERE user_id=?"
);

$stmt1->bind_param(
"i",
$user_id
);

$stmt1->execute();

$totalDeposit=
$stmt1->get_result()
->fetch_assoc()['total'];



/* WALLET EXPENSES */

$stmt2=
$conn->prepare(
"SELECT IFNULL(
SUM(amount),0
) total
FROM expenses
WHERE user_id=?"
);

$stmt2->bind_param(
"i",
$user_id
);

$stmt2->execute();

$totalExpense=
$stmt2->get_result()
->fetch_assoc()['total'];



$walletBalance=
$totalDeposit-
$totalExpense;



/* VALIDATION */

if($amount<=0){

$error=
"Invalid amount.";

}

elseif(
$amount>$walletBalance
){

$error=
"Wallet payment failed.
Insufficient balance:
$"
.
number_format(
$walletBalance,
2
);

}

else{


/* SAVE EXPENSE */

$stmt=
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



/* TRANSACTION LOG */

$stmt3=
$conn->prepare(
"INSERT INTO transactions
(user_id,type,amount,description)
VALUES
(?,'expense',?,?)"
);

$stmt3->bind_param(
"ids",
$user_id,
$amount,
$description
);

$stmt3->execute();



header(
"Location: dashboard.php"
);

exit();

}

}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Pay Using Wallet</title>

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

</head>

<body>

<div class="dashboard-layout">

<?php include "../Includes/sidebar.php"; ?>

<main class="dash-main">

<h2>
Pay Using Wallet
</h2>


<?php include "../Includes/balance.php"; ?>


<?php if($error): ?>

<div class="form-error">
<?= $error ?>
</div>

<?php endif; ?>


<div class="form-container">

<form method="POST">


<label>
Amount
</label>

<input
type="number"
name="amount"
min="1"
step="0.01"
required
>



<label>
Date
</label>

<input
type="date"
name="date"
id="date"
required
>



<label>
Category
</label>

<select
name="category"
required
>

<option value="">
Select Category
</option>

<option>
Food
</option>

<option>
Transport
</option>

<option>
Shopping
</option>

<option>
Bills
</option>

<option>
Entertainment
</option>

</select>



<label>
Description
</label>

<input
type="text"
name="description"
placeholder="Optional"
>



<button type="submit">
💳 Pay with Wallet
</button>


</form>

</div>

</main>
</div>



<script>

/* auto-fill today's date */

document
.getElementById(
"date"
).value=
new Date()
.toISOString()
.split("T")[0];

</script>

</body>
</html>