<?php
include('config.php');
include('session.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['del_id'])) {
    $id = $_GET['del_id'];

    // Retrieve staff_id before deleting the assignment
    $stmt = $conn->prepare("SELECT staff_id FROM assignedlecture WHERE assignLecture_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $staff_id = $row['staff_id'];

    // Delete the assignment
    $stmt = $conn->prepare("DELETE FROM assignedlecture WHERE assignLecture_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Update staff assignment status
        $stmt = $conn->prepare("UPDATE staff SET isAssigned = 0 WHERE staff_id = ?");
        $stmt->bind_param("i", $staff_id);
        
        if ($stmt->execute()) {
            $_SESSION['alertStyle'] = 'alert alert-success';
            $_SESSION['statusMsg'] = 'Lecturer deleted successfully!';
        } else {
            $_SESSION['alertStyle'] = 'alert alert-danger';
            $_SESSION['statusMsg'] = 'An error occurred while updating the staff assignment status.';
        }
    } else {
        $_SESSION['alertStyle'] = 'alert alert-danger';
        $_SESSION['statusMsg'] = 'An error occurred while deleting the lecturer.';
    }
    $stmt->close();
}

// Redirect to createLectures.php
header("Location: ../Admin/createLectures.php");
exit();
?>



