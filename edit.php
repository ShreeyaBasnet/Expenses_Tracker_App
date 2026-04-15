<?php
session_start();
include "../Includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Config/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";

// GET TRANSACTION
if (!isset($_GET['id'])) {
    header("Location: transactions.php");
    exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM transactions WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$txn = $stmt->get_result()->fetch_assoc();

if (!$txn) {
    header("Location: transactions.php");
    exit();
}

/* =========================
   UPDATE
========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $amount = floatval($_POST['amount']);
    $description = htmlspecialchars($_POST['description']);

    if ($amount <= 0) {
        $error = "Amount must be greater than 0";
    } else {

        // UPDATE TRANSACTIONS
        $stmt = $conn->prepare("UPDATE transactions SET amount=?, description=? WHERE id=? AND user_id=?");
        $stmt->bind_param("dsii", $amount, $description, $id, $user_id);
        $stmt->execute();

        // UPDATE ORIGINAL TABLE
        if ($txn['type'] == 'deposit') {
            $stmt2 = $conn->prepare("UPDATE deposits SET amount=?, note=? WHERE user_id=? AND amount=? AND note=? LIMIT 1");
            $stmt2->bind_param("dsids", $amount, $description, $user_id, $txn['amount'], $txn['description']);
            $stmt2->execute();
        } else {
            $stmt2 = $conn->prepare("UPDATE expenses SET amount=?, description=? WHERE user_id=? AND amount=? AND description=? LIMIT 1");
            $stmt2->bind_param("dsids", $amount, $description, $user_id, $txn['amount'], $txn['description']);
            $stmt2->execute();
        }

        header("Location: transactions.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Edit Transaction</title>

<link rel="stylesheet" href="../Assets/global.css">
<link rel="stylesheet" href="../Assets/sidebar.css">
<link rel="stylesheet" href="../Assets/dashboard.css">
<link rel="stylesheet" href="../Assets/deposit.css">

</head>

<body>

<div class="dashboard-layout">

<?php include "../Includes/sidebar.php"; ?>

<main class="dash-main">

<h2>Edit Transaction</h2>

<?php if (!empty($error)): ?>
  <div class="form-error"><?= $error ?></div>
<?php endif; ?>

<div class="form-container">
<form method="POST">

<label>Amount</label>
<input type="number" name="amount" value="<?= $txn['amount'] ?>" required>

<label>Description</label>
<input type="text" name="description" value="<?= htmlspecialchars($txn['description']) ?>">

<button type="submit">Update</button>

</form>
</div>

</main>
</div>

</body>
</html>