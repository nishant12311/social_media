<?php
// send_request.php

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database connection
include 'includes/db_connect.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the current user's ID
    $current_user_id = $_SESSION["user_id"];

    // Get the ID of the user to send a friend request to
    if (isset($_POST['friend_id'])) {
        $friend_id = intval($_POST['friend_id']);

        // Prevent sending a friend request to oneself
        if ($friend_id == $current_user_id) {
            $_SESSION['error'] = "You cannot send a friend request to yourself.";
            header("location: profile.php?user_id=" . $friend_id);
            exit();
        }

        // Check if a friend request already exists
        $sql = "SELECT id, status FROM Friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iiii", $current_user_id, $friend_id, $friend_id, $current_user_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Fetch the existing request
                $stmt->bind_result($id, $status);
                $stmt->fetch();

                if ($status == 'pending') {
                    $_SESSION['error'] = "A friend request is already pending.";
                } elseif ($status == 'accepted') {
                    $_SESSION['error'] = "You are already friends.";
                } elseif ($status == 'rejected') {
                    // Optionally, allow re-sending a friend request if previously rejected
                    // For simplicity, we'll allow it
                    $sql_insert = "INSERT INTO Friends (user_id, friend_id) VALUES (?, ?)";

                    if ($stmt_insert = $conn->prepare($sql_insert)) {
                        $stmt_insert->bind_param("ii", $current_user_id, $friend_id);
                        if ($stmt_insert->execute()) {
                            $_SESSION['success'] = "Friend request sent successfully.";
                        } else {
                            $_SESSION['error'] = "Failed to send friend request.";
                        }
                        $stmt_insert->close();
                    }
                    $stmt->close();
                    $conn->close();
                    header("location: profile.php?user_id=" . $friend_id);
                    exit();
                }
            } else {
                // No existing request, insert a new one
                $sql_insert = "INSERT INTO Friends (user_id, friend_id) VALUES (?, ?)";

                if ($stmt_insert = $conn->prepare($sql_insert)) {
                    $stmt_insert->bind_param("ii", $current_user_id, $friend_id);
                    if ($stmt_insert->execute()) {
                        $_SESSION['success'] = "Friend request sent successfully.";
                    } else {
                        $_SESSION['error'] = "Failed to send friend request.";
                    }
                    $stmt_insert->close();
                }
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = "Invalid request.";
    }

    // Redirect back to the profile page
    header("location: profile.php?user_id=" . $friend_id);
    exit();
} else {
    // If not a POST request, redirect to home
    header("location: index.php");
    exit();
}
?>
