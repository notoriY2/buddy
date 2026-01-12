<?php
session_start(); // Start the session
include('config.php');
include('session.php');

if (isset($_GET['activate_id'])) {
    $activateId = $_GET['activate_id'];

    // Deactivate all active sessions rsa@9795
    $query1 = mysqli_query($conn, "UPDATE session SET isActive = 0 WHERE isActive = 1");
    if ($query1) {
        // Activate the selected session
        $query2 = mysqli_query($conn, "UPDATE session SET isActive = 1 WHERE session_id = '$activateId'");
        if ($query2) {
            $_SESSION['alertStyle'] = "alert alert-success";
            $_SESSION['statusMsg'] = "Session Activated Successfully!";
        } else {
            $_SESSION['alertStyle'] = "alert alert-danger";
            $_SESSION['statusMsg'] = "Failed To Activate Session!";
        }
    } else {
        $_SESSION['alertStyle'] = "alert alert-danger";
        $_SESSION['statusMsg'] = "Failed To Activate Session!";
    }
} else {
    $_SESSION['alertStyle'] = "alert alert-danger";
    $_SESSION['statusMsg'] = "Invalid session activation request!";
}

header("Location: ../Admin/createSession.php");
exit();
?>