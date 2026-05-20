<?php
/* ============================================================
   Forgot Password - Expense Tracker
   Generates a secure reset token, stores it in the database,
   and sends a reset email via the Node.js mail API.
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

/* =========================
   HANDLE FORM SUBMISSION
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* CSRF VALIDATION */
    if (
        !isset($_POST['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']
    ) {
        die("Invalid CSRF token");
    }

    /* SANITIZE EMAIL */
    $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));

    /* VALIDATE EMAIL FORMAT */
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {

        /*
         * IMPORTANT: Always show the same success message whether
         * the email exists or not. This prevents user enumeration attacks.
         */
        $genericSuccess = "If an account with that email exists, a password reset link has been sent. Please check your inbox.";

        /* CHECK IF USER EXISTS */
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            /* GENERATE SECURE TOKEN */
            $token = bin2hex(random_bytes(32));

            /* SET EXPIRY TO 1 HOUR FROM NOW */
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

            /* STORE TOKEN AND EXPIRY IN DATABASE */
            $updateStmt = $conn->prepare(
                "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?"
            );
            $updateStmt->bind_param("sss", $token, $expiry, $email);
            $updateStmt->execute();

            /*
             * BUILD THE RESET LINK
             * Adjust the base URL to match your server configuration.
             * Uses the current protocol and host for portability.
             */
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $resetLink = $protocol . "://" . $host . "/expense-tracker/Auth/reset-password.php?token=" . $token;

            /*
             * SEND RESET EMAIL VIA NODE.JS API
             * The Node.js server handles email delivery via Nodemailer.
             * Default Node.js API URL: http://localhost:3000/api/send-reset-email
             */
            $nodeApiUrl = "http://localhost:3000/api/send-reset-email";

            /* Prepare the POST data */
            $postData = json_encode([
                "email" => $email,
                "name"  => $user['name'],
                "resetLink" => $resetLink
            ]);

            /* Send request using cURL */
            $ch = curl_init($nodeApiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Content-Length: " . strlen($postData)
            ]);
            /* Timeout after 10 seconds */
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            /* Check if email was sent successfully */
            if ($httpCode === 200) {
                $success = $genericSuccess;
            } else {
                /*
                 * Log the error for debugging but show a generic message
                 * to avoid leaking internal details to the user.
                 */
                error_log("Mail API Error: HTTP $httpCode - $curlError - Response: $response");
                $error = "Unable to send reset email. Please try again later.";
            }

        } else {
            /*
             * User does NOT exist - show the SAME success message
             * to prevent user enumeration.
             */
            $success = $genericSuccess;
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
    <title>Forgot Password - Expense Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/login.css">
</head>
<body>

<div class="wrapper">

    <!-- LEFT PANEL -->
    <div class="left">
        <div class="hero">
            <h1>Forgot your password?</h1>
            <p>No worries. Enter your email and we'll send you a reset link.</p>
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
            <div class="feat">Secure password reset</div>
            <div class="feat">Token expires in 1 hour</div>
            <div class="feat">One-time use link</div>
            <div class="feat">Email verification</div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right">

        <div class="form">
            <h2>Reset password</h2>
            <p class="sub">Enter your email to receive a reset link</p>

            <!-- SUCCESS MESSAGE -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- ERROR MESSAGE -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <!-- CSRF TOKEN -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="input">
                    <input
                        type="email"
                        name="email"
                        placeholder="Enter your email address"
                        required
                        autocomplete="email"
                    >
                </div>

                <button type="submit">Send reset link</button>
            </form>

            <!-- BACK TO LOGIN -->
            <div class="back-link">
                <a href="login.php">&larr; Back to login</a>
            </div>
        </div>

    </div>

</div>

</body>
</html>
