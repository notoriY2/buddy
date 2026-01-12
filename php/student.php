<?php
    session_start();
    include_once "config.php";
    $outgoing_id = $_SESSION['studentNo'];
    $sql = "SELECT * FROM student WHERE NOT studentNo = {$outgoing_id} ORDER BY student_id DESC";
    $query = mysqli_query($conn, $sql);
    $output = "";
    if(mysqli_num_rows($query) == 0){
        $output .= "No student are available to chat";
    }elseif(mysqli_num_rows($query) > 0){
        include_once "data.php";//rsa@9795
    }
    echo $output;
?>