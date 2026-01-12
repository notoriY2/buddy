<?php
include('config.php');
session_start();

if (isset($_SESSION['staffId'])) {
    $staffId = $_SESSION['staffId'];
    $staffType_id = $_SESSION['staffType_id'];
    
} elseif (isset($_SESSION['studentNo'])) {
    $studentNo = $_SESSION['studentNo'];
    // Add redirection for students if needed
} else {
    header('Location: ../login_form.php');
    exit;
}