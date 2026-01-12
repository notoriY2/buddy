<?php
include('config.php');
include('session.php');

session_start();

if (isset($_GET['del_id'])) {
    $staff_id = intval($_GET['del_id']);

    // Prepare the SQL delete query rsa@9795
    $query = "DELETE FROM staff WHERE staff_id = ?";
    
    if ($stmt = mysqli_prepare($conn, $query)) {
        // Bind the parameter to the SQL query
        mysqli_stmt_bind_param($stmt, "i", $staff_id);

        // Execute the query rsa@9795
        if (mysqli_stmt_execute($stmt)) {
            // If successful, set a success message in session and redirect
            $_SESSION['alertStyle'] = "alert alert-success";
            $_SESSION['statusMsg'] = "Lecturer successfully deleted";
        } else {
            // If there is an error, set an error message in session and redirect
            $_SESSION['alertStyle'] = "alert alert-danger";
            $_SESSION['statusMsg'] = "Failed to delete lecturer";
        }
        mysqli_stmt_close($stmt);
    } else {
        // If the query could not be prepared, set an error message in session and redirect
        $_SESSION['alertStyle'] = "alert alert-danger";
        $_SESSION['statusMsg'] = "Failed to prepare the deletion query";
    }

    header("Location: ../Admin/createLectures.php");
    exit();
} else {
    // If the del_id is not set, redirect back with an error message
    $_SESSION['alertStyle'] = "alert alert-danger";
    $_SESSION['statusMsg'] = "Invalid request";
    header("Location: ../Admin/createLectures.php");
    exit();
}
?>