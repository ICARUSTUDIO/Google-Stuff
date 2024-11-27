<?php
session_start();

// Database credentials
$servername = "localhost";
$db_username = "root"; // Your MySQL username (default for XAMPP is "root")
$db_password = ""; // Your MySQL password (default for XAMPP is empty)
$dbname = "google_login_db"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login attempt
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the username and password exist in the database
    $stmt = $conn->prepare("SELECT password FROM admin_credentials WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Set session variables to indicate logged in
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;

            // Redirect to user dashboard
            header("Location: user-dash.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign In</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="fontawesome-pro-6.5.0-web/css/all.css">
    <link rel="stylesheet" href="assets/CSS/login.css">
</head>
<body>
    <section>
        <form method="POST" action="">
            <h2>Sign in</h2>
            <?php 
            if (isset($error)) {
                echo "<p style='color: red; text-align: center;'>$error</p>";
            }
            ?>
            <div class="inputFields username">
                <input type="text" name="username" id="username" required value="admin">
                <label for="username" id="UsernameIco&Txt">
                    <i class="fa fa-user"></i> Username
                </label>
            </div> 
            
            <div class="inputFields password">    
                <input type="password" name="password" id="password" required value="admin">
                <label for="password" id="PasswordIco&Txt">
                    <i class="fa fa-lock"></i> Password
                </label>
            </div>
            <div class="Rememberme">
                <label><input type="checkbox" name="Rememberme" id="Rememberme"> Remember me</label>
            </div>
            <div class="Login">
                <button type="submit" class="button">Login</button>
            </div>
        </form>
    </section>
</body>
</html>
