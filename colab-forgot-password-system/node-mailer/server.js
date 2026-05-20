/* ============================================================
   Express Server - Expense Tracker Mail Service
   Handles password reset email delivery via Nodemailer
   ============================================================ */

/* Load environment variables from .env file */
require("dotenv").config();

const express = require("express");
const cors = require("cors");
const mailRoutes = require("./routes/mail");

/* Create Express app */
const app = express();
const PORT = process.env.PORT || 3000;

/* ---------- MIDDLEWARE ---------- */

/* Parse JSON request bodies */
app.use(express.json());

/* Enable CORS for PHP application requests */
app.use(cors({
    origin: "*",          /* Allow requests from any origin (PHP server) */
    methods: ["POST"],    /* Only allow POST requests */
    allowedHeaders: ["Content-Type"]
}));

/* ---------- ROUTES ---------- */

/* Mount mail routes under /api */
app.use("/api", mailRoutes);

/* Health check endpoint */
app.get("/", (req, res) => {
    res.json({
        status: "running",
        service: "Expense Tracker Mail Service",
        timestamp: new Date().toISOString()
    });
});

/* ---------- ERROR HANDLING ---------- */

/* 404 handler */
app.use((req, res) => {
    res.status(404).json({
        success: false,
        message: "Endpoint not found"
    });
});

/* Global error handler */
app.use((err, req, res, next) => {
    console.error("Server Error:", err.message);
    res.status(500).json({
        success: false,
        message: "Internal server error"
    });
});

/* ---------- START SERVER ---------- */

app.listen(PORT, () => {
    console.log(`Mail service running on http://localhost:${PORT}`);
    console.log(`Health check: http://localhost:${PORT}/`);
    console.log(`Send reset email: POST http://localhost:${PORT}/api/send-reset-email`);
});
