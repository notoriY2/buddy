<?php
session_start();
require 'config.php'; // Include your database connection file

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $post_id = $_POST['post_id'];
    $comment_text = trim($_POST['comment']);
    
    // Get the student ID from session
    $studentNo = $_SESSION['studentNo'];

    // Query to get the student_id from the student table
    $student_query = $conn->prepare("SELECT student_id FROM student WHERE studentNo = ?");
    $student_query->bind_param("s", $studentNo);
    $student_query->execute();
    $student_query->bind_result($student_id);
    $student_query->fetch();
    $student_query->close();

    // Check if comment is not empty
    if (!empty($comment_text)) {
        // Insert the comment into the database
        $stmt = $conn->prepare("INSERT INTO comment (post_id, student_id, comment_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $post_id, $student_id, $comment_text);

        if ($stmt->execute()) {
            // Set success message in session
            $_SESSION['comment_status'] = 'Comment successfully submitted.';
        } else {
            // Set error message in session
            $_SESSION['comment_status'] = 'Failed to submit comment.';
        }

        $stmt->close();
    } else {
        // Set error message for empty comment in session
        $_SESSION['comment_status'] = 'Comment cannot be empty.';
    }

    $conn->close();
    
    // Redirect to users.php
    header('Location: ../users.php');
    exit;
}
?>