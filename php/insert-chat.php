<?php 
    session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if(isset($_SESSION['studentNo'])){
        include_once "config.php";

        // Retrieve and sanitize POST data
        $outgoing_id = $_SESSION['studentNo'];
        $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);

        // Debugging: Check if POST data is correct rsa@9795
        if (empty($message)) {
            echo "Message is empty.";
            exit();
        }
        if (empty($incoming_id)) {
            echo "Incoming ID is empty.";
            exit();
        }

        // Insert message into database
        $sql = "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg) VALUES ({$incoming_id}, {$outgoing_id}, '{$message}')";
$query = mysqli_query($conn, $sql);

if (!$query) {
    die("SQL Error: " . mysqli_error($conn));
}

        echo "Message sent!";
    } else {
        header("location: ../login.php");
    }
?>
