<?php
/* ============================================================
   Login & Signup - Expense Tracker
   Handles user registration and authentication
   ============================================================ */

session_start();
include "../Includes/db.php";

/* CSRF TOKEN - Generate if not exists */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =========================
   SIGNUP
========================= */

if (
    isset($_POST['name']) &&
    isset($_POST['email']) &&
    isset($_POST['password'])
) {

    /* CSRF VALIDATION */
    if (
        !isset($_POST['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']
    ) {
        die("Invalid CSRF token");
    }

    /* SANITIZE */
    $name = trim(htmlspecialchars($_POST['name']));
    $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'];

    /* VALIDATION */
    if (empty($name) || empty($email) || empty($password)) {
        $signupError = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signupError = "Invalid email address.";
    } elseif (strlen($password) < 6) {
        $signupError = "Password must be at least 6 characters.";
    } else {

        /* HASH PASSWORD */
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        /* CHECK EXISTING USER */
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $signupError = "User already exists.";
        } else {

            /* INSERT USER */
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashedPassword);

            if ($stmt->execute()) {
                /* SECURE SESSION */
                session_regenerate_id(true);
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $name;
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
) {

    /* CSRF VALIDATION */
    if (
        !isset($_POST['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']
    ) {
        die("Invalid CSRF token");
    }

    /* SANITIZE */
    $email = trim(filter_var($_POST['loginEmail'], FILTER_SANITIZE_EMAIL));
    $password = $_POST['loginPassword'];

    /* VALIDATION */
    if (empty($email) || empty($password)) {
        $loginError = "All fields are required.";
    } else {

        $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            /* VERIFY PASSWORD */
            if (password_verify($password, $user['password'])) {
                /* REGENERATE SESSION */
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['name'];
                header("Location: ../Public/dashboard.php");
                exit();
            } else {
                $loginError = "Wrong password.";
            }
        } else {
            $loginError = "User not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/login.css">
</head>
<body>

<div class="wrapper">

    <!-- LEFT PANEL -->
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

    <!-- RIGHT PANEL -->
    <div class="right">

        <!-- SIGNUP FORM -->
        <div class="form" id="signupFormBox">
            <h2>Create account</h2>
            <p class="sub">Start managing finances</p>

            <?php if (!empty($signupError)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($signupError) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
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

        <!-- LOGIN FORM -->
        <div class="form" id="loginFormBox" style="display:none;">
            <h2>Welcome back</h2>
            <p class="sub">Login to continue</p>

            <?php if (!empty($loginError)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($loginError) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="input">
                    <input type="email" name="loginEmail" placeholder="Email" required>
                </div>
                <div class="input">
                    <input type="password" name="loginPassword" placeholder="Password" required>
                </div>

                <!-- Forgot Password Link -->
                <div class="forgot-link">
                    <a href="forgot-password.php">Forgot password?</a>
                </div>

                <button type="submit">Login</button>
            </form>
        </div>

        <div class="switch" onclick="toggleForm()" id="switchText">
            Already have an account? <span>Login</span>
        </div>

    </div>

</div>

<script>
    let isLogin = false;

    /* Toggle between signup and login forms */
    function toggleForm() {
        const signup = document.getElementById("signupFormBox");
        const login = document.getElementById("loginFormBox");
        const text = document.getElementById("switchText");

        if (!isLogin) {
            signup.style.display = "none";
            login.style.display = "block";
            text.innerHTML = 'Don\'t have an account? <span>Sign up</span>';
            isLogin = true;
        } else {
            signup.style.display = "block";
            login.style.display = "none";
            text.innerHTML = 'Already have an account? <span>Login</span>';
            isLogin = false;
        }
    }

    /* Auto-open correct form based on localStorage */
    window.onload = function () {
        const mode = localStorage.getItem("formMode");
        const signup = document.getElementById("signupFormBox");
        const login = document.getElementById("loginFormBox");
        const text = document.getElementById("switchText");

        if (mode === "login") {
            signup.style.display = "none";
            login.style.display = "block";
            text.innerHTML = 'Don\'t have an account? <span>Sign up</span>';
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
