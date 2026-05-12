<?php

session_start();
include "../Includes/db.php";

/* CSRF TOKEN */
if(empty($_SESSION['csrf_token'])){

$_SESSION['csrf_token'] =
bin2hex(random_bytes(32));

}

/* =========================
   SIGNUP
========================= */

if (
isset($_POST['name']) &&
isset($_POST['email']) &&
isset($_POST['password'])
){

/* CSRF VALIDATION */
if(
!isset($_POST['csrf_token']) ||
$_POST['csrf_token'] !== $_SESSION['csrf_token']
){

die("Invalid CSRF token");

}

/* SANITIZE */

$name =
trim(
htmlspecialchars($_POST['name'])
);

$email =
trim(
filter_var(
$_POST['email'],
FILTER_SANITIZE_EMAIL
)
);

$password =
$_POST['password'];

/* VALIDATION */

if(
empty($name) ||
empty($email) ||
empty($password)
){

echo "<script>alert('All fields required');</script>";

}
elseif(
!filter_var($email, FILTER_VALIDATE_EMAIL)
){

echo "<script>alert('Invalid email');</script>";

}
elseif(strlen($password) < 6){

echo "<script>alert('Password must be at least 6 characters');</script>";

}
else{

/* HASH PASSWORD */

$hashedPassword =
password_hash(
$password,
PASSWORD_DEFAULT
);

/* CHECK EXISTING USER */

$check =
$conn->prepare(
"SELECT id FROM users WHERE email=?"
);

$check->bind_param(
"s",
$email
);

$check->execute();

$result =
$check->get_result();

/* USER EXISTS */

if ($result->num_rows > 0) {

echo "<script>alert('User already exists');</script>";

}
else {

/* INSERT USER */

$stmt =
$conn->prepare(
"INSERT INTO users
(name, email, password)
VALUES (?, ?, ?)"
);

$stmt->bind_param(
"sss",
$name,
$email,
$hashedPassword
);

/* SUCCESS */

if ($stmt->execute()) {

/* SECURE SESSION */

session_regenerate_id(true);

$_SESSION['user_id'] =
$stmt->insert_id;

$_SESSION['username'] =
$name;

header("Location: ../Public/dashboard.php");
exit();

}

}

}

}

/* =========================
   LOGIN
========================= */

if (
isset($_POST['loginEmail']) &&
isset($_POST['loginPassword'])
){

/* CSRF VALIDATION */

if(
!isset($_POST['csrf_token']) ||
$_POST['csrf_token'] !== $_SESSION['csrf_token']
){

die("Invalid CSRF token");

}

/* SANITIZE */

$email =
trim(
filter_var(
$_POST['loginEmail'],
FILTER_SANITIZE_EMAIL
)
);

$password =
$_POST['loginPassword'];

/* VALIDATION */

if(
empty($email) ||
empty($password)
){

echo "<script>alert('All fields required');</script>";

}
else{

$stmt =
$conn->prepare(
"SELECT * FROM users
WHERE email=?"
);

$stmt->bind_param(
"s",
$email
);

$stmt->execute();

$result =
$stmt->get_result();

if ($result->num_rows > 0) {

$user =
$result->fetch_assoc();

/* VERIFY PASSWORD */

if (
password_verify(
$password,
$user['password']
)
) {

/* REGENERATE SESSION */

session_regenerate_id(true);

$_SESSION['user_id'] =
$user['id'];

$_SESSION['username'] =
$user['name'];

header("Location: ../Public/dashboard.php");
exit();

}
else {

echo "<script>alert('Wrong password');</script>";

}

}
else {

echo "<script>alert('User not found');</script>";

}

}

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Expense Tracker Login</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="../Assets/login.css">

</head>

<body>

<div class="wrapper">

<!-- LEFT -->
<div class="left">

<div class="hero">

<h1>
Track your money smarter
</h1>

<p>
Clean insights. Better decisions. Total control.
</p>

</div>

<div>

<div class="chart">

<div class="bar"></div>
<div class="bar"></div>
<div class="bar"></div>
<div class="bar"></div>
<div class="bar"></div>

</div>

<div class="chart-label">
Monthly Overview
</div>

</div>

<div class="features">

<div class="feat">
Real-time expense tracking
</div>

<div class="feat">
Smart budgeting insights
</div>

<div class="feat">
Clean & minimal dashboard
</div>

<div class="feat">
Secure & private data
</div>

</div>

</div>

<!-- RIGHT -->
<div class="right">

<!-- SIGNUP -->
<div class="form" id="signupFormBox">

<h2>
Create account
</h2>

<p class="sub">
Start managing finances
</p>

<form method="POST">

<!-- CSRF -->
<input
type="hidden"
name="csrf_token"
value="<?= $_SESSION['csrf_token'] ?>"
>

<div class="input">

<input
type="text"
name="name"
placeholder="Full name"
required
>

</div>

<div class="input">

<input
type="email"
name="email"
placeholder="Email"
required
>

</div>

<div class="input">

<input
type="password"
name="password"
placeholder="Password"
required
>

</div>

<button type="submit">
Create account
</button>

</form>

</div>

<!-- LOGIN -->
<div class="form"
id="loginFormBox"
style="display:none;">

<h2>
Welcome back
</h2>

<p class="sub">
Login to continue
</p>

<form method="POST">

<!-- CSRF -->
<input
type="hidden"
name="csrf_token"
value="<?= $_SESSION['csrf_token'] ?>"
>

<div class="input">

<input
type="email"
name="loginEmail"
placeholder="Email"
required
>

</div>

<div class="input">

<input
type="password"
name="loginPassword"
placeholder="Password"
required
>

</div>

<button type="submit">
Login
</button>

</form>

</div>

<div
class="switch"
onclick="toggleForm()"
id="switchText"
>

Already have an account?
<span>Login</span>

</div>

</div>

</div>

<script>

let isLogin = false;

/* TOGGLE */
function toggleForm(){

const signup =
document.getElementById(
"signupFormBox"
);

const login =
document.getElementById(
"loginFormBox"
);

const text =
document.getElementById(
"switchText"
);

if(!isLogin){

signup.style.display =
"none";

login.style.display =
"block";

text.innerHTML =
'Don’t have an account? <span>Sign up</span>';

isLogin = true;

}
else{

signup.style.display =
"block";

login.style.display =
"none";

text.innerHTML =
'Already have an account? <span>Login</span>';

isLogin = false;

}

}

/* AUTO OPEN */
window.onload = function(){

const mode =
localStorage.getItem(
"formMode"
);

const signup =
document.getElementById(
"signupFormBox"
);

const login =
document.getElementById(
"loginFormBox"
);

const text =
document.getElementById(
"switchText"
);

if(mode === "login"){

signup.style.display =
"none";

login.style.display =
"block";

text.innerHTML =
'Don’t have an account? <span>Sign up</span>';

isLogin = true;

}
else{

signup.style.display =
"block";

login.style.display =
"none";

text.innerHTML =
'Already have an account? <span>Login</span>';

isLogin = false;

}

};

</script>

</body>
</html>