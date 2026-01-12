<<?php
session_start();
include_once "config.php"; // Include your database connection

// Get the notification IDs from the request body
$data = json_decode(file_get_contents("php://input"));
$notificationIds = $data->notification_ids;

// Ensure student is logged in
if (isset($_SESSION['studentNo'])) {
    $studentId = $_SESSION['studentNo'];

    // Prepare the statement to mark the notifications as read
    $stmt = $conn->prepare("UPDATE notification SET is_read = 1 WHERE notification_id IN (" . implode(',', array_fill(0, count($notificationIds), '?')) . ") AND student_id = ?");
    
    // Bind parameters dynamically
    $params = array_merge($notificationIds, [$studentId]);
    $types = str_repeat("i", count($notificationIds)) . "i"; // Assuming all IDs are integers

    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false]);
}
$conn->close();
?>
