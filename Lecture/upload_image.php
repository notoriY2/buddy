<?php
include('../php/config.php');
include('../php/session.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$alertStyle = "";
$statusMsg = "";

// Check if the staffId is set in the session
if (!isset($_SESSION['staffId'])) {
    // If staffId is not set, redirect to the login page or show an error message
    header('Location: ../login_form.php'); // Redirect to login page
    exit(); // Stop further execution
}

// Assuming the staffId of the logged-in staff member is stored in a session variable
$staff_id = $_SESSION['staffId'];

if (isset($_POST['submit'])) {
    // Check if a file was uploaded
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profileImage']['tmp_name'];
        $fileName = $_FILES['profileImage']['name'];
        $fileSize = $_FILES['profileImage']['size'];
        $fileType = $_FILES['profileImage']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Allowed file extensions
        $allowedExtensions = array('jpeg', 'jpg', 'png');

        if (in_array($fileExtension, $allowedExtensions)) {
            // Set the upload directory
            $uploadFileDir = '../php/images/';
            $newFileName = $staff_id . '.' . $fileExtension; // Rename the file to avoid conflicts
            $dest_path = $uploadFileDir . $newFileName;

            // Move the file to the destination directory
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Update the database with the path to the uploaded image
                $updateQuery = mysqli_query($conn, "UPDATE staff SET image='$newFileName' WHERE staff_id='$staff_id'");

                if ($updateQuery) {
                    $_SESSION['alertStyle'] = "alert alert-success";
                    $_SESSION['statusMsg'] = "Profile image updated successfully!";
                } else {
                    $_SESSION['alertStyle'] = "alert alert-danger";
                    $_SESSION['statusMsg'] = "An error occurred while updating your profile image!";
                }
            } else {
                $_SESSION['alertStyle'] = "alert alert-danger";
                $_SESSION['statusMsg'] = "There was an error moving the uploaded file.";
            }
        } else {
            $_SESSION['alertStyle'] = "alert alert-danger";
            $_SESSION['statusMsg'] = "Upload failed. Only JPEG, JPG, and PNG files are allowed.";
        }
    } else {
        $_SESSION['alertStyle'] = "alert alert-danger";
        $_SESSION['statusMsg'] = "No file uploaded or there was an upload error.";
    }
}

// Redirect to the previous page with a query parameter
$redirectUrl = $_SERVER['HTTP_REFERER'] . (strpos($_SERVER['HTTP_REFERER'], '?') === false ? '?' : '&') . 'message=1';
header('Location: ' . $redirectUrl);
exit;
?>