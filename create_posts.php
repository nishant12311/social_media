<?php
// create_post.php

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
$post_content = "";
$upload_status = "";

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the current user's ID
    $user_id = $_SESSION["user_id"];

    // Validate and sanitize input
    if (isset($_POST['post_content'])) {
        $post_content = trim($_POST['post_content']);
        $post_content = htmlspecialchars($post_content);

        // Check if post content is not empty
        if (empty($post_content)) {
            $_SESSION['error'] = "Post content cannot be empty.";
        } else {
            // Insert post into the database
            $sql = "INSERT INTO Posts (user_id, post_content, created_at) VALUES (?, ?, NOW())";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("is", $user_id, $post_content);

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Post created successfully.";
                } else {
                    $_SESSION['error'] = "Failed to create post.";
                }
                $stmt->close();
            }
        }
    }

    // Redirect to profile page or another page after processing
    header("location: profile.php");
    exit();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS file -->
</head>
<body>
    <div class="container">
        <h1>Create a New Post</h1>
        <form action="create_post.php" method="post">
            <textarea name="post_content" rows="5" cols="50" placeholder="What's on your mind?" required></textarea>
            <br>
            <input type="submit" value="Post">
        </form>
        <?php
        // Display error or success message if available
        if (isset($_SESSION['error'])) {
            echo '<p class="error">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p class="success">' . $_SESSION['success'] . '</p>';
            unset($_SESSION['success']);
        }
        ?>
    </div>
</body>
</html>
