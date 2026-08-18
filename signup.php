<?php
$conn = new mysqli("localhost", "root", "", "crasm");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if ($username === "" || $password === "") {
        die("Username and password are required.");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO administrator (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashedPassword);

    if ($stmt->execute()) {
        echo "Administrator account created successfully.";
    } else {
        echo "Failed to create account. Username may already exist.";
    }

    $stmt->close();
}

$conn->close();
?>

<form method="POST">
    <label>Username</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Create Account</button>
</form>