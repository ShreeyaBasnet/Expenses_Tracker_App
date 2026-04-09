"""
app.py
Expense Tracker — Flask API

Routes only. No business logic here.
All logic lives in forecasting.py and notifications.py.

Endpoints:
  GET /predict-expenses      → category spending forecasts
  GET /check-notifications   → triggered alert notifications
  GET /health                → confirms server is running
"""

from flask import Flask, jsonify
from forecasting import run_forecast
from notifications import run_notifications

app = Flask(__name__)


# =============================================================
# ROUTES
# =============================================================

@app.route('/predict-expenses', methods=['GET'])
def predict_expenses():
    """
    Returns next-month spending predictions per category.

    Example response:
    {
        "status": "success",
        "predictions": {
            "Food":      { "predicted_amount": 131.60, "model_used": "linear_regression" },
            "Transport": { "predicted_amount": 21.64,  "model_used": "linear_regression" },
            ...
        }
    }
    """
    try:
        predictions = run_forecast()
        return jsonify({
            'status': 'success',
            'predictions': predictions
        }), 200

    except FileNotFoundError:
        return jsonify({
            'status': 'error',
            'message': 'Expense data file not found.'
        }), 404

    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


@app.route('/check-notifications', methods=['GET'])
def check_notifications():
    """
    Returns list of triggered notification alerts.

    Example response:
    {
        "status": "success",
        "count": 2,
        "notifications": [
            {
                "type": "budget_alert",
                "severity": "warning",
                "message": "You have used 84% of your balance this month."
            },
            {
                "type": "top_category",
                "severity": "info",
                "message": "Food is your biggest expense this week at $87.50."
            }
        ]
    }
    """
    try:
        notifications = run_notifications()
        return jsonify({
            'status': 'success',
            'count': len(notifications),
            'notifications': notifications
        }), 200

    except FileNotFoundError:
        return jsonify({
            'status': 'error',
            'message': 'Expense data file not found.'
        }), 404

    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


@app.route('/health', methods=['GET'])
def health():
    """Quick check to confirm the API is running."""
    return jsonify({'status': 'running'}), 200


# =============================================================
# RUN
# =============================================================

if __name__ == '__main__':
    app.run(debug=True, port=5000)# Flask API routes
