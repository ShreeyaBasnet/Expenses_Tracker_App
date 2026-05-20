# Expense Tracker - Forgot Password & Reset Password System

A secure password reset workflow for PHP + MySQL authentication using Node.js + Nodemailer for email delivery.

## Tech Stack

- **Backend:** PHP 8+, MySQLi (prepared statements)
- **Database:** MySQL
- **Email Service:** Node.js 18+, Express.js, Nodemailer
- **Frontend:** HTML/CSS (responsive, modern design)
- **Auth:** Session-based authentication

---

## Project Structure

```
expense-tracker/
├── Auth/
│   ├── login.php              # Login & Signup (existing auth)
│   ├── forgot-password.php    # Forgot password form & token generation
│   └── reset-password.php     # Reset password form & validation
├── Assets/
│   └── login.css              # Shared stylesheet for auth pages
├── Database/
│   └── migration.sql          # SQL migration for reset columns
├── Includes/
│   └── db.php                 # MySQL database connection
├── Public/
│   └── dashboard.php          # Protected dashboard page
├── node-mailer/
│   ├── server.js              # Express server entry point
│   ├── routes/
│   │   └── mail.js            # POST /api/send-reset-email
│   ├── services/
│   │   └── mailer.js          # Nodemailer transporter & HTML template
│   ├── package.json           # Node.js dependencies
│   ├── .env.example           # Environment variable template
│   └── .gitignore             # Ignores node_modules and .env
└── README.md                  # This file
```

---

## Setup Instructions

### 1. Database Migration

Run the SQL migration to add reset columns to your `users` table:

```sql
-- Connect to your MySQL database and run:
SOURCE /path/to/expense-tracker/Database/migration.sql;

-- Or run directly:
ALTER TABLE users
    ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL AFTER password,
    ADD COLUMN reset_token_expiry DATETIME DEFAULT NULL AFTER reset_token;

ALTER TABLE users ADD INDEX idx_reset_token (reset_token);
```

### 2. Configure Database Connection

Edit `Includes/db.php` with your MySQL credentials:

```php
$host = "localhost";
$username = "root";
$password = "";
$database = "expense_tracker";
```

### 3. Set Up Node.js Mail Server

```bash
# Navigate to the node-mailer directory
cd expense-tracker/node-mailer

# Install dependencies
npm install

# Copy environment template
cp .env.example .env

# Edit .env with your credentials
nano .env
```

### 4. Configure Gmail App Password

To use Gmail SMTP with Nodemailer, you need a **Gmail App Password** (not your regular Gmail password):

1. Go to [Google Account Security](https://myaccount.google.com/security)
2. Enable **2-Step Verification** if not already enabled
3. Go to [App Passwords](https://myaccount.google.com/apppasswords)
4. Select **Mail** as the app and your device
5. Click **Generate**
6. Copy the 16-character password
7. Paste it in your `.env` file as `GMAIL_APP_PASSWORD`

**Your `.env` file should look like:**

```env
PORT=3000
GMAIL_USER=yourname@gmail.com
GMAIL_APP_PASSWORD=abcd efgh ijkl mnop
APP_NAME=Expense Tracker
```

### 5. Start the Node.js Mail Server

```bash
cd expense-tracker/node-mailer

# Production
npm start

# Development (auto-restart on file changes)
npm run dev
```

You should see:

```
Mail service running on http://localhost:3000
SMTP connected - Ready to send emails
```

### 6. Start PHP Server

```bash
# From the project root
cd expense-tracker
php -S localhost:8000
```

---

## Password Reset Flow

### How It Works

```
1. User clicks "Forgot password?" on login page
   └── Navigates to forgot-password.php

2. User enters their email address
   └── PHP validates email, generates secure token

3. Token stored in database with 1-hour expiry
   └── bin2hex(random_bytes(32)) = 64-char hex token

4. PHP sends POST request to Node.js API
   └── cURL → http://localhost:3000/api/send-reset-email

5. Node.js sends HTML email via Gmail SMTP
   └── Nodemailer → Gmail → User's inbox

6. User clicks reset link in email
   └── Navigates to reset-password.php?token=abc123...

7. User enters new password (min 6 chars) + confirmation
   └── PHP validates token, checks expiry

8. Password updated, token cleared from database
   └── password_hash() + NULL token fields
```

### API Endpoint

**POST** `http://localhost:3000/api/send-reset-email`

```json
{
    "email": "user@example.com",
    "name": "John Doe",
    "resetLink": "http://localhost:8000/Auth/reset-password.php?token=abc123..."
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Password reset email sent successfully",
    "messageId": "<unique-id@gmail.com>"
}
```

### PHP → Node.js Integration (cURL)

The `forgot-password.php` file calls the Node.js API using PHP's cURL:

```php
$nodeApiUrl = "http://localhost:3000/api/send-reset-email";

$postData = json_encode([
    "email"     => $email,
    "name"      => $user['name'],
    "resetLink" => $resetLink
]);

$ch = curl_init($nodeApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Content-Length: " . strlen($postData)
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
```

---

## Security Features

| Feature | Implementation |
|---------|---------------|
| SQL Injection Prevention | MySQLi prepared statements with `bind_param()` |
| CSRF Protection | Random token per session, validated on every POST |
| User Enumeration Prevention | Same success message whether email exists or not |
| Secure Token Generation | `bin2hex(random_bytes(32))` — 64 hex characters |
| Token Expiration | 1-hour expiry, enforced in SQL query |
| One-Time Use Tokens | Token cleared from DB after successful reset |
| Session Hardening | `session_regenerate_id(true)` on login |
| Password Hashing | `password_hash()` with `PASSWORD_DEFAULT` (bcrypt) |
| Input Sanitization | `filter_var()` + `htmlspecialchars()` + `trim()` |
| Token Format Validation | Regex check for 64-char hex string |

---

## Troubleshooting

### "SMTP Connection Error"
- Verify `GMAIL_USER` and `GMAIL_APP_PASSWORD` in `.env`
- Make sure 2-Step Verification is enabled on your Google account
- Generate a new App Password if the current one isn't working

### "Unable to send reset email"
- Check that the Node.js server is running on port 3000
- Check PHP error logs for cURL connection errors
- Verify `curl` extension is enabled in PHP (`php -m | grep curl`)

### "This reset link has expired"
- Token expires after 1 hour — request a new one
- Check that your MySQL server's timezone matches PHP's timezone

### Emails going to spam
- Use a verified Gmail account
- Consider setting up SPF/DKIM records for production
- For production, use a dedicated email service (SendGrid, Mailgun, etc.)
