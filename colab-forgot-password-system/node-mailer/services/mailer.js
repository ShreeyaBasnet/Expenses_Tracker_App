/* ============================================================
   Mailer Service - Expense Tracker
   Handles email sending via Nodemailer with Gmail SMTP
   ============================================================ */

const nodemailer = require("nodemailer");

/* =========================
   CREATE SMTP TRANSPORTER
   =========================
   Uses Gmail SMTP with App Password authentication.
   See README for instructions on generating a Gmail App Password.
========================= */

const transporter = nodemailer.createTransport({
    service: "gmail",
    auth: {
        user: process.env.GMAIL_USER,
        pass: process.env.GMAIL_APP_PASSWORD
    }
});

/* Verify SMTP connection on startup */
transporter.verify((error) => {
    if (error) {
        console.error("SMTP Connection Error:", error.message);
        console.error("Please check your GMAIL_USER and GMAIL_APP_PASSWORD in .env");
    } else {
        console.log("SMTP connected - Ready to send emails");
    }
});

/* =========================
   GENERATE HTML EMAIL TEMPLATE
   =========================
   Creates a professional, responsive HTML email
   for password reset with a reset button and fallback link.
========================= */

function generateResetEmailHTML(name, resetLink) {
    const appName = process.env.APP_NAME || "Expense Tracker";

    return `
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Password Reset - ${appName}</title>
    </head>
    <body style="
        margin: 0;
        padding: 0;
        background-color: #0a0a0a;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #ffffff;
    ">
        <!-- OUTER TABLE -->
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="
            background-color: #0a0a0a;
            padding: 40px 20px;
        ">
            <tr>
                <td align="center">

                    <!-- MAIN CONTAINER -->
                    <table role="presentation" width="520" cellspacing="0" cellpadding="0" style="
                        background-color: #111111;
                        border-radius: 16px;
                        overflow: hidden;
                        max-width: 520px;
                    ">

                        <!-- HEADER GRADIENT -->
                        <tr>
                            <td style="
                                background: linear-gradient(135deg, #6c5ce7, #a855f7);
                                padding: 32px 36px;
                                text-align: center;
                            ">
                                <h1 style="
                                    margin: 0;
                                    font-size: 22px;
                                    font-weight: 700;
                                    color: #ffffff;
                                    letter-spacing: -0.3px;
                                ">${appName}</h1>
                                <p style="
                                    margin: 8px 0 0 0;
                                    font-size: 14px;
                                    color: rgba(255,255,255,0.85);
                                ">Password Reset Request</p>
                            </td>
                        </tr>

                        <!-- BODY -->
                        <tr>
                            <td style="padding: 36px;">

                                <p style="
                                    font-size: 16px;
                                    color: #ffffff;
                                    margin: 0 0 8px 0;
                                ">Hi <strong>${name}</strong>,</p>

                                <p style="
                                    font-size: 14px;
                                    color: #aaaaaa;
                                    line-height: 1.6;
                                    margin: 0 0 28px 0;
                                ">
                                    We received a request to reset your password.
                                    Click the button below to create a new password.
                                </p>

                                <!-- RESET BUTTON -->
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td align="center" style="padding: 0 0 28px 0;">
                                            <a href="${resetLink}" target="_blank" style="
                                                display: inline-block;
                                                padding: 14px 40px;
                                                background: linear-gradient(135deg, #6c5ce7, #a855f7);
                                                color: #ffffff;
                                                text-decoration: none;
                                                font-size: 15px;
                                                font-weight: 600;
                                                border-radius: 10px;
                                                letter-spacing: 0.3px;
                                            ">Reset Password</a>
                                        </td>
                                    </tr>
                                </table>

                                <!-- EXPIRATION WARNING -->
                                <div style="
                                    background: rgba(255, 71, 87, 0.1);
                                    border: 1px solid rgba(255, 71, 87, 0.2);
                                    border-radius: 10px;
                                    padding: 14px 16px;
                                    margin: 0 0 24px 0;
                                ">
                                    <p style="
                                        font-size: 13px;
                                        color: #ff4757;
                                        margin: 0;
                                        line-height: 1.5;
                                    ">
                                        &#9888; This link expires in <strong>1 hour</strong>.
                                        After that, you'll need to request a new reset link.
                                    </p>
                                </div>

                                <!-- FALLBACK LINK -->
                                <p style="
                                    font-size: 13px;
                                    color: #666666;
                                    line-height: 1.5;
                                    margin: 0 0 8px 0;
                                ">
                                    If the button doesn't work, copy and paste this link into your browser:
                                </p>
                                <p style="
                                    font-size: 12px;
                                    color: #a855f7;
                                    word-break: break-all;
                                    margin: 0 0 24px 0;
                                    line-height: 1.5;
                                ">
                                    <a href="${resetLink}" target="_blank" style="
                                        color: #a855f7;
                                        text-decoration: underline;
                                    ">${resetLink}</a>
                                </p>

                                <hr style="
                                    border: none;
                                    border-top: 1px solid #222222;
                                    margin: 0 0 20px 0;
                                ">

                                <p style="
                                    font-size: 12px;
                                    color: #555555;
                                    line-height: 1.5;
                                    margin: 0;
                                ">
                                    If you didn't request this password reset, you can safely ignore this email.
                                    Your password will remain unchanged.
                                </p>

                            </td>
                        </tr>

                        <!-- FOOTER -->
                        <tr>
                            <td style="
                                padding: 20px 36px;
                                text-align: center;
                                border-top: 1px solid #1a1a1a;
                            ">
                                <p style="
                                    font-size: 11px;
                                    color: #444444;
                                    margin: 0;
                                ">
                                    &copy; ${new Date().getFullYear()} ${appName}. All rights reserved.
                                </p>
                            </td>
                        </tr>

                    </table>

                </td>
            </tr>
        </table>
    </body>
    </html>
    `;
}

/* =========================
   SEND RESET EMAIL
   =========================
   Sends the password reset email to the specified user.
   Parameters:
     - email: recipient email address
     - name: recipient's name (for personalization)
     - resetLink: the full password reset URL with token
   Returns: Nodemailer send result with messageId
========================= */

async function sendResetEmail(email, name, resetLink) {
    const appName = process.env.APP_NAME || "Expense Tracker";

    /* Build the email options */
    const mailOptions = {
        from: `"${appName}" <${process.env.GMAIL_USER}>`,
        to: email,
        subject: `${appName} - Password Reset Request`,
        /* Plain text fallback for email clients that don't support HTML */
        text: `Hi ${name},\n\nWe received a request to reset your password.\n\nClick the link below to reset your password:\n${resetLink}\n\nThis link expires in 1 hour.\n\nIf you didn't request this, you can safely ignore this email.\n\n- ${appName} Team`,
        /* HTML version with styled template */
        html: generateResetEmailHTML(name, resetLink)
    };

    /* Send the email and return the result */
    const info = await transporter.sendMail(mailOptions);
    console.log(`Reset email sent to ${email} - Message ID: ${info.messageId}`);
    return info;
}

/* Export the sendResetEmail function */
module.exports = { sendResetEmail };
