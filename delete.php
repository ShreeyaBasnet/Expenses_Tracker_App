<?php
session_start();
include "../Includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Config/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    // Get transaction
    $stmt = $conn->prepare("SELECT type, amount, description FROM transactions WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $txn = $stmt->get_result()->fetch_assoc();

    if ($txn) {

        if ($txn['type'] == 'deposit') {
            $stmtDel = $conn->prepare("DELETE FROM deposits WHERE user_id=? AND amount=? AND note=? LIMIT 1");
            $stmtDel->bind_param("ids", $user_id, $txn['amount'], $txn['description']);
            $stmtDel->execute();
        } else {
            $stmtDel = $conn->prepare("DELETE FROM expenses WHERE user_id=? AND amount=? AND description=? LIMIT 1");
            $stmtDel->bind_param("ids", $user_id, $txn['amount'], $txn['description']);
            $stmtDel->execute();
        }

        // Delete transaction
        $stmt = $conn->prepare("DELETE FROM transactions WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
    }
}

header("Location: transactions.php");
exit();