<?php
include "../Config/db.php";

if (!isset($_GET['token'])) {
    die("Invalid report");
}

$token = $_GET['token'];

$userQuery = $conn->prepare("SELECT id, username FROM users WHERE report_token=?");
$userQuery->bind_param("s", $token);
$userQuery->execute();
$userResult = $userQuery->get_result();

if ($userResult->num_rows == 0) {
    die("User not found");
}

$user = $userResult->fetch_assoc();
$user_id = $user['id'];

// TOTAL DEPOSIT
$stmt1 = $conn->prepare("SELECT SUM(amount) total FROM deposits WHERE user_id=?");
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$totalDeposit = $stmt1->get_result()->fetch_assoc()['total'] ?? 0;

// TOTAL EXPENSE
$stmt2 = $conn->prepare("SELECT SUM(amount) total FROM expenses WHERE user_id=?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$totalExpense = $stmt2->get_result()->fetch_assoc()['total'] ?? 0;

$balance = $totalDeposit - $totalExpense;

// CATEGORY TOTALS
$food = 0;
$transport = 0;
$shopping = 0;
$others = 0;

$catQuery = $conn->prepare("SELECT category, SUM(amount) total FROM expenses WHERE user_id=? GROUP BY category");
$catQuery->bind_param("i", $user_id);
$catQuery->execute();
$catResult = $catQuery->get_result();

while($row = $catResult->fetch_assoc()) {

    if ($row['category'] == 'Food') {
        $food = $row['total'];
    }

    if ($row['category'] == 'Transport') {
        $transport = $row['total'];
    }

    if ($row['category'] == 'Shopping') {
        $shopping = $row['total'];
    }

    if ($row['category'] == 'Others') {
        $others = $row['total'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Expense Report</title>
<link rel="stylesheet" href="../Assets/dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
</html>