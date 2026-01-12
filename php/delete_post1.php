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

        // Delete all likes associated with the post
        $stmt = $conn->prepare("DELETE FROM `like` WHERE post_id = ?");
        $stmt->bind_param("i", $postId);
        $stmt->execute();

        // Delete the post if it belongs to the student
        $stmt = $conn->prepare("DELETE FROM post WHERE post_id = ? AND student_id = ?");
        $stmt->bind_param("ii", $postId, $studentId);

        if ($stmt->execute()) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Invalid student number.";
    }
} else {
    echo "Invalid post ID.";
}
?>