<aside class="sidebar">

<!-- LOGO -->
<div class="sidebar-logo">
ब<span>chat</span>
</div>

<!-- MAIN SECTION -->
<div class="sidebar-section">

<div class="sidebar-section-label">
Main
</div>

<!-- OVERVIEW -->
<a href="dashboard.php"
class="sidebar-item <?= basename($_SERVER['PHP_SELF'])=='dashboard.php' ? 'active':'' ?>">

<i class="fa-solid fa-house sidebar-icon"></i>

<span class="text">
Overview
</span>

</a>

<!-- TRANSACTIONS -->
<a href="transactions.php"
class="sidebar-item <?= basename($_SERVER['PHP_SELF'])=='transactions.php' ? 'active':'' ?>">

<i class="fa-solid fa-credit-card sidebar-icon"></i>

<span class="text">
Transactions
</span>

</a>

<!-- DEPOSIT -->
<a href="deposit.php"
class="sidebar-item <?= basename($_SERVER['PHP_SELF'])=='deposit.php' ? 'active':'' ?>">

<i class="fa-solid fa-wallet sidebar-icon"></i>

<span class="text">
Deposit
</span>

</a>

<!-- EXPENSE -->
<a href="expense.php"
class="sidebar-item <?= basename($_SERVER['PHP_SELF'])=='expense.php' ? 'active':'' ?>">

<i class="fa-solid fa-money-bill-transfer sidebar-icon"></i>

<span class="text">
Expense
</span>

</a>

<!-- FORECAST -->
<a href="forecast.php"
class="sidebar-item <?= basename($_SERVER['PHP_SELF'])=='forecast.php' ? 'active':'' ?>">

<i class="fa-solid fa-chart-line sidebar-icon"></i>

<span class="text">
Future Forecasting
</span>

</a>

</div>

<?php

$username =
$_SESSION['username']
?? "User";

?>

<!-- FOOTER -->
<div class="sidebar-footer">

<a href="#"
class="sidebar-item"
onclick="confirmLogout()">

<i class="fa-solid fa-right-from-bracket sidebar-icon"></i>

<span class="text">
Logout
</span>

</a>

</div>

<!-- PROFILE -->
<div class="sidebar-profile">

<div class="profile-avatar">

<i class="fa-solid fa-user"></i>

</div>

<div class="profile-info">

<div class="profile-label">
Logged in as
</div>

<div class="profile-name">
<?= htmlspecialchars($username) ?>
</div>

</div>

</div>

</aside>

<script>

function confirmLogout(){

if(
confirm(
"Are you sure you want to logout?"
)
){
window.location.href=
"../Config/logout.php";
}

}

/* notifications */

const userId=
<?= $_SESSION['user_id'] ?>;

fetch(
'https://expensetrackerai-xjro.onrender.com/check-notifications?user_id='
+userId
)

.then(r=>r.json())

.then(data=>{

if(data.count>0){

alert(
data.notifications[0].message
);

}

});

</script>