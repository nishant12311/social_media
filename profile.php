<?php
// profile.php

// Start the session
session_start();

// Include database connection
include 'includes/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Get the user ID from the URL parameter
if (isset($_GET['user_id'])) {
    $profile_user_id = intval($_GET['user_id']);
} else {
    // If no user_id is provided, redirect to the current user's profile
    $profile_user_id = $_SESSION["user_id"];
}

// Fetch profile user information
$sql = "SELECT username, email, profile_picture, created_at FROM Users WHERE user_id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $profile_user_id);
    $stmt->execute();
    $stmt->bind_result($username, $email, $profile_picture, $created_at);
    $stmt->fetch();
    $stmt->close();
}

// Determine the relationship status between current user and profile user
$relationship_status = "";
$can_send_request = false;

if ($profile_user_id != $_SESSION["user_id"]) {
    $sql_relation = "SELECT status FROM Friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";

    if ($stmt_rel = $conn->prepare($sql_relation)) {
        $stmt_rel->bind_param("iiii", $_SESSION["user_id"], $profile_user_id, $profile_user_id, $_SESSION["user_id"]);
        $stmt_rel->execute();
        $stmt_rel->bind_result($status);
        if ($stmt_rel->fetch()) {
            if ($status == 'pending') {
                $relationship_status = "Friend request pending.";
            } elseif ($status == 'accepted') {
                $relationship_status = "You are friends.";
            } elseif ($status == 'rejected') {
                $relationship_status = "Friend request rejected.";
                $can_send_request = true; // Allow re-sending request
            }
        } else {
            // No existing relationship
            $relationship_status = "You are not friends.";
            $can_send_request = true;
        }
        $stmt_rel->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($username); ?>'s Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-header {
            margin-top: 50px;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Social Media</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION["username"]); ?>)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Header -->
    <div class="container profile-header">
        <div class="row">
            <div class="col-md-4 text-center">
                <?php if (!empty($profile_picture)): ?>
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-picture">
                <?php else: ?>
                    <img src="assets/images/default_profile.png" alt="Profile Picture" class="profile-picture">
                <?php endif; ?>
            </div>
            <div class="col-md-8">
                <h2><?php echo htmlspecialchars($username); ?></h2>
                <p>Email: <?php echo htmlspecialchars($email); ?></p>
                <p>Joined on: <?php echo htmlspecialchars(date("F d, Y", strtotime($created_at))); ?></p>
                <p>Status: <?php echo htmlspecialchars($relationship_status); ?></p>

                <?php if ($can_send_request): ?>
                    <form action="send_request.php" method="post">
                        <input type="hidden" name="friend_id" value="<?php echo htmlspecialchars($profile_user_id); ?>">
                        <button type="submit" class="btn btn-primary">Add Friend</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Display Success or Error Messages -->
    <div class="container mt-3">
        <?php 
        if(isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        if(isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        ?>
    </div>

    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
