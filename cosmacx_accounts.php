<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cosmacx_db";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create Database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error;
}

// Use the database
$conn->select_db($dbname);

// Create users table if not exists
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table users created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error;
}

// Function to hash the password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Function to verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Function to send the reset password email (mock function for now)
function sendPasswordResetEmail($email, $newPassword) {
    echo "Password reset email sent to $email with new password: $newPassword<br>";
}

// Handle account creation
if (isset($_POST['create_account'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = hashPassword($_POST['password']);

    // Insert the new user into the database
    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";

    if ($conn->query($sql) === TRUE) {
        echo "New account created successfully<br>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the user exists
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (verifyPassword($password, $user['password'])) {
            echo "Login successful. Welcome " . $user['username'] . "!<br>";
        } else {
            echo "Incorrect password<br>";
        }
    } else {
        echo "No account found with that email<br>";
    }
}

// Handle password reset
if (isset($_POST['forgot_password'])) {
    $email = $_POST['email'];

    // Generate a new password
    $newPassword = bin2hex(random_bytes(4));
    $hashedNewPassword = hashPassword($newPassword);

    // Update the user's password in the database
    $sql = "UPDATE users SET password='$hashedNewPassword' WHERE email='$email'";

    if ($conn->query($sql) === TRUE) {
        // Send the new password via email (mock function)
        sendPasswordResetEmail($email, $newPassword);
    } else {
        echo "Error updating password: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COSMACX Account Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { margin-bottom: 20px; }
        input { display: block; margin-bottom: 10px; padding: 8px; width: 300px; }
        button { padding: 10px 20px; background-color: #333; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #555; }
    </style>
</head>
<body>

<h1>COSMACX Account Management</h1>

<!-- Account creation form -->
<h2>Create Account</h2>
<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="create_account">Create Account</button>
</form>

<!-- Login form -->
<h2>Login</h2>
<form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="login">Login</button>
</form>

<!-- Forgot password form -->
<h2>Forgot Password</h2>
<form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <button type="submit" name="forgot_password">Reset Password</button>
</form>

</body>
</html>
