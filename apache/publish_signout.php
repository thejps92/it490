<?php
// Start the session (if not already started)
session_start();

// Check if the user is signed in (session exists)
if (isset($_SESSION['username'])) {
    // Sign the user out by destroying the session and unsetting all session variables
    session_destroy();
    session_unset();
    
    // Redirect the user to the sign in page
    header('Location: signin.php');
    exit();
} else {
    // If the user is not signed in, redirect them to the home page
    header('Location: index.php');
    exit();
}
?>