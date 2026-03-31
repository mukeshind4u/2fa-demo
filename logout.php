<?php
// logout.php
session_start();

// 1. Destroy all session data
$_SESSION = array();
session_destroy();

// 2. Delete the "Remember this device" cookie if it exists
if (isset($_COOKIE['trusted_device'])) {
    // To delete a cookie, you set its expiration date to the past
    setcookie('trusted_device', '', time() - 3600, '/');
}

// 3. Send the user back to the login screen
header("Location: login.php");
exit;
?>