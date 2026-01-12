<?php
@include 'php/config.php';

session_start();
error_reporting(0);

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM staff WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Verify password
    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['staffId'] = $row['staff_id'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['name'] = $row['name'];
        $_SESSION['staffType_id'] = $row['staffType_id'];

        if ($_SESSION['staffType_id'] == 1) { // Admin
            echo "<script type='text/javascript'>
            window.location = 'Admin/Dashboard.php';
            </script>";
        } else if ($_SESSION['staffType_id'] == 2) { // Lecturer
            echo "<script type='text/javascript'>
            window.location = 'Lecture/Dashboard.php';
            </script>";
        }
    } else {
        $errorMsg = "<div class='alert alert-danger' role='alert'>Invalid email/Password!</div>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="Buddy-icon" href="images/12.png">
    <title>Buddy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
    <link rel="stylesheet" href="css/styless.css">
    <link rel="stylesheet" href="css/sty.css">

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
    margin-top:-550px;
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
    <div class="form-container">
    <a href="index.php" class="back">
    <i class="fas fa-times"></i>
</a>
        <form action="" method="post">
            <h3>Login Now</h3>
            <?php if (isset($errorMsg)) echo $errorMsg; ?>
            <label>Email</label>
            <input type="text" name="email" required class="form-control" placeholder="Staff No@buddy.ac.za">
            <label>Password</label>
            <input type="password" name="password" required class="form-control" placeholder="Staff No@buddy.123">
            <p><small><i>Note: By default, the password is set to "<b>Staff No@buddy.123</b>"</i></small></p>
            <input type="submit" name="submit" value="Login Now" class="form-btn">
        </form>
    </div>
</body>
</html>