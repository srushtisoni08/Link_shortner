<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.html");
    exit();
}

$shortened_link = ""; // Initialize variable
$user_links = []; // Initialize an array to hold the user's links

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["original_url"])) {
    $o_url = trim($_POST['original_url']);

    if (filter_var($o_url, FILTER_VALIDATE_URL)) {
        $short_code = substr(md5(uniqid(rand(), true)), 0, 6);

        $stmt = $conn->prepare("INSERT INTO urls (user_id, short_code, long_url, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $_SESSION['user_id'], $short_code, $o_url);

        if ($stmt->execute()) {
            $shortened_link = "http://localhost/link_shortner/Includes/r.php?c=$short_code";
        } else {
            $shortened_link = "Error creating short link.";
        }
        $stmt->close();
    } else {
        $shortened_link = "Invalid URL!";
    }
}

// Fetch user's links after potentially inserting a new one
$stmt_fetch = $conn->prepare("SELECT long_url, short_code, created_at FROM urls WHERE user_id = ? ORDER BY created_at DESC");
$stmt_fetch->bind_param("i", $_SESSION['user_id']);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();

while ($row = $result->fetch_assoc()) {
    $user_links[] = $row;
}
$stmt_fetch->close();
$conn->close();

?>
<html>
<body>
    <h2>Shorten a URL</h2>
    <form method="POST" action="dashboard.php">
        <input type="url" name="original_url" placeholder="Enter URL to shorten" required>
        <button type="submit">Shorten</button>
    </form>

    <?php
    if ($shortened_link) {
        echo "<p>Shortened Link: <a href='$shortened_link' target='_blank'>$shortened_link</a></p>";
    }

    echo "<h3>Your Links</h3>";
    if (empty($user_links)) {
        echo "<p>You haven't shortened any links yet.</p>";
    } else {
        foreach ($user_links as $link) {
            $short_url = "http://localhost/link_shortner/Includes/r.php?c=" . $link['short_code'];
            echo "<p>Original: " . htmlspecialchars($link['long_url']) . "<br>";
            echo "Short: <a href='$short_url' target='_blank'>$short_url</a><br>";
            echo "Created: " . $link['created_at'] . "</p>";
        }
    }
    ?>
</body>
</html>