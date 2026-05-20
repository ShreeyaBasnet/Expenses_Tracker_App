<?php

session_start();
include "db.php";

$data=json_decode(
file_get_contents("php://input"),
true
);

$user_id=$_SESSION['user_id'];

$amount=$data['amount'];
$date=$data['date'];
$note=$data['note'];

$stmt=$conn->prepare(
"INSERT INTO deposits
(user_id,amount,date,note)
VALUES(?,?,?,?)"
);

$stmt->bind_param(
"idss",
$user_id,
$amount,
$date,
$note
);

$stmt->execute();


$stmt2=$conn->prepare(
"INSERT INTO transactions
(user_id,type,amount,description)
VALUES
(?,'deposit',?,?)"
);

$stmt2->bind_param(
"ids",
$user_id,
$amount,
$note
);

$stmt2->execute();

echo "success";