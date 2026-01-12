<?php
include('config.php');
include('session.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);
$del_id = $_GET['del_id'];

$query = mysqli_query($conn, "DELETE FROM student WHERE studentNo='$del_id'");

if ($query == TRUE) {
    $_SESSION['alertStyle'] = 'alert alert-success';
    $_SESSION['statusMsg'] = 'Student deleted successfully!';
} else {
    $_SESSION['alertStyle'] = 'alert alert-danger';
    $_SESSION['statusMsg'] = 'An error occurred while deleting the student!';
}

echo "<script type='text/javascript'>window.location = '../Admin/createStudent.php';</script>";
?>


