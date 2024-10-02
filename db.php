<?php
$host = 'localhost';
$user = 'root'; // Your DB username
$pass = ''; // Your DB password
$db = 'aptech'; // Your database name

$mysqli = new mysqli($host, $user, $pass, $db);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
