<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.html");
    exit();
}

$shortened_link = ""; // Initialize variable
$error_message = "";   // Initialize error message
$user_links = [];      // Initialize an array to hold the user's links

// Handle URL shortening
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["original_url"])) {
    $o_url = trim($_POST['original_url']);

    // Validate URL
    if (filter_var($o_url, FILTER_VALIDATE_URL)) {
        // Generate unique short code
        do {
            $short_code = substr(md5(uniqid(rand(), true)), 0, 6);
            
            // Check if short code already exists
            $check_stmt = $conn->prepare("SELECT short_code FROM urls WHERE short_code = ?");
            $check_stmt->bind_param("s", $short_code);
            $check_stmt->execute();
            $exists = $check_stmt->get_result()->num_rows > 0;
            $check_stmt->close();
        } while ($exists);

        // Insert new URL
        $stmt = $conn->prepare("INSERT INTO urls (user_id, short_code, long_url, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $_SESSION['user_id'], $short_code, $o_url);

        if ($stmt->execute()) {
            $shortened_link = "http://localhost/Includes/r.php?c=" . urlencode($short_code);
        } else {
            $error_message = "Error creating short link: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error_message = "Please enter a valid URL (including http:// or https://)";
    }
}

// Fetch user's links
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>URL Shortener Dashboard</title>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>URL Shortener Dashboard</h2>
            <a href="../public/logout.php" class="logout" style="color: white; text-decoration: none; padding: 8px 16px; border-radius: 4px;">Logout</a>
        </div>

        <form method="POST" action="dashboard.php">
            <div class="form-group">
                <input type="url" name="original_url" placeholder="Enter URL to shorten (e.g., https://example.com)" required>
                <button type="submit">Shorten URL</button>
            </div>
        </form>

        <?php if (!empty($shortened_link)): ?>
            <div class="success">
                <strong>Success!</strong> Your shortened link: 
                <a href="<?php echo htmlspecialchars($shortened_link); ?>" target="_blank" class="link-short">
                    <?php echo htmlspecialchars($shortened_link); ?>
                </a>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <h3>Your Shortened Links</h3>
        <?php if (empty($user_links)): ?>
            <p>You haven't shortened any links yet. Create your first one above!</p>
        <?php else: ?>
            <?php foreach ($user_links as $link): ?>
                <div class="link-item" style="margin-bottom: 10px;">
                    <div class="link-original">
                        Original: <?php echo htmlspecialchars($link['long_url']); ?>
                    </div>
                    <div>
                        Short: 
                        <a href="http://localhost/Includes/r.php?c=<?php echo urlencode($link['short_code']); ?>" target="_blank" class="link-short">
                            http://localhost/Includes/r.php?c=<?php echo htmlspecialchars($link['short_code']); ?>
                        </a>
                    </div>
                    <div class="link-date">
                        Created: <?php echo date('M j, Y g:i A', strtotime($link['created_at'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
