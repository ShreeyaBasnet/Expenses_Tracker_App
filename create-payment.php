<?php

require "stripe-config.php";

header("Content-Type: application/json");

$data=json_decode(
file_get_contents("php://input"),
true
);

if(
!isset($data["amount"])
){
echo json_encode([
"error"=>"No amount sent"
]);
exit;
}

$amount=$data["amount"];


/* Stripe minimum usually 50 cents */
if($amount<1){
$amount=1;
}

try{

$intent=
\Stripe\PaymentIntent::create([
"amount"=>$amount*100,
"currency"=>"usd"
]);

echo json_encode([
"clientSecret" =>
$intent->client_secret
]);

}

catch(Exception $e){

echo json_encode([
"error"=>$e->getMessage()
]);

}

?>