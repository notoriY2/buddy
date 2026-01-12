<?php 
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(isset($_SESSION['studentNo'])){
    include_once "config.php";
    $outgoing_id = $_SESSION['studentNo'];
    $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
    $output = "";

    // Update SQL to join with the profile table to get images
    $sql = "SELECT messages.*, student.firstName, student.lastName, profile.image 
            FROM messages 
            LEFT JOIN student ON student.studentNo = messages.outgoing_msg_id
            LEFT JOIN profile ON student.student_id = profile.student_id
            WHERE (outgoing_msg_id = {$outgoing_id} AND incoming_msg_id = {$incoming_id})
            OR (outgoing_msg_id = {$incoming_id} AND incoming_msg_id = {$outgoing_id}) 
            ORDER BY msg_id";

    $query = mysqli_query($conn, $sql);
    if(mysqli_num_rows($query) > 0){
        while($row = mysqli_fetch_assoc($query)){
            // Determine the image source for outgoing messages
            if($row['outgoing_msg_id'] === $outgoing_id){
                $output .= '<div class="chat outgoing">
                            <div class="details">
                                <p>'. htmlspecialchars($row['msg']) .'</p>
                            </div>
                            </div>';
            }else{
                // Check if 'image' key exists and is not empty
                $imgSrc = !empty($row['image']) ? 'php/images/'.$row['image'] : 'php/images/default.png'; // Use a default image if not set
                $output .= '<div class="chat incoming">
                            <img src="'. htmlspecialchars($imgSrc) .'" alt="">
                            <div class="details">
                                <p>'. htmlspecialchars($row['msg']) .'</p>
                            </div>
                            </div>';
            }
        }
    }else{
        $output .= '<div class="text">No messages are available. Once you send message they will appear here.</div>';
    }
    echo $output;
}else{
    header("location: ../login.php");
}
?>
