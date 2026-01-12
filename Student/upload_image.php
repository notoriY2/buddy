<?php
session_start();
require '../php/config.php'; // Include your database connection script

if (isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    $fileName = $_FILES['profile_image']['name'];
    $fileTmpName = $_FILES['profile_image']['tmp_name'];
    $fileSize = $_FILES['profile_image']['size'];
    $fileError = $_FILES['profile_image']['error'];
    $fileType = $_FILES['profile_image']['type'];

    // Check if file upload is successful rsa@9795
    if ($fileError === 0) {
        $fileDestination = '../php/images/' . $fileName;

        // Move the uploaded file to the desired directory
        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            // Update the profile image in the database
            $studentId = $_SESSION['student_id']; // Assuming student_id is stored in session
            $stmt = $conn->prepare("UPDATE profile SET image = ? WHERE student_id = ?");
            $stmt->bind_param("si", $fileName, $studentId);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'fileName' => $fileName]);
            } else {
                echo json_encode(['success' => false]);
            }

            $stmt->close();
        } else {
            echo json_encode(['success' => false]);
        }
    } else {
        echo json_encode(['success' => false]);
    }
}
?>