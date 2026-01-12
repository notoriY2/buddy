<?php
session_start();
require 'php/config.php'; // Assuming this file contains your database connection code

if (!isset($_SESSION['studentNo'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentNo = $_SESSION['studentNo'];

    // Fetch the student ID
    $sql = mysqli_query($conn, "SELECT student_id FROM student WHERE studentNo = '$studentNo'");
    if (mysqli_num_rows($sql) > 0) {
        $row = mysqli_fetch_assoc($sql);
        $studentId = $row['student_id'];

        // Get form data
        $bio = mysqli_real_escape_string($conn, $_POST['bio']);
        $language = mysqli_real_escape_string($conn, $_POST['language']);
        $highschool = mysqli_real_escape_string($conn, $_POST['highschool']);
        $city = mysqli_real_escape_string($conn, $_POST['city']);
        $country = mysqli_real_escape_string($conn, $_POST['country']);

        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = basename($_FILES['image']['name']);
            $targetFile = "php/images/" . $image;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check !== false && ($_FILES['image']['size'] < 5000000) && in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $image = '';
                }
            } else {
                $image = '';
            }
        }

        // Insert profile data into the database
        $sql = "INSERT INTO profile (student_id, bio, home_language, high_school, city, country, image) 
                VALUES ('$studentId', '$bio', '$language', '$highschool', '$city', '$country', '$image')";
        if (mysqli_query($conn, $sql)) {
            // Profile setup successful, redirect to users.php
            header('Location: users.php');
            exit;
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<!-- Coding By CodingNepal - youtube.com/codingnepal -->
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="shortcut icon" type="Buddy-icon" href="images/12.png">
  <title>Buddy</title>
  <link rel="stylesheet" href="css/sty.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
  <style>

.back {
        position: absolute;
        top: 100px;
        left: 550px;
        width: 50px;
        height: 50px;
        background-color: #94827F;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        z-index: 1000; /* Ensure it stays on top */
    }

    .back:hover {
        background-color: #A99B99;
        transform: scale(1.1);
    }

    .back:focus {
        outline: none;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
    }
    </style>
</head>
<body>
<a href="users.php" class="back">
    <i class="fas fa-times"></i>
</a>
  <div class="wrapper" style="margin-top:20vh">
    <section class="form signup">
      <header>Set Up Profile</header>
      <form action="#" method="POST" enctype="multipart/form-data" autocomplete="off">
        <div class="error-text"></div>
        <div class="field input">
          <label>Bio</label>
          <input type="text" name="bio" placeholder="Add A BIO" required>
        </div>
        <div class="field input">
          <label>Home Language</label>
          <input type="text" name="language" placeholder="Home Language" required>
        </div>
        <div class="field input">
          <label>Went to</label>
          <input type="text" name="highschool" placeholder="High School" required>
        </div>
        <div class="name-details">
          <div class="field input">
            <label>From City</label>
            <input type="text" name="city" placeholder="City" required>
          </div>
          <div class="field input">
            <label>Country</label>
            <input type="text" name="country" placeholder="Country" required>
          </div>
        </div>
        <div class="field image">
          <label>Select Image</label>
          <input type="file" name="image" accept="image/x-png,image/gif,image/jpeg,image/jpg" required>
        </div>
        <div class="field button">
          <input type="submit" name="submit" value="Continue to Buddy">
        </div>
      </form>
    </section>
  </div>
</body>
</html>
