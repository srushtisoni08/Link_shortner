<?php
include 'db.php'; // adjust if your db.php is in another folder

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get values from form
    $name = $_POST["username"] ?? null;
    $email = $_POST["email"] ?? null;
    $password = $_POST["password"] ?? null;
    $confirm_password = $_POST["confirm-password"] ?? null;

    // Validation
    if (!$name || !$email || !$password || !$confirm_password) {
        die("❌ Please fill all fields!");
    }

    if ($password !== $confirm_password) {
        die("❌ Passwords do not match!");
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $sql = "INSERT INTO users (name, email, Password, Time) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "✅ Signup successful! <a href='login.html'>Login here</a>";
    } else {
        echo "❌ Error: " . $stmt->error;
    }
}
?>
