<?php
include 'db.php'; // db.php is in the same Includes folder

// Check if short code parameter exists
if (isset($_GET['c']) && !empty($_GET['c'])) {
    $short_code = trim($_GET['c']);
    
    // Validate short code format (6 characters, alphanumeric)
    if (preg_match('/^[a-zA-Z0-9]{6}$/', $short_code)) {
        // Prepare and execute query to find the original URL
        $stmt = $conn->prepare("SELECT long_url FROM urls WHERE short_code = ?");
        $stmt->bind_param("s", $short_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $long_url = $row['long_url'];
            
            // Optional: Update click count (you can add a clicks column to track usage)
            // $update_stmt = $conn->prepare("UPDATE urls SET clicks = clicks + 1 WHERE short_code = ?");
            // $update_stmt->bind_param("s", $short_code);
            // $update_stmt->execute();
            // $update_stmt->close();
            
            $stmt->close();
            $conn->close();
            
            // Redirect to the original URL
            header("Location: " . $long_url, true, 301); // 301 for permanent redirect
            exit();
        } else {
            $stmt->close();
            $conn->close();
            
            // Display user-friendly error page
            displayErrorPage("Link Not Found", "This shortened link does not exist or may have expired.");
        }
    } else {
        $conn->close();
        displayErrorPage("Invalid Link", "The provided link format is invalid.");
    }
} else {
    $conn->close();
    displayErrorPage("Missing Link", "No short code was provided in the URL.");
}

// Function to display a user-friendly error page
function displayErrorPage($title, $message) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> - URL Shortener</title>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">⚠️</div>
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <p><?php echo htmlspecialchars($message); ?></p>
            <a href="../public/index.html" class="btn">Go to Homepage</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>