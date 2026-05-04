<?php
require "../vendor/autoload.php";
include "../Includes/db.php";

\Stripe\Stripe::setApiKey(getenv("STRIPE_SECRET_KEY"));
// ⚠️ set your webhook secret (from Stripe dashboard)
$endpoint_secret = "whsec_XXXXXXXX";

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

try {
  $event = \Stripe\Webhook::constructEvent(
    $payload, $sig_header, $endpoint_secret
  );
} catch(Exception $e) {
  http_response_code(400);
  exit();
}

// 🎯 Handle event
if ($event->type == 'checkout.session.completed') {

  $session = $event->data->object;

  // only process paid sessions
  if ($session->payment_status == 'paid') {

    $meta = $session->metadata;

    $user_id = $meta->user_id;
    $amount  = $meta->amount;
    $date    = $meta->date;
    $note    = $meta->note;

    // 🧠 Prevent duplicate inserts (IMPORTANT)
    $check = $conn->prepare("SELECT id FROM transactions WHERE description=?");
    $check->bind_param("s", $session->id);
    $check->execute();
    $exists = $check->get_result()->num_rows;

    if ($exists == 0) {

      // insert deposit
      $stmt = $conn->prepare("
        INSERT INTO deposits (user_id, amount, date, note)
        VALUES (?, ?, ?, ?)
      ");
      $stmt->bind_param("idss", $user_id, $amount, $date, $note);
      $stmt->execute();

      // transaction log (use session id to avoid duplicates)
      $stmt2 = $conn->prepare("
        INSERT INTO transactions (user_id, type, amount, description)
        VALUES (?, 'deposit', ?, ?)
      ");
      $stmt2->bind_param("ids", $user_id, $amount, $session->id);
      $stmt2->execute();
    }
  }
}

http_response_code(200);