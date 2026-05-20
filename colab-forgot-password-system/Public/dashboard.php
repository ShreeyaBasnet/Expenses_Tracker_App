<?php
/* ============================================================
   Dashboard - Expense Tracker
   Protected page: redirects to login if not authenticated
   ============================================================ */

session_start();

/* Check if user is logged in */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Expense Tracker</title>
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
    <p>This is your expense tracker dashboard.</p>
    <a href="../Auth/logout.php">Logout</a>
</body>
</html>
