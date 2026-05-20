<?php
session_start();

require "../vendor/autoload.php";

\Stripe\Stripe::setApiKey(getenv("STRIPE_SECRET_KEY"));
header("Content-Type: application/json");

/* GET DATA */
$data = json_decode(file_get_contents("php://input"), true);

/* SAFETY CHECK */
if(!$data || !isset($data['amount'])){
echo json_encode([
"error" => "Invalid data received"
]);
exit;
}

$amount = (int)$data['amount'];
$date = $data['date'] ?? "";
$category = $data['category'] ?? "";
$description = $data['description'] ?? "";

/* STORE IN SESSION */
$_SESSION["expense_amount"] = $amount;
$_SESSION["expense_date"] = $date;
$_SESSION["expense_category"] = $category;
$_SESSION["expense_description"] = $description;

try{

$session = \Stripe\Checkout\Session::create([

'payment_method_types' => ['card'],

'line_items' => [[
'price_data' => [
'currency' => 'usd',
'product_data' => [
'name' => 'Expense Payment'
],
'unit_amount' => $amount * 100
],
'quantity' => 1
]],

'mode' => 'payment',

'success_url' => 'http://localhost/Colab/Public/expense-success.php',
'cancel_url' => 'http://localhost/Colab/Public/expense.php'

]);

echo json_encode([
"id" => $session->id
]);

}
catch(Exception $e){

echo json_encode([
"error" => $e->getMessage()
]);

}