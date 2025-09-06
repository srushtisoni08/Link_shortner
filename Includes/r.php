<?php
include 'db.php'; // db.php is in the same Includes folder

if (isset($_GET['c']) && !empty($_GET['c'])) {
    $short_code = trim($_GET['c']);

    $stmt = $conn->prepare("SELECT long_url FROM urls WHERE short_code = ?");
    $stmt->bind_param("s", $short_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        header("Location: " . $row['long_url']);
        exit();
    } else {
        echo "Invalid or expired link!";
    }

    $stmt->close();
} else {
    echo "No short code provided!";
}

$conn->close();
?>
