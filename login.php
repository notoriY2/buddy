<?php 
session_start();
require 'php/config.php'; // Assuming this file contains your database connection code
error_reporting(E_ALL); // Report all errors
ini_set('display_errors', 1); // Display errors on the page

if (isset($_SESSION['studentNo'])) {
    $studentNo = $_SESSION['studentNo'];

    // Get student_id for the logged-in student
    $stmt = $conn->prepare("SELECT student_id FROM student WHERE studentNo = ?");
    $stmt->bind_param("s", $studentNo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $studentId = $row['student_id'];
        
        // Check if the student_id exists in the profile table
        $stmt = $conn->prepare("SELECT * FROM profile WHERE student_id = ?");
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Profile is set up, redirect to users.php
            header("Location: users.php");
        } else {
            // No profile found, redirect to profile setup
            header("Location: profile.php");
        }
    } else {
        // If no student data found, force logout or redirect to login
        header("Location: index.php");
    }
    exit;
}
?>


<!DOCTYPE html>
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
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px; /* Set the width of the button */
    height: 50px; /* Set the height of the button */
    background-color: #94827F; /* Background color for the button */
    color: white; /* Color of the icon */
    border-radius: 50%; /* Make the button circular */
    cursor: pointer; /* Change cursor to pointer on hover */
    transition: background-color 0.3s ease, transform 0.2s ease; /* Add transitions for effects */
    margin: 10px; /* Add some margin around the button */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add shadow for better visibility */
    margin-left:450px;
}

/* Hover effect */
.back:hover {
    background-color: #A99B99; /* Darker background color on hover */
    transform: scale(1.1); /* Slightly enlarge the button */
}

/* Focus effect */
.back:focus {
    outline: none; /* Remove default focus outline */
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.5); /* Add shadow on focus */
}
    </style> 
</head>
<body>
<a href="index.php" class="back">
    <i class="fas fa-times"></i>
</a>
  <div class="wrapper" style="margin-top:20vh">
    <section class="form login">
      <header>Welcome</header>
      <form action="php/login.php" method="POST" enctype="multipart/form-data" autocomplete="off">
        <div class="error-text"></div>
        <div class="field input">
          <label>Email</label>
          <input type="text" name="email" placeholder="Student No@buddy.ac.za" required>
        </div>
        <div class="field input">
          <label>Password</label>
          <input type="password" name="password" placeholder="Student No@buddy.123" required>
          <i class="fas fa-eye"></i>
        </div>
        <p><small><i>Note: By default, the password is set to "<b>Student No@buddy.123</b>"</i></small></p>
        <div class="field button">
          <input type="submit" name="submit" value="Continue to Chat">
        </div>
      </form>
    </section>
  </div>
  
  <script src="javascript/pass-show-hide.js"></script>
  <script src="javascript/login.js"></script>

</body>
</html>
