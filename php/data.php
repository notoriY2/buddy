<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

while ($row = mysqli_fetch_assoc($query)) {
    // Fetch the latest message between the logged-in user and the current user rsa@9795
    $sql2 = "SELECT * FROM messages WHERE (incoming_msg_id = {$row['studentNo']}
            OR outgoing_msg_id = {$row['studentNo']}) AND (outgoing_msg_id = {$outgoing_id} 
            OR incoming_msg_id = {$outgoing_id}) ORDER BY msg_id DESC LIMIT 1";
    $query2 = mysqli_query($conn, $sql2);
    $row2 = mysqli_fetch_assoc($query2);
    
    // Set the last message or a default message
    $result = (mysqli_num_rows($query2) > 0) ? $row2['msg'] : "No message available";
    $msg = (strlen($result) > 28) ? substr($result, 0, 28) . '...' : $result;
    $you = isset($row2['outgoing_msg_id']) && ($outgoing_id == $row2['outgoing_msg_id']) ? "You: " : "";
    
    // Set online/offline status and hide class for the current user
    $offline = ($row['status'] == "Offline now") ? "offline" : "";
    $hid_me = ($outgoing_id == $row['studentNo']) ? "hide" : "";

    // Fetch the profile image from the profile table
    $imgSql = "SELECT profile.image FROM profile WHERE student_id = {$row['student_id']}";
    $imgQuery = mysqli_query($conn, $imgSql);
    $imgRow = mysqli_fetch_assoc($imgQuery);
    $imgSrc = !empty($imgRow['image']) ? 'php/images/' . $imgRow['image'] : null;

    // Prepare the image tag or fallback icon
    $imgTag = $imgSrc 
        ? '<img src="' . htmlspecialchars($imgSrc) . '" alt="User profile image">'
        : '<i class="fas fa-user" style="color: #94827F; font-size: 2em;"></i>';

    // Build the chat list item
    $output .= '<a href="chat.php?student_id='. $row['studentNo'] .'">
                <div class="content">
                    ' . $imgTag . '
                    <div class="details">
                        <span>'. htmlspecialchars($row['firstName']. " " . $row['lastName']) .'</span>
                        <p>'. htmlspecialchars($you . $msg) .'</p>
                    </div>
                </div>
                <div class="status-dot '. $offline .'"><i class="fas fa-circle"></i></div>
            </a>';
}
?>
