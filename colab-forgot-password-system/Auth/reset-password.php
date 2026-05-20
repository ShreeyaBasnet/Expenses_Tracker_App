<?php
/* ============================================================
   Reset Password - Expense Tracker
   Validates the reset token, checks expiration, and allows
   the user to set a new password securely.
   ============================================================ */

session_start();
include "../Includes/db.php";

/* CSRF TOKEN - Generate if not exists */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* Status messages */
$success = "";
$error = "";
$tokenValid = false;
$token = "";

/* =========================
   VALIDATE TOKEN FROM URL
========================= */

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
} elseif (isset($_POST['token'])) {
    $token = trim($_POST['token']);
}

/* Check if token is provided */
if (!empty($token)) {

    /* Validate token format (must be 64 hex characters) */
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        $error = "Invalid reset link.";
    } else {

        /* LOOK UP TOKEN IN DATABASE */
        $stmt = $conn->prepare(
            "SELECT id, email FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()"
        );
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $tokenValid = true;
        } else {
            $error = "This reset link has expired or is invalid. Please request a new one.";
        }
    }

} else {
    $error = "No reset token provided. Please use the link from your email.";
}

/* =========================
   HANDLE PASSWORD RESET
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {

    /* CSRF VALIDATION */
    if (
        !isset($_POST['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']
    ) {
        die("Invalid CSRF token");
    }

    /* GET PASSWORDS */
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    /* VALIDATE PASSWORDS */
    if (empty($newPassword) || empty($confirmPassword)) {
        $error = "Both password fields are required.";
    } elseif (strlen($newPassword) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {

        /* HASH THE NEW PASSWORD */
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        /*
         * UPDATE PASSWORD AND CLEAR RESET TOKEN
         * Setting reset_token and reset_token_expiry to NULL
         * ensures the token can only be used once.
         */
        $updateStmt = $conn->prepare(
            "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?"
        );
        $updateStmt->bind_param("ss", $hashedPassword, $token);

        if ($updateStmt->execute() && $updateStmt->affected_rows > 0) {
            $success = "Your password has been reset successfully! You can now login with your new password.";
            $tokenValid = false; /* Hide the form after success */
        } else {
            $error = "Failed to reset password. The link may have expired.";
        }
    }

    /* Regenerate CSRF token after form submission */
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Expense Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/login.css">
</head>
<body>

<div class="wrapper">

    <!-- LEFT PANEL -->
    <div class="left">
        <div class="hero">
            <h1>Set your new password</h1>
            <p>Choose a strong password to keep your account secure.</p>
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
            <div class="feat">Minimum 6 characters</div>
            <div class="feat">Passwords must match</div>
            <div class="feat">Securely hashed storage</div>
            <div class="feat">One-time reset link</div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right">

        <div class="form">
            <h2>New password</h2>
            <p class="sub">Enter and confirm your new password</p>

            <!-- SUCCESS MESSAGE -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <div class="back-link" style="margin-top: 16px;">
                    <a href="login.php" style="color: #a855f7; font-weight: 500;">Go to login &rarr;</a>
                </div>
            <?php endif; ?>

            <!-- ERROR MESSAGE -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php if (!$tokenValid): ?>
                    <div class="back-link" style="margin-top: 16px;">
                        <a href="forgot-password.php">Request a new reset link &rarr;</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- PASSWORD RESET FORM (only shown if token is valid) -->
            <?php if ($tokenValid): ?>
                <form method="POST">
                    <!-- CSRF TOKEN -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <!-- RESET TOKEN -->
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div class="input">
                        <input
                            type="password"
                            name="new_password"
                            placeholder="New password"
                            required
                            minlength="6"
                            autocomplete="new-password"
                        >
                    </div>

                    <div class="password-requirements">
                        Minimum 6 characters required
                    </div>

                    <div class="input">
                        <input
                            type="password"
                            name="confirm_password"
                            placeholder="Confirm new password"
                            required
                            minlength="6"
                            autocomplete="new-password"
                        >
                    </div>

                    <button type="submit">Reset password</button>
                </form>
            <?php endif; ?>

            <!-- BACK TO LOGIN (shown on form page) -->
            <?php if ($tokenValid): ?>
                <div class="back-link">
                    <a href="login.php">&larr; Back to login</a>
                </div>
            <?php endif; ?>

        </div>

    </div>

</div>

</body>
</html>
