<?php

session_start();

require "../vendor/autoload.php";
include "../Includes/db.php";

/* LOGIN CHECK */
if(!isset($_SESSION['user_id'])){
http_response_code(401);

echo json_encode([
"error" => "Unauthorized"
]);

exit();
}

/* CSRF CHECK */
if(
empty($_SESSION['csrf_token'])
){
$_SESSION['csrf_token'] =
bin2hex(random_bytes(32));
}

/* STRIPE SECRET KEY */
\Stripe\Stripe::setApiKey(
$_ENV["STRIPE_SECRET_KEY"]
);

header("Content-Type: application/json");

/* GET JSON DATA */
$data =
json_decode(
file_get_contents("php://input"),
true
);

/* VALIDATE REQUEST */
if(!$data){

echo json_encode([
"error" => "Invalid request"
]);

exit();
}

/* CSRF VALIDATION */
if(
!isset($data['csrf_token']) ||
$data['csrf_token'] !== $_SESSION['csrf_token']
){

echo json_encode([
"error" => "Invalid CSRF token"
]);

exit();
}

/* USER */
$user_id =
$_SESSION['user_id'];

/* SANITIZE INPUTS */
$amount =
isset($data["amount"])
?
(float)$data["amount"]
:
0;

$date =
$data["date"] ?? "";

$note =
trim(
htmlspecialchars(
$data["note"] ?? ""
)
);

/* VALIDATION */
if($amount <= 0){

echo json_encode([
"error" => "Invalid amount"
]);

exit();
}

if(empty($date)){

echo json_encode([
"error" => "Date required"
]);

exit();
}

/* STORE IN SESSION (OPTIONAL FALLBACK) */
$_SESSION["deposit_amount"] = $amount;
$_SESSION["deposit_date"]   = $date;
$_SESSION["deposit_note"]   = $note;

try{

/* CREATE STRIPE SESSION */
$session =
\Stripe\Checkout\Session::create([

'payment_method_types' => ['card'],

'mode' => 'payment',

'line_items' => [[

'price_data' => [

'currency' => 'usd',

'product_data' => [
'name' => 'Wallet Top Up'
],

'unit_amount' => $amount * 100

],

'quantity' => 1

]],

/* METADATA */
'metadata' => [

'user_id' => $user_id,

'amount'  => $amount,

'date'    => $date,

'note'    => $note,

'type'    => 'deposit'

],

/* SUCCESS */
'success_url' =>
'http://localhost/Colab/Public/stripe-success.php?session_id={CHECKOUT_SESSION_ID}',

/* CANCEL */
'cancel_url'  =>
'http://localhost/Colab/Public/deposit.php'

]);

/* RESPONSE */
echo json_encode([
"id" => $session->id
]);

}
catch(Exception $e){

echo json_encode([
"error" => $e->getMessage()
]);

}
?>