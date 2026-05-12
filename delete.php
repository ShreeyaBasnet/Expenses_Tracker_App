<?php

session_start();
include "../Includes/db.php";

/* LOGIN CHECK */
if (!isset($_SESSION['user_id'])) {

header("Location: ../Config/login.php");
exit();

}

/* CSRF TOKEN CHECK */
if (
empty($_SESSION['csrf_token'])
) {

$_SESSION['csrf_token'] =
bin2hex(random_bytes(32));

}

$user_id =
$_SESSION['user_id'];

/* VALIDATE REQUEST */
if (
$_SERVER["REQUEST_METHOD"] !== "POST"
) {

die("Invalid request");

}

/* CSRF VALIDATION */
if (
!isset($_POST['csrf_token']) ||
$_POST['csrf_token'] !== $_SESSION['csrf_token']
) {

die("Invalid CSRF token");

}

/* ID VALIDATION */
if (
!isset($_POST['id'])
) {

die("Transaction ID missing");

}

$id =
intval($_POST['id']);

/* GET TRANSACTION */

$stmt =
$conn->prepare(
"SELECT type, amount, description
FROM transactions
WHERE id=? AND user_id=?"
);

$stmt->bind_param(
"ii",
$id,
$user_id
);

$stmt->execute();

$txn =
$stmt->get_result()->fetch_assoc();

/* IF EXISTS */
if ($txn) {

/* DELETE FROM DEPOSITS */

if ($txn['type'] == 'deposit') {

$stmtDel =
$conn->prepare(
"DELETE FROM deposits
WHERE user_id=? AND amount=? AND note=?
LIMIT 1"
);

$stmtDel->bind_param(
"ids",
$user_id,
$txn['amount'],
$txn['description']
);

$stmtDel->execute();

}

/* DELETE FROM EXPENSES */
else {

$stmtDel =
$conn->prepare(
"DELETE FROM expenses
WHERE user_id=? AND amount=? AND description=?
LIMIT 1"
);

$stmtDel->bind_param(
"ids",
$user_id,
$txn['amount'],
$txn['description']
);

$stmtDel->execute();

}

/* DELETE TRANSACTION */

$stmtTxn =
$conn->prepare(
"DELETE FROM transactions
WHERE id=? AND user_id=?"
);

$stmtTxn->bind_param(
"ii",
$id,
$user_id
);

$stmtTxn->execute();

}

/* REDIRECT */
header("Location: transactions.php");
exit();

?>