<?php
session_start();
include "../Includes/db.php";

/* ================= SIGNUP ================= */
if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // CHECK IF USER EXISTS
    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('User already exists');</script>";
    } else {

        // INSERT USER
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {

            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $name;

            header("Location: ../Public/dashboard.php");
            exit();
        }
    }
}

/* ================= LOGIN ================= */
if (isset($_POST['loginEmail']) && isset($_POST['loginPassword'])) {

    $email = $_POST['loginEmail'];
    $password = $_POST['loginPassword'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name'];

            header("Location: ../Public/dashboard.php");
            exit();

        } else {
            echo "<script>alert('Wrong password');</script>";
        }

    } else {
        echo "<script>alert('User not found');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>बchat Login</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../Assets/login.css">

</head>

<body>

<div class="wrapper">

  <!-- LEFT -->
  <div class="left">
    <div class="hero">
      <h1>Track your money smarter</h1>
      <p>Clean insights. Better decisions. Total control.</p>
    </div>

    <div>
      <div class="chart">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
      </div>
      <div class="chart-label">Monthly Overview</div>
    </div>

    <div class="features">
      <div class="feat">Real-time expense tracking</div>
      <div class="feat">Smart budgeting insights</div>
      <div class="feat">Clean & minimal dashboard</div>
      <div class="feat">Secure & private data</div>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="right">

    <!-- SIGNUP -->
    <div class="form" id="signupFormBox">
      <h2>Create account</h2>
      <p class="sub">Start managing finances</p>

      <form method="POST">
        <div class="input">
          <input type="text" name="name" placeholder="Full name" required>
        </div>

        <div class="input">
          <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="input">
          <input type="password" name="password" placeholder="Password" required>
        </div>

        <button type="submit">Create account</button>
      </form>
    </div>

    <!-- LOGIN -->
    <div class="form" id="loginFormBox" style="display:none;">
      <h2>Welcome back</h2>
      <p class="sub">Login to continue</p>

      <form method="POST">
        <div class="input">
          <input type="email" name="loginEmail" placeholder="Email" required>
        </div>

        <div class="input">
          <input type="password" name="loginPassword" placeholder="Password" required>
        </div>

        <button type="submit">Login</button>
      </form>
    </div>

    <div class="switch" onclick="toggleForm()" id="switchText">
      Already have an account? <span>Login</span>
    </div>

  </div>

</div>
<style>/* =========================
   VARIABLES (GLOBAL THEME)
========================= */
:root {
  --navy: #1B2E4B;
  --navy-light: #243d63;
  --emerald: #2E7D5A;
  --emerald-light: #3a9e72;
  --bg: #F5F7FA;
  --white: #ffffff;
  --text: #1a2235;
  --gray-300: #d4dae4;
  --gray-500: #8896ab;

  --font: 'Sora', sans-serif;
  --font-body: 'DM Sans', sans-serif;
}

/* =========================
   RESET
========================= */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: var(--font-body);
  background: var(--bg);
  color: var(--text);
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
}

/* =========================
   WRAPPER
========================= */
.wrapper {
  width: 900px;
  height: 550px;
  display: flex;
  background: var(--white);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.08);
}

/* =========================
   LEFT PANEL (FIXED COLORS)
========================= */
.left {
  width: 50%;
  background: var(--navy);
  color: white;
  padding: 50px 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.hero h1 {
  font-family: var(--font);
  font-size: 32px;
  font-weight: 700;
  color: white; /* FIXED */
}

.hero p {
  margin-top: 10px;
  color: #cbd5e1;
}

/* =========================
   CHART
========================= */
.chart {
  display: flex;
  gap: 10px;
  margin: 30px 0 10px;
}

.bar {
  width: 45px;
  height: 20px;
  background: linear-gradient(180deg, var(--emerald), #1B5E4B);
  border-radius: 6px;
}

.bar:nth-child(2) { height: 30px; }
.bar:nth-child(3) { height: 25px; }
.bar:nth-child(4) { height: 40px; }
.bar:nth-child(5) { height: 28px; }

.chart-label {
  font-size: 13px;
  color: #cbd5e1;
}

/* =========================
   FEATURES LIST
========================= */
.features {
  margin-top: 20px;
}

.feat {
  margin-bottom: 10px;
  font-size: 14px;
  color: #e2e8f0;
}

.feat::before {
  content: "✔ ";
  color: var(--emerald);
  font-weight: bold;
}

/* =========================
   RIGHT PANEL
========================= */
.right {
  width: 50%;
  padding: 50px 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

/* =========================
   FORM
========================= */
.form {
  width: 100%;
}

.form h2 {
  font-family: var(--font);
  font-size: 22px;
  margin-bottom: 5px;
}

.sub {
  color: var(--gray-500);
  font-size: 14px;
  margin-bottom: 20px;
}

/* =========================
   INPUT
========================= */
.input {
  margin-bottom: 15px;
}

.input input {
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  border: 1px solid var(--gray-300);
  font-size: 14px;
  font-family: var(--font-body);
}

.input input:focus {
  outline: none;
  border-color: var(--emerald);
}

/* =========================
   BUTTON
========================= */
button {
  width: 100%;
  padding: 12px;
  border-radius: 12px;
  border: none;
  background: var(--emerald);
  color: white;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: 0.2s;
}

button:hover {
  background: var(--emerald-light);
}

/* =========================
   SWITCH TEXT
========================= */
.switch {
  margin-top: 15px;
  text-align: center;
  font-size: 14px;
  cursor: pointer;
}

.switch span {
  color: var(--emerald);
  font-weight: 600;
}

/* =========================
   RESPONSIVE
========================= */
@media (max-width: 900px) {
  .wrapper {
    flex-direction: column;
    height: auto;
    width: 90%;
  }

  .left, .right {
    width: 100%;
  }
}
</style>

<script>
let isLogin = false;

/* ===== MANUAL TOGGLE (CLICK) ===== */
function toggleForm() {
  const signup = document.getElementById("signupFormBox");
  const login = document.getElementById("loginFormBox");
  const text = document.getElementById("switchText");

  if (!isLogin) {
    signup.style.display = "none";
    login.style.display = "block";
    text.innerHTML = 'Don’t have an account? <span>Sign up</span>';
    isLogin = true;
  } else {
    signup.style.display = "block";
    login.style.display = "none";
    text.innerHTML = 'Already have an account? <span>Login</span>';
    isLogin = false;
  }
}

/* ===== AUTO OPEN (FROM LANDING PAGE) ===== */
window.onload = function () {
  const mode = localStorage.getItem("formMode");

  const signup = document.getElementById("signupFormBox");
  const login = document.getElementById("loginFormBox");
  const text = document.getElementById("switchText");

  if (mode === "login") {
    signup.style.display = "none";
    login.style.display = "block";
    text.innerHTML = 'Don’t have an account? <span>Sign up</span>';
    isLogin = true;
  } else {
    signup.style.display = "block";
    login.style.display = "none";
    text.innerHTML = 'Already have an account? <span>Login</span>';
    isLogin = false;
  }
};
</script>


</body>
</html>



