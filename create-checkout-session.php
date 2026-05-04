<?php
session_start();
require "../vendor/autoload.php";
include "../Includes/db.php";

\Stripe\Stripe::setApiKey(getenv("STRIPE_SECRET_KEY"));
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $_SESSION['user_id'];

$amount = (int)$data["amount"];
$date   = $data["date"];
$note   = $data["note"] ?? "";

// store in session (optional fallback)
$_SESSION["deposit_amount"] = $amount;
$_SESSION["deposit_date"]   = $date;
$_SESSION["deposit_note"]   = $note;

$session = \Stripe\Checkout\Session::create([
  'payment_method_types' => ['card'],
  'mode' => 'payment',
  'line_items' => [[
    'price_data' => [
      'currency' => 'usd',
      'product_data' => ['name' => 'Wallet Top Up'],
      'unit_amount' => $amount * 100
    ],
    'quantity' => 1
  ]],

  // 🔑 IMPORTANT: attach metadata
  'metadata' => [
    'user_id' => $user_id,
    'amount'  => $amount,
    'date'    => $date,
    'note'    => $note,
    'type'    => 'deposit'
  ],

  'success_url' => 'http://localhost/Colab/Public/stripe-success.php?session_id={CHECKOUT_SESSION_ID}',
  'cancel_url'  => 'http://localhost/Colab/Public/deposit.php'
]);

echo json_encode(["id" => $session->id]);