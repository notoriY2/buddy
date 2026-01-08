<?php
include('config.php');
include('session.php');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['del_id']) && !empty($_GET['del_id'])) {
    $del_id = $_GET['del_id'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM course WHERE courseCode=?");
    $stmt->bind_param("s", $del_id);

    if ($stmt->execute()) {
        $_SESSION['alertStyle'] = 'alert alert-success';
        $_SESSION['statusMsg'] = 'Course deleted successfully!';
    } else {
        $_SESSION['alertStyle'] = 'alert alert-danger';
        $_SESSION['statusMsg'] = 'An error occurred while deleting the course.';
    }

    $stmt->close();
} else {
    $_SESSION['alertStyle'] = 'alert alert-danger';
    $_SESSION['statusMsg'] = 'Invalid course ID.';
}

// Redirect to createCourses.php
header("Location: ../Admin/createCourses.php");
exit();
?>
