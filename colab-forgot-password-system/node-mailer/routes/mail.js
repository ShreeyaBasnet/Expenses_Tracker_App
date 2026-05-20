/* ============================================================
   Mail Routes - Expense Tracker
   API endpoint for sending password reset emails
   ============================================================ */

const express = require("express");
const router = express.Router();
const { sendResetEmail } = require("../services/mailer");

/* =========================
   POST /api/send-reset-email
   =========================
   Expects JSON body:
   {
     "email": "user@example.com",
     "name": "John Doe",
     "resetLink": "http://localhost/expense-tracker/Auth/reset-password.php?token=abc123..."
   }
========================= */

router.post("/send-reset-email", async (req, res) => {
    try {
        /* Extract data from request body */
        const { email, name, resetLink } = req.body;

        /* Validate required fields */
        if (!email || !name || !resetLink) {
            return res.status(400).json({
                success: false,
                message: "Missing required fields: email, name, resetLink"
            });
        }

        /* Validate email format */
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return res.status(400).json({
                success: false,
                message: "Invalid email format"
            });
        }

        /* Send the reset email */
        const result = await sendResetEmail(email, name, resetLink);

        /* Return success response */
        return res.status(200).json({
            success: true,
            message: "Password reset email sent successfully",
            messageId: result.messageId
        });

    } catch (error) {
        console.error("Error sending reset email:", error.message);

        return res.status(500).json({
            success: false,
            message: "Failed to send email. Please try again later."
        });
    }
});

module.exports = router;
