<?php

session_start();

/* CLEAR SESSION VARIABLES */
$_SESSION = [];

/* DELETE SESSION COOKIE */
if (ini_get("session.use_cookies")) {

$params = session_get_cookie_params();

setcookie(
session_name(),
'',
time() - 42000,
$params["path"],
$params["domain"],
$params["secure"],
$params["httponly"]
);

}

/* DESTROY SESSION */
session_destroy();

/* REGENERATE */
session_regenerate_id(true);

/* REDIRECT */
header("Location: login.php");
exit();

?><?php

session_start();

/* CLEAR SESSION VARIABLES */
$_SESSION = [];

/* DELETE SESSION COOKIE */
if (ini_get("session.use_cookies")) {

$params = session_get_cookie_params();

setcookie(
session_name(),
'',
time() - 42000,
$params["path"],
$params["domain"],
$params["secure"],
$params["httponly"]
);

}

/* DESTROY SESSION */
session_destroy();

/* REGENERATE */
session_regenerate_id(true);

/* REDIRECT */
header("Location: login.php");
exit();

?>