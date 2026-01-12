<?php
session_start();
require 'config.php'; // Database connection

// Set error reporting to log instead of displaying (optional for debugging)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['studentNo'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Set studentNo from session
$studentNo = $_SESSION['studentNo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $originalPostId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if ($originalPostId > 0) {
        // Fetch student_id using studentNo
        $stmt = $conn->prepare("SELECT student_id FROM student WHERE studentNo = ?");
        if (!$stmt) {
            $error = $conn->error;
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $error]);
            exit;
        }

        $stmt->bind_param("s", $studentNo);
        if (!$stmt->execute()) {
            $error = $stmt->error;
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Execute failed: ' . $error]);
            exit;
        }

        $stmt->bind_result($studentId);
        $stmt->fetch();
        $stmt->close();

        if ($studentId) {
            // Insert the shared post into the shared_post table
            $stmt = $conn->prepare("
                INSERT INTO shared_post (student_id, original_post_id, shared_at)
                VALUES (?, ?, NOW())
            ");
            if (!$stmt) {
                $error = $conn->error;
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $error]);
                exit;
            }

            $stmt->bind_param("ii", $studentId, $originalPostId);

            header('Content-Type: application/json');
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $stmt->error]);
            }
            $stmt->close();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Student ID not found']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
    }
    $conn->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>