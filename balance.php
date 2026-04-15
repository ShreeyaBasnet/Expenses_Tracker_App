<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../Includes/db.php";

$user_id = $_SESSION['user_id'] ?? null;

$balance = 0;
$totalDeposit = 0;
$totalExpense = 0;

if ($user_id) {

    // TOTAL DEPOSIT
    $stmt1 = $conn->prepare("SELECT SUM(amount) as total FROM deposits WHERE user_id=?");
    $stmt1->bind_param("i", $user_id);
    $stmt1->execute();
    $totalDeposit = $stmt1->get_result()->fetch_assoc()['total'] ?? 0;

    // TOTAL EXPENSE
    $stmt2 = $conn->prepare("SELECT SUM(amount) as total FROM expenses WHERE user_id=?");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $totalExpense = $stmt2->get_result()->fetch_assoc()['total'] ?? 0;

    $balance = $totalDeposit - $totalExpense;
}
?>

<link rel="stylesheet" href="../Assets/balance.css">
<div class="balance-cards">

  <div class="balance-card main">
    <div class="balance-label">Total Balance</div>
    <div class="balance-value">$<?= number_format($balance, 2) ?></div>
  </div>

  <div class="balance-card income">
    <div class="balance-label">Total Income</div>
    <div class="balance-value">$<?= number_format($totalDeposit, 2) ?></div>
  </div>

  <div class="balance-card expense">
    <div class="balance-label">Total Expense</div>
    <div class="balance-value">$<?= number_format($totalExpense, 2) ?></div>
  </div>

  <div class="balance-card savings">
    <div class="balance-label">Net Savings</div>
    <div class="balance-value">$<?= number_format($balance, 2) ?></div>
  </div>

</div>