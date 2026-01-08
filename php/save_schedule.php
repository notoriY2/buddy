<?php 
require_once('config.php');
session_start();

if($_SERVER['REQUEST_METHOD'] != 'POST'){
    $_SESSION['statusMsg'] = 'Error: No data to save.';
    $_SESSION['alertStyle'] = 'alert-danger';
    header('Location: ../Student/index.php');
    exit;
}

extract($_POST);
$allday = isset($allday);

if(empty($id)){
    $sql = "INSERT INTO `schedule_list` (`title`,`description`,`start_datetime`,`end_datetime`) VALUES ('$title','$description','$start_datetime','$end_datetime')";
}else{
    $sql = "UPDATE `schedule_list` SET `title` = '{$title}', `description` = '{$description}', `start_datetime` = '{$start_datetime}', `end_datetime` = '{$end_datetime}' WHERE `id` = '{$id}'";
}

$save = $conn->query($sql);

if($save){
    $_SESSION['statusMsg'] = 'Schedule Successfully Saved.';
    $_SESSION['alertStyle'] = 'alert-success';
} else {
    $_SESSION['statusMsg'] = 'An error occurred: ' . $conn->error;
    $_SESSION['alertStyle'] = 'alert-danger';
}

$conn->close();
header('Location: ../Student/index.php');
exit;
?>