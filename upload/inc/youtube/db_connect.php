<?php
$host = 'localhost';
$dbUsername = 'your_db_username';
$dbPassword = 'your_db_password';
$dbName = 'your_db_name';

// Create a connection to the database
$connection = new mysqli($host, $dbUsername, $dbPassword, $dbName);

// Check if the connection was successful
if ($connection->connect_error) {
   die("Connection failed: " . $connection->connect_error);
}
?>
