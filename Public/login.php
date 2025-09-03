<?php
include 'db.php'; // adjust path if needed
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? null;
    $password = $_POST["password"] ?? null;

    if (!$email || !$password) {
        die("❌ Please fill all fields!");
    }

    // Query user by email
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc(); // ✅ use $user, not $email

        if (password_verify($password, $user['Password'])) { // Changed $user['password'] to $user['Password']
            // Store session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name'];

            echo "✅ Login successful! Welcome, " . htmlspecialchars($user['name']);
            // Or redirect:
            // header("Location: dashboard.php");
            // exit();
        } else {
            echo "❌ Invalid password!";
        }
    } else {
        echo "❌ No user found with that email!";
    }
}
?>