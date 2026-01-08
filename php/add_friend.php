<?php
session_start();
require 'config.php'; // Adjust the path to your config file as needed

// Check if the user is logged in
if (!isset($_SESSION['studentNo'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch user data
$studentNo = $_SESSION['studentNo'];
$stmt = $conn->prepare("SELECT student_id FROM student WHERE studentNo = ?");
$stmt->bind_param("s", $studentNo);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fromStudentId = $row['student_id'];
} else {
    header('Location: ../logout.php');
    exit;
}

// Validate and process the friend request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $toStudentId = $_POST['to_student_id'];

    // Validate input
    if (!isset($toStudentId) || !is_numeric($toStudentId) || $toStudentId == $fromStudentId) {
        header('Location: ../Student/friends.php');
        exit;
    }

    // Check if the request is to cancel or add
    $stmt = $conn->prepare("SELECT status FROM request WHERE from_student_id = ? AND to_student_id = ?");
    $stmt->bind_param("ii", $fromStudentId, $toStudentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $requestStatus = $row['status'];

        if ($requestStatus === 'pending') {
            // Cancel the friend request
            $stmt = $conn->prepare("DELETE FROM request WHERE from_student_id = ? AND to_student_id = ?");
            $stmt->bind_param("ii", $fromStudentId, $toStudentId);
            if ($stmt->execute()) {
                header('Location: ../Student/friends.php?status=canceled');
            } else {
                header('Location: ../Student/friends.php?status=error');
            }
        } else {
            // Add a new friend request
            $stmt = $conn->prepare("INSERT INTO request (from_student_id, to_student_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $fromStudentId, $toStudentId);
            if ($stmt->execute()) {
                header('Location: ../Student/friends.php?status=success');
            } else {
                header('Location: ../Student/friends.php?status=error');
            }
        }
    } else {
        // No existing request, so add a new friend request
        $stmt = $conn->prepare("INSERT INTO request (from_student_id, to_student_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $fromStudentId, $toStudentId);
        if ($stmt->execute()) {
            header('Location: ../Student/friends.php?status=success');
        } else {
            header('Location: ../Student/friends.php?status=error');
        }
    }
    exit;
}
?>
