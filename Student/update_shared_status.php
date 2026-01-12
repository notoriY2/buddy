<?php
session_start();
require '../php/config.php';
if (!isset($_SESSION['studentNo'])) {
    header('Location: ../login.php');
    exit;
}

$postId = $_POST['post_id'];
$action = $_POST['action'];

if ($action === 'share') {
    $stmt = $conn->prepare("INSERT INTO shared_post (student_id, original_post_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $_SESSION['studentId'], $postId);
    $stmt->execute();
} else if ($action === 'unshare') {
    $stmt = $conn->prepare("DELETE FROM shared_post WHERE student_id = ? AND original_post_id = ?");
    $stmt->bind_param("ii", $_SESSION['studentId'], $postId);
    $stmt->execute();
}

echo 'Success';
?>
