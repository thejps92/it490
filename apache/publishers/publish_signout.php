<?php
// Start the session
session_start();

// Check if the user is signed in
if (isset($_SESSION['username'])) {
    session_destroy();
    session_unset();
    header('Location: signin.php');
    exit();
} else {
    header('Location: index.php');
    exit();
}
?>