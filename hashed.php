<?php
// hash_admin_password.php
// Run this script ONCE to hash your existing admin password

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "connect_db";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Hash the plaintext password
$plain_password = "Starboy190";
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
$username = "Victor01";

// Update the database with the hashed password
$stmt = $conn->prepare("UPDATE admin_credentials SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashed_password, $username);

if ($stmt->execute()) {
    echo "Admin password has been successfully hashed and updated in the database.";
} else {
    echo "Error updating password: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>