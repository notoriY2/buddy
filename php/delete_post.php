<?php
session_start();
require 'config.php'; // Ensure this file contains your database connection setup

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['studentNo'])) {
    header('Location: ../login.php');
    exit;
}

// Retrieve the student number from the session
$studentNo = $_SESSION['studentNo'];

// Retrieve the post ID from the URL parameter
$postId = $_GET['post_id'] ?? null;

if ($postId) {
    // Retrieve the student ID based on the student number
    $stmt = $conn->prepare("SELECT student_id FROM student WHERE studentNo = ?");
    $stmt->bind_param("s", $studentNo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $studentId = $student['student_id'];

        // Check if the student is an admin
        $stmt = $conn->prepare("
            SELECT gm.role, p.student_id AS post_owner_id
            FROM group_membership gm
            JOIN post p ON gm.group_id = p.group_id
            WHERE gm.student_id = ? AND p.post_id = ?
        ");
        $stmt->bind_param("ii", $studentId, $postId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            
            // Check if the student is an admin or is the owner of the post
            if ($data['role'] === 'admin' || (int)$data['post_owner_id'] === (int)$studentId) {
                // Prepare a statement to delete the post
                $stmt = $conn->prepare("DELETE FROM post WHERE post_id = ?");
                $stmt->bind_param("i", $postId);
                if ($stmt->execute()) {
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit;
                } else {
                    echo "Error: " . $stmt->error;
                }
            } else {
                // Redirect to the previous page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
            }
        } else {
            echo "Post not found or you are not authorized to delete this post";
        }
    } else {
        echo "Invalid student number";
    }
} else {
    echo "Invalid post ID";
}
?>
