<?php
include('config.php');
include('session.php');

// Ensure `del_id` is set and is an integer
if (isset($_GET['del_id']) && is_numeric($_GET['del_id'])) {
    $del_id = intval($_GET['del_id']);

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM faculty WHERE faculty_id = ?");
    $stmt->bind_param("i", $del_id);

    if ($stmt->execute()) {
        $_SESSION['alertStyle'] = 'alert alert-success';
        $_SESSION['statusMsg'] = 'Faculty deleted successfully!';
    } else {
        $_SESSION['alertStyle'] = 'alert alert-danger';
        $_SESSION['statusMsg'] = 'An error occurred while deleting the faculty.';
    }

    $stmt->close();
} else {
    $_SESSION['alertStyle'] = 'alert alert-danger';
    $_SESSION['statusMsg'] = 'Invalid faculty ID.';
}

// Redirect to createFaculty.php
echo "<script type='text/javascript'>
    window.location = '../Admin/createFaculty.php';
    </script>";
?>