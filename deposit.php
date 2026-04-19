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
    $note = isset($_POST['note']) ? htmlspecialchars($_POST['note']) : "";

    // ✅ VALIDATION
    if ($amount <= 0) {
        $error = "Amount must be greater than 0";
    } elseif (empty($date)) {
        $error = "Date is required";
    } else {

        // INSERT INTO deposits
        $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, date, note) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $user_id, $amount, $date, $note);
        $stmt->execute();

        // INSERT INTO transactions
        $stmt2 = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'deposit', ?, ?)");
        $stmt2->bind_param("ids", $user_id, $amount, $note);
        $stmt2->execute();

        header("Location: deposit.php");
        exit();
    }
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

</head>

<body>

<div class="dashboard-layout">

<?php include "../Includes/sidebar.php"; ?>

<main class="dash-main">

<h2>Add Deposit</h2>

<!-- BALANCE -->
<?php include "../Includes/balance.php"; ?>

<!-- ERROR -->
<?php if (!empty($error)): ?>
  <div class="form-error"><?= $error ?></div>
<?php endif; ?>

<!-- FORM -->
<div class="form-container">
<form method="POST">

  <label>Amount</label>
  <input type="number" name="amount" min="1" step="0.01" required
  value="<?= isset($_POST['amount']) ? $_POST['amount'] : '' ?>">

  <label>Date</label>
  <input type="date" name="date" id="date" required
  value="<?= isset($_POST['date']) ? $_POST['date'] : '' ?>">

  <label>Note</label>
  <input type="text" name="note" placeholder="Optional"
  value="<?= isset($_POST['note']) ? $_POST['note'] : '' ?>">

  <button type="submit">+ Add Deposit</button>

</form>
</div>

</main>
</div>

<script>
// Auto-fill today's date ONLY if empty
const dateInput = document.getElementById("date");
if (!dateInput.value) {
  dateInput.value = new Date().toISOString().split("T")[0];
}
</script>

</body>
</html>