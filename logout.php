<?php
// logout.php - ends the session and returns to the login page
session_start();

$_SESSION = [];   // remove all session data (id, username, role, cart)

// delete the session cookie in the browser
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

session_destroy();

header("Location: index.php");
exit;