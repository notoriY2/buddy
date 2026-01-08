<?php 
session_start();
include_once "config.php";

$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

if (!empty($email) && !empty($password)) {
    $sql = mysqli_query($conn, "SELECT * FROM student WHERE email = '{$email}'");
    if (mysqli_num_rows($sql) > 0) {
        $row = mysqli_fetch_assoc($sql);
        $user_pass = md5($password); // You should ideally use password_verify if stored as hashed passwords
        $enc_pass = $row['password'];
        if (password_verify($password, $enc_pass)) {
            $status = "Active now";
            $sql2 = mysqli_query($conn, "UPDATE student SET status = '{$status}' WHERE studentNo = '{$row['studentNo']}'");
            if ($sql2) {
                $_SESSION['studentNo'] = $row['studentNo'];
                echo "success";
            } else {
                echo "Something went wrong. Please try again!";
            }
        } else {
            echo "Email or Password is Incorrect!";
        }
    } else {
        echo "$email - This email does not exist!";
    }
} else {
    echo "All input fields are required!";
}
?>
