<?php
session_start();
include "../Includes/db.php";

if(!isset($_SESSION['expense_amount'])){
header("Location: dashboard.php");
exit();
}

$user_id=$_SESSION['user_id'];

$amount=$_SESSION['expense_amount'];
$date=$_SESSION['expense_date'];
$category=$_SESSION['expense_category'];
$description=$_SESSION['expense_description'];


/* SAVE EXPENSE */

$stmt=$conn->prepare("
INSERT INTO expenses (user_id,description,category,amount,date)
VALUES(?,?,?,?,?)
");

$stmt->bind_param("issds",$user_id,$description,$category,$amount,$date);
$stmt->execute();


/* TRANSACTION */

$stmt2=$conn->prepare("
INSERT INTO transactions (user_id,type,amount,description)
VALUES (?,'expense',?,?)
");

$stmt2->bind_param("ids",$user_id,$amount,$description);
$stmt2->execute();


unset($_SESSION['expense_amount']);
unset($_SESSION['expense_date']);
unset($_SESSION['expense_category']);
unset($_SESSION['expense_description']);


echo "<script>alert('Payment Successful');window.location='dashboard.php';</script>";