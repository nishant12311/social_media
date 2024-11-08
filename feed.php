<?php
// feed.php

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database connection
include 'includes/db_connect.php';

// Initialize variables
$posts = [];

// Fetch posts from the database
$sql = "SELECT p.post_id, p.post_content, p.created_at, u.username 
        FROM Posts p 
        JOIN Users u ON p.user_id = u.user_id 
        ORDER BY p.created_at DESC";

if ($result = $conn->query($sql)) {
    // Fetch all posts
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    $result->free();
} else {
    $_SESSION['error'] = "Failed to fetch posts.";
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS file -->
</head>
<body>
    <div class="container">
        <h1>Feed</h1>
        <?php
        // Display error message if available
        if (isset($_SESSION['error'])) {
            echo '<p class="error">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }

        // Display posts
        if (!empty($posts)) {
            foreach ($posts as $post) {
                echo '<div class="post">';
                echo '<p><strong>' . htmlspecialchars($post['username']) . '</strong></p>';
                echo '<p>' . htmlspecialchars($post['post_content']) . '</p>';
                echo '<p><small>' . htmlspecialchars($post['created_at']) . '</small></p>';
                echo '</div>';
            }
        } else {
            echo '<p>No posts available.</p>';
        }
        ?>
    </div>
</body>
</html>
