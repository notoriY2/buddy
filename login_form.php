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
            window.location = 'Admin/createSession.php';
            </script>";
        } else if ($_SESSION['staffType_id'] == 2) { // Lecturer
            echo "<script type='text/javascript'>
            window.location = 'Lecture/viewFaculty.php';
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
    <title>Login Form</title>
    <link rel="stylesheet" href="css/styless.css">
    <link rel="stylesheet" href="css/sty.css">
</head>
<body>
    <div class="form-container">
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