<?php
session_start();
include "../Includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Config/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "
SELECT id, amount, description AS note, type, created_at AS date
FROM transactions
WHERE user_id = ?
ORDER BY created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Transactions</title>

<link rel="stylesheet" href="../Assets/global.css">
<link rel="stylesheet" href="../Assets/sidebar.css">
<link rel="stylesheet" href="../Assets/dashboard.css">
<link rel="stylesheet" href="../Assets/balance.css">
<link rel="stylesheet" href="../Assets/transaction.css">

</head>

<body>

<div class="dashboard-layout">

<?php include "../Includes/sidebar.php"; ?>

<main class="dash-main">

<h2>Transactions</h2>

<?php include "../Includes/balance.php"; ?>

<div class="txn-container">

<table class="txn-table">

<thead>
<tr>
<th>Description</th>
<th>Type</th>
<th>Date</th>
<th>Amount</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>

<td><?= htmlspecialchars($row['note']) ?></td>

<td>
  <span class="type-badge <?= $row['type'] == 'expense' ? 'type-expense' : 'type-income' ?>">
    <?= ucfirst($row['type']) ?>
  </span>
</td>

<td><?= date("Y-m-d", strtotime($row['date'])) ?></td>

<td class="<?= $row['type']=="expense" ? 'amount-expense' : 'amount-income' ?>">
  <?= $row['type']=="expense" ? "-" : "+" ?>$<?= number_format($row['amount'], 2) ?>
</td>

<td class="txn-actions">
  <a href="edit.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>

  <a href="delete.php?id=<?= $row['id'] ?>" 
     class="btn-delete"
     onclick="return confirm('Are you sure you want to delete this transaction?')">
     Delete
  </a>
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