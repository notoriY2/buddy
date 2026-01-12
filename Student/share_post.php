<?php
session_start();
require '../php/config.php';

// Check if the user is logged in
if (!isset($_SESSION['studentNo'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $postId = intval($_POST['post_id']);
    $studentNo = $_SESSION['studentNo'];

    // Fetch the student ID from the session
    $stmt = $conn->prepare("SELECT student_id FROM student WHERE studentNo = ?");
    $stmt->bind_param("s", $studentNo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $studentId = $row['student_id'];

        // Insert the shared post into the database
        $stmt = $conn->prepare("INSERT INTO shared_post (original_post_id, student_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $postId, $studentId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
}
?>