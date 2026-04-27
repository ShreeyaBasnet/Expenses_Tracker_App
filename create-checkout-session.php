<?php

session_start();

require "stripe-config.php";
include "db.php";

header("Content-Type: application/json");

$data=json_decode(
file_get_contents("php://input"),
true
);

if(!$data){
echo json_encode([
"error"=>"No data sent"
]);
exit;
}

$amount=(int)$data["amount"];
$date=$data["date"];
$note=$data["note"];

$_SESSION["deposit_amount"]=$amount;
$_SESSION["deposit_date"]=$date;
$_SESSION["deposit_note"]=$note;


try{

$session=
\Stripe\Checkout\Session::create([

'payment_method_types'=>['card'],

'line_items'=>[
[
'price_data'=>[
'currency'=>'usd',

'product_data'=>[
'name'=>'Wallet Top Up'
],

'unit_amount'=>$amount*100
],

'quantity'=>1
]
],

'mode'=>'payment',

'success_url'=>
'http://localhost/Colab/Public/stripe-success.php',

'cancel_url'=>
'http://localhost/Colab/Public/deposit.php'

]);

echo json_encode([
"id"=>$session->id
]);

}

catch(Exception $e){

echo json_encode([
"error"=>$e->getMessage()
]);

}