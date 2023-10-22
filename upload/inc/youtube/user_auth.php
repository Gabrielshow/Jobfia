<?php
// Include the necessary authentication checks at the beginning of each protected page
session_start();

if (!isset($_SESSION['user_id'])) {
   // Redirect the user to the login page or display an error message
   header("Location: login.php");
   exit();
}
?>
