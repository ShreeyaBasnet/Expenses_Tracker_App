<?php

session_start();
include "../Includes/db.php";

/* LOGIN CHECK */
if(!isset($_SESSION['user_id'])){

header("Location: ../Config/login.php");
exit();

}

/* CSRF TOKEN */
if(
empty($_SESSION['csrf_token'])
){

$_SESSION['csrf_token'] =
bin2hex(random_bytes(32));

}

$user_id =
$_SESSION['user_id'];

/* GET TRANSACTIONS */

$query = "
SELECT
id,
amount,
description AS note,
type,
created_at AS date
FROM transactions
WHERE user_id=?
ORDER BY created_at DESC
";

$stmt =
$conn->prepare($query);

$stmt->bind_param(
"i",
$user_id
);

$stmt->execute();

$result =
$stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>
Wallet Transactions
</title>

<link rel="stylesheet"
href="../Assets/global.css">

<link rel="stylesheet"
href="../Assets/sidebar.css">

<link rel="stylesheet"
href="../Assets/dashboard.css">

<link rel="stylesheet"
href="../Assets/balance.css">

<link rel="stylesheet"
href="../Assets/transaction.css">

</head>

<body>

<div class="dashboard-layout">

<?php include "../Includes/sidebar.php"; ?>

<main class="dash-main">

<h2>
Wallet Transactions
</h2>

<?php include "../Includes/balance.php"; ?>

<div class="txn-container">

<table class="txn-table">

<thead>

<tr>

<th>Description</th>
<th>Transaction Type</th>
<th>Date</th>
<th>Amount</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php while($row=$result->fetch_assoc()): ?>

<tr>

<td>

<?= htmlspecialchars(
$row['note'] ?: '—'
) ?>

</td>

<td>

<?php

$label =
$row['type']=="expense"
?
"Wallet Payment"
:
"Wallet Top Up";

?>

<span class="
type-badge
<?= $row['type']=="expense"
? 'type-expense'
: 'type-income'
?>
">

<?= $label ?>

</span>

</td>

<td>

<?= date(
"Y-m-d",
strtotime(
$row['date']
)
) ?>

</td>

<td class="
<?= $row['type']=="expense"
? 'amount-expense'
: 'amount-income'
?>
">

<?= $row['type']=="expense"
? "-"
: "+"
?>

$

<?= number_format(
$row['amount'],
2
) ?>

</td>

<td class="txn-actions">

<!-- EDIT -->
<a
href="edit.php?id=<?= $row['id'] ?>"
class="btn-edit"
>
Edit
</a>

<!-- DELETE -->
<form
method="POST"
action="delete.php"
style="display:inline;"
onsubmit="
return confirm(
'Delete this transaction?'
)
"
>

<input
type="hidden"
name="csrf_token"
value="<?= $_SESSION['csrf_token'] ?>"
>

<input
type="hidden"
name="id"
value="<?= $row['id'] ?>"
>

<button
type="submit"
class="btn-delete"
>
Delete
</button>

</form>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</main>
</div>

</body>
</html>