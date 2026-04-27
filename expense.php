<?php
session_start();
include "../Includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Config/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $date = $_POST['date'] ?? "";
    $category = $_POST['category'] ?? "";
    $description = isset($_POST['description']) 
        ? htmlspecialchars($_POST['description']) 
        : "";


/* WALLET BALANCE */

$stmt1=$conn->prepare(
"SELECT SUM(amount) total
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
->fetch_assoc()['total'] ?? 0;



$stmt2=$conn->prepare(
"SELECT SUM(amount) total
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
->fetch_assoc()['total'] ?? 0;



$currentBalance=
$totalDeposit-
$totalExpense;



/* VALIDATION */

if($amount<=0){

$error=
"Amount must be greater than 0";

}

elseif(empty($date)){

$error=
"Date required";

}

elseif($amount>$currentBalance){

$error=
"Wallet payment failed.
Insufficient balance:
$".
number_format(
$currentBalance,
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



/* TRANSACTION */

$stmt2=
$conn->prepare(
"INSERT INTO transactions
(user_id,type,amount,description)
VALUES
(?,'expense',?,?)"
);

$stmt2->bind_param(
"ids",
$user_id,
$amount,
$description
);

$stmt2->execute();



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
<title>Wallet Payment</title>

<link rel="stylesheet" href="../Assets/global.css">
<link rel="stylesheet" href="../Assets/sidebar.css">
<link rel="stylesheet" href="../Assets/dashboard.css">
<link rel="stylesheet" href="../Assets/balance.css">
<link rel="stylesheet" href="../Assets/expense.css">

</head>

<body>

<div class="dashboard-layout">

<?php include "../Includes/sidebar.php"; ?>

<main class="dash-main">

<h2>Pay Using Wallet</h2>


<?php include "../Includes/balance.php"; ?>


<?php if(!empty($error)): ?>

<div class="form-error">
<?= $error ?>
</div>

<?php endif; ?>



<div class="form-container">

<form method="POST" id="walletForm">


<label>Amount</label>

<input
type="number"
name="amount"
min="1"
step="0.01"
required
value="<?= $_POST['amount'] ?? '' ?>"
>



<label>Date</label>

<input
type="date"
name="date"
id="date"
required
value="<?= $_POST['date'] ?? '' ?>"
>



<label>Category</label>

<select
name="category"
required
>

<option value="">
Select category
</option>

<option value="Food">
Food
</option>

<option value="Transport">
Transport
</option>

<option value="Shopping">
Shopping
</option>

<option value="Bills">
Bills
</option>

<option value="Entertainment">
Entertainment
</option>

</select>



<label>Description</label>

<input
type="text"
name="description"
placeholder="Optional"
value="<?= $_POST['description'] ?? '' ?>"
>



<button type="submit">
💳 Pay with Wallet
</button>

</form>

</div>

</main>
</div>



<script>

const dateInput=
document.getElementById(
"date"
);

if(!dateInput.value){

dateInput.value=
new Date()
.toISOString()
.split("T")[0];

}



/* Payment confirmation */

document
.getElementById(
"walletForm"
)
.addEventListener(
"submit",
function(e){

let amount=
document.querySelector(
'[name="amount"]'
).value;


let ok=
confirm(
"Confirm wallet payment of $"
+
amount
+
" ?"
);


if(!ok){
e.preventDefault();
}

}
);

</script>

</body>
</html>