<?php
session_start();
require 'config.php'; // Assuming this file contains your database connection code

// Check if the user is logged in
if (!isset($_SESSION['studentNo'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$studentNo = $_SESSION['studentNo'];
$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($postId <= 0 || !in_array($action, ['like', 'unlike'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

// Fetch user ID
$stmt = $conn->prepare("SELECT student_id FROM student WHERE studentNo = ?");
$stmt->bind_param("s", $studentNo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $studentId = $row['student_id'];

    if ($action === 'like') {
        // Insert like into the database
        $stmt = $conn->prepare("INSERT INTO `like` (post_id, student_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $postId, $studentId);
        $success = $stmt->execute();
    } else {
        // Remove like from the database
        $stmt = $conn->prepare("DELETE FROM `like` WHERE post_id = ? AND student_id = ?");
        $stmt->bind_param("ii", $postId, $studentId);
        $success = $stmt->execute();
    }

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'User not found']);
}
?>