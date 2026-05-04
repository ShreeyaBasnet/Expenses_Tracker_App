<aside class="sidebar">

  <!-- LOGO -->
  <div class="sidebar-logo">
    ब<span>chat</span>
  </div>

  <!-- MAIN SECTION -->
  <div class="sidebar-section">
    <div class="sidebar-section-label">Main</div>

    <a href="dashboard.php"
       class="sidebar-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
      <span class="icon">🏠</span>
      <span class="text">Overview</span>
    </a>

    <a href="transactions.php"
       class="sidebar-item <?= basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : '' ?>">
      <span class="icon">💳</span>
      <span class="text">Transactions</span>
    </a>

    <a href="deposit.php"
       class="sidebar-item <?= basename($_SERVER['PHP_SELF']) == 'deposit.php' ? 'active' : '' ?>">
      <span class="icon">⬇️</span>
      <span class="text">Deposit</span>
    </a>

    <a href="expense.php"
       class="sidebar-item <?= basename($_SERVER['PHP_SELF']) == 'expense.php' ? 'active' : '' ?>">
      <span class="icon">⬆️</span>
      <span class="text">Expense</span>
    </a>
    <a href="forecast.php"
   class="sidebar-item <?= basename($_SERVER['PHP_SELF']) == 'forecast.php' ? 'active' : '' ?>">
  <span class="icon">📈</span>
  <span class="text">Future Forecasting</span>
</a>

  </div>

</aside>