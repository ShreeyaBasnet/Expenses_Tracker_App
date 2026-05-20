<?php
/* ============================================================
   Database Connection
   Expense Tracker - MySQLi Connection
   ============================================================ */

/* Database credentials */
$host = "localhost";
$username = "root";
$password = "";
$database = "expense_tracker";

/* Create MySQLi connection */
$conn = new mysqli($host, $username, $password, $database);

/* Check connection */
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* Set charset to prevent encoding issues */
$conn->set_charset("utf8mb4");
?>
