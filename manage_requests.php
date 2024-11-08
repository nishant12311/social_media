<?php
// manage_requests.php

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database connection
include 'includes/db_connect.php';

// Fetch incoming friend requests
$current_user_id = $_SESSION["user_id"];

$sql = "SELECT Friends.id, Users.user_id, Users.username, Users.profile_picture 
        FROM Friends 
        JOIN Users ON Friends.user_id = Users.user_id 
        WHERE Friends.friend_id = ? AND Friends.status = 'pending'";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $friend_requests = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
}

// Handle Accept/Decline Actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['request_id']) && isset($_POST['action'])) {
        $request_id = intval($_POST['request_id']);
        $action = $_POST['action'];

        if ($action == 'accept') {
            // Update the friend request status to 'accepted'
            $sql_update = "UPDATE Friends SET status = 'accepted' WHERE id = ?";

            if ($stmt_update = $conn->prepare($sql_update)) {
                $stmt_update->bind_param("i", $request_id);
                $stmt_update->execute();
                $stmt_update->close();
                $_SESSION['success'] = "Friend request accepted.";
            }
        } elseif ($action == 'decline') {
            // Update the friend request status to 'rejected'
            $sql_update = "UPDATE Friends SET status = 'rejected' WHERE id = ?";

            if ($stmt_update = $conn->prepare($sql_update)) {
                $stmt_update->bind_param("i", $request_id);
                $stmt_update->execute();
                $stmt_update->close();
                $_SESSION['success'] = "Friend request declined.";
            }
        }

        // Redirect to avoid form resubmission
        header("location: manage_requests.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Friend Requests - Social Media</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .profile-picture {
            width: 50px;
            height: 50px;
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
                        <a class="nav-link active" aria-current="page" href="manage_requests.php">Friend Requests</a>
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
        <h2>Incoming Friend Requests</h2>

        <!-- Display Success Messages -->
        <?php 
        if(isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        ?>

        <?php if (count($friend_requests) > 0): ?>
            <ul class="list-group">
                <?php foreach ($friend_requests as $request): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <?php if (!empty($request['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($request['profile_picture']); ?>" alt="Profile Picture" class="profile-picture me-3">
                            <?php else: ?>
                                <img src="assets/images/default_profile.png" alt="Profile Picture" class="profile-picture me-3">
                            <?php endif; ?>
                            <span><?php echo htmlspecialchars($request['username']); ?></span>
                        </div>
                        <div>
                            <form action="manage_requests.php" method="post" class="d-inline">
                                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                                <input type="hidden" name="action" value="accept">
                                <button type="submit" class="btn btn-success btn-sm">Accept</button>
                            </form>
                            <form action="manage_requests.php" method="post" class="d-inline">
                                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                                <input type="hidden" name="action" value="decline">
                                <button type="submit" class="btn btn-danger btn-sm">Decline</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No incoming friend requests.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
