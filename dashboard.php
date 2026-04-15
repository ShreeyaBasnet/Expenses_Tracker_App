<?php
session_start();
include "../Includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Config/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// RECENT TRANSACTIONS
$query = "
    SELECT description, category, amount, date, 'expense' as type 
    FROM expenses WHERE user_id = ?
    
    UNION ALL
    
    SELECT note as description, 'Deposit' as category, amount, date, 'deposit' as type 
    FROM deposits WHERE user_id = ?
    
    ORDER BY date DESC LIMIT 5
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$transactions = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Dashboard</title>

<link rel="stylesheet" href="../Assets/global.css">
<link rel="stylesheet" href="../Assets/sidebar.css">
<link rel="stylesheet" href="../Assets/dashboard.css">
<link rel="stylesheet" href="../Assets/transaction.css">
</head>

<body>

<div class="dashboard-layout">

<?php include "../Includes/sidebar.php"; ?>

<main class="dash-main">

<!-- HEADER -->
<div class="dash-header">
  <div>
    <div class="dash-greeting">
      Hello, <?php echo ucfirst($username); ?> 👋
    </div>
    <div class="dash-date"><?php echo date("l, d M Y"); ?></div>
  </div>

  <div class="dash-header-actions">
    <button class="btn btn-outline" onclick="goToDeposit()">Deposit</button>
    <button class="btn btn-primary" onclick="goToExpense()">+ Add Expense</button>
  </div>
</div>

<!-- ✅ BALANCE COMPONENT -->
<?php include "../Includes/balance.php"; ?>

<!-- TRANSACTIONS -->
<div class="txn-section">
  <div class="txn-header">
    <div class="txn-title">Recent Transactions</div>
  </div>

  <table class="txn-table">
    <thead>
      <tr>
        <th>Description</th>
        <th>Category</th>
        <th>Date</th>
        <th>Status</th>
        <th style="text-align:right">Amount</th>
      </tr>
    </thead>

    <tbody>

<?php while($row = $transactions->fetch_assoc()): ?>

<tr>
  <td><?= htmlspecialchars($row['description'] ?: '—') ?></td>
  <td><?= htmlspecialchars($row['category']) ?></td>
  <td><?= date("Y-m-d", strtotime($row['date'])) ?></td>

  <td>
    <span class="type-badge <?= $row['type'] == 'expense' ? 'type-expense' : 'type-income' ?>">
      <?= $row['type'] == 'expense' ? 'Expense' : 'Deposit' ?>
    </span>
  </td>

  <td class="<?= $row['type']=="expense" ? 'amount-expense' : 'amount-income' ?>">
    <?= $row['type']=='expense' ? '-' : '+' ?>$<?= number_format($row['amount'], 2) ?>
  </td>
</tr>

<?php endwhile; ?>

    </tbody>
  </table>
</div>

</main>
</div>

<script>
function goToDeposit() {
  window.location.href = "deposit.php";
}

function goToExpense() {
  window.location.href = "expense.php";
}
</script>

</body>
</html>