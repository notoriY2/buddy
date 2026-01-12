<?php
if (isset($_POST['cropped_image'])) {
    $croppedImage = $_POST['cropped_image'];

    // Extract the base64 data from the string
    list($type, $data) = explode(';', $croppedImage);
    list(, $data) = explode(',', $data);
    $data = base64_decode($data);

    // Define the file path rsa@9795
    $filePath = '../uploads/cropped_image_' . time() . '.png';

    // Save the cropped image to a file
    if (file_put_contents($filePath, $data)) {
        echo "Image successfully saved as $filePath";
        
        // Here, you can add your database logic to save the file path.
        // Example:
        // $conn = new mysqli('hostname', 'username', 'password', 'database');
        // $stmt = $conn->prepare("INSERT INTO images (file_path) VALUES (?)");
        // $stmt->bind_param("s", $filePath);
        // $stmt->execute();
    } else {
        echo "Failed to save image.";
    }
} else {
    echo "No image data received.";
}
