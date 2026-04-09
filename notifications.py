"""
notifications.py
Expense Tracker — Rule-Based Notification Engine

Handles 3 notification rules:
  Rule 1 — Budget Threshold Alert   (rule-based: 80% balance used)
  Rule 2 — Top Spending Category    (rule-based: highest category this week)
  Rule 3 — Anomaly Detection        (AI/stats: std deviation spike detection)

Called by: app.py via run_notifications()

To switch from CSV to SQL: edit load_data() and load_balance() only.
Everything else stays the same.
"""

import pandas as pd
import numpy as np
from datetime import timedelta


# =============================================================
# DATA LOADING
# =============================================================

def load_data():
    """
    Load expense transactions from CSV.

    --- TO SWITCH TO SQL LATER ---
    import mysql.connector
    conn = mysql.connector.connect(
        host='localhost', user='root',
        password='your_password', database='expense_tracker'
    )
    df = pd.read_sql("SELECT date, category, amount FROM expenses", conn)
    conn.close()
    return df
    """
    df = pd.read_csv('expenses.csv')
    df['date'] = pd.to_datetime(df['date'])
    return df


def load_balance():
    """
    Load user's wallet balance.

    --- TO SWITCH TO SQL LATER ---
    cursor.execute("SELECT balance FROM wallet WHERE user_id = 1")
    return float(cursor.fetchone()[0])
    """
    return 1000.00


# =============================================================
# HELPER
# =============================================================

def get_this_week(df):
    """Returns only this week's expense records."""
    today = df['date'].max()
    week_start = today - timedelta(days=today.weekday())
    return df[df['date'] >= week_start]


# =============================================================
# RULE 1 — BUDGET THRESHOLD ALERT
# Triggers when total spending this month >= 80% of balance
# =============================================================

def check_budget_threshold(df, balance):
    """
    Compares total spending this month against wallet balance.
    Triggers at 80% usage.
    """
    today = df['date'].max()
    this_month = df[
        (df['date'].dt.month == today.month) &
        (df['date'].dt.year == today.year)
    ]
    total_spent = this_month['amount'].sum()
    usage_pct = (total_spent / balance) * 100

    if usage_pct >= 80:
        return {
            'type': 'budget_alert',
            'severity': 'warning',
            'message': f"You have used {usage_pct:.0f}% of your balance this month — consider reducing spending."
        }
    return None


# =============================================================
# RULE 2 — TOP SPENDING CATEGORY THIS WEEK
# Always returns — informational insight
# =============================================================

def check_top_category(df):
    """
    Finds the category with the highest total spending this week.
    """
    this_week = get_this_week(df)

    if this_week.empty:
        return None

    category_totals = this_week.groupby('category')['amount'].sum()
    top_category = category_totals.idxmax()
    top_amount = category_totals.max()

    return {
        'type': 'top_category',
        'severity': 'info',
        'message': f"{top_category} is your biggest expense this week at ${top_amount:.2f}."
    }


# =============================================================
# RULE 3 — ANOMALY DETECTION
# Triggers when this week's category spending exceeds
# mean + 2 standard deviations of historical weekly spending
# =============================================================

def check_spending_anomaly(df):
    """
    Uses standard deviation based anomaly detection.
    Compares this week's spending per category against
    historical weekly mean and std deviation.
    Triggers when current week > mean + (2 x std).
    """
    alerts = []
    this_week = get_this_week(df)

    if this_week.empty:
        return alerts

    # Build historical weekly totals per category
    df['week_index'] = (
        (df['date'].dt.year - df['date'].dt.year.min()) * 52 +
        df['date'].dt.isocalendar().week.astype(int)
    )

    weekly_totals = (
        df.groupby(['week_index', 'category'])['amount']
        .sum()
        .reset_index()
    )

    # Calculate mean and std per category
    historical_stats = weekly_totals.groupby('category')['amount'].agg(['mean', 'std'])

    # This week's totals per category
    this_week_totals = this_week.groupby('category')['amount'].sum()

    for category, this_week_amount in this_week_totals.items():
        if category not in historical_stats.index:
            continue

        mean = historical_stats.loc[category, 'mean']
        std = historical_stats.loc[category, 'std']

        # Handle case where std is NaN (only 1 week of data)
        if pd.isna(std) or std == 0:
            continue

        threshold = mean + (2 * std)

        if this_week_amount > threshold:
            alerts.append({
                'type': 'anomaly_detected',
                'severity': 'warning',
                'message': f"Unusual spike detected in {category} spending this week — significantly higher than your historical average."
            })

    return alerts


# =============================================================
# MAIN NOTIFICATION RUNNER
# =============================================================

def run_notifications():
    """Runs all 3 rules and returns list of triggered alerts."""
    df = load_data()
    balance = load_balance()

    notifications = []

    # Rule 1 — Budget threshold
    budget_alert = check_budget_threshold(df, balance)
    if budget_alert:
        notifications.append(budget_alert)

    # Rule 2 — Top category (always runs)
    top_category = check_top_category(df)
    if top_category:
        notifications.append(top_category)

    # Rule 3 — Anomaly detection (can return multiple)
    anomaly_alerts = check_spending_anomaly(df)
    notifications.extend(anomaly_alerts)

    return notifications# Notification rules
