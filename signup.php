<?php
// Enable error reporting para makita ang problema
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "crasm");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check kung may 'name' column ang 'administrator' table
$checkColumn = $conn->query("SHOW COLUMNS FROM administrator LIKE 'name'");
if ($checkColumn->num_rows === 0) {
    die("❌ Walang 'name' column sa 'administrator' table. Kailangan mo munang i‑add ito.<br>
         <strong>SQL command:</strong><br>
         <code>ALTER TABLE administrator ADD COLUMN name VARCHAR(100) NOT NULL AFTER id;</code>");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name     = trim($_POST["name"] ?? '');
    $username = trim($_POST["username"] ?? '');
    $password = $_POST["password"] ?? '';

    if ($name === "" || $username === "" || $password === "") {
        die("All fields are required.");
    }

    // Check duplicate username
    $checkStmt = $conn->prepare("SELECT id FROM administrator WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        die("Username already exists.");
    }
    $checkStmt->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO administrator (name, username, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $username, $hashedPassword);

    if ($stmt->execute()) {
        echo "✅ Administrator account created successfully.";
    } else {
        echo "❌ Failed: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head><title>Create Admin</title></head>
<body>
<form method="POST">
    <label>Full Name</label><br>
    <input type="text" name="name" required><br><br>
    <label>Username</label><br>
    <input type="text" name="username" required><br><br>
    <label>Password</label><br>
    <input type="password" name="password" required><br><br>
    <button type="submit">Create Account</button>
</form>
</body>
</html>