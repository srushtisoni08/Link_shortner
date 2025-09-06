<?php
include 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["username"] ?? null;
    $email = $_POST["email"] ?? null;
    $password = $_POST["password"] ?? null;
    $confirm_password = $_POST["confirm-password"] ?? null;

    if (!$name || !$email || !$password || !$confirm_password) {
        die("Please fill all fields!");
    }

    if ($password !== $confirm_password) {
        die("Passwords do not match!");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, Password, Time) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $hashed_password);

    if ($stmt->execute()) {
        header("Location: ../public/login.html");
        exit();
    } else {
        echo "❌ Error: " . $stmt->error;
    }
}
?>