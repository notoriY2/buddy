<?php
include('config.php');
include('session.php');

$del_id = $_GET['del_id'];

$query = mysqli_query($conn, "DELETE FROM session WHERE session_id='$del_id'");

if ($query) {
    $_SESSION['alertStyle'] = 'alert alert-success';
    $_SESSION['statusMsg'] = 'Session deleted successfully!';
} else {
    $_SESSION['alertStyle'] = 'alert alert-danger';
    $_SESSION['statusMsg'] = 'An error occurred while deleting the session.';
}

echo "<script type = \"text/javascript\">
    window.location = (\"../Admin/createSession.php\")
    </script>";  
?>
