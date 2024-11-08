<?php
// friends.php

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database connection
include 'includes/db_connect.php';

// Get the current user's ID
$current_user_id = $_SESSION["user_id"];

// Fetch friends where the user is either user_id or friend_id with status 'accepted'
$sql = "SELECT Users.user_id, Users.username, Users.profile_picture 
        FROM Friends 
        JOIN Users ON (Friends.friend_id = Users.user_id) 
        WHERE Friends.user_id = ? AND Friends.status = 'accepted'
        UNION
        SELECT Users.user_id, Users.username, Users.profile_picture 
        FROM Friends 
        JOIN Users ON (Friends.user_id = Users.user_id) 
        WHERE Friends.friend_id = ? AND Friends.status = 'accepted'";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $current_user_id, $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $friends = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Friends - Social Media</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .friend-card {
            margin-bottom: 20px;
        }
        .profile-picture {
            width: 100px;
            height: 100px;
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
                        <a class="nav-link" href="profile.php?user_id=<?php echo $_SESSION['user_id']; ?>">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_requests.php">Friend Requests</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="friends.php">Friends</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION["username"]); ?>)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h2>Your Friends</h2>

        <?php if (count($friends) > 0): ?>
            <div class="row">
                <?php foreach ($friends as $friend): ?>
                    <div class="col-md-4">
                        <div class="card friend-card">
                            <div class="card-body text-center">
                                <?php if (!empty($friend['profile_picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($friend['profile_picture']); ?>" alt="Profile Picture" class="profile-picture mb-3">
                                <?php else: ?>
                                    <img src="assets/images/default_profile.png" alt="Profile Picture" class="profile-picture mb-3">
                                <?php endif; ?>
                                <h5 class="card-title"><?php echo htmlspecialchars($friend['username']); ?></h5>
                                <a href="profile.php?user_id=<?php echo htmlspecialchars($friend['user_id']); ?>" class="btn btn-primary btn-sm">View Profile</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>You have no friends yet.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
