<?php
session_start();
require 'config.php'; // Include your database connection file

if (isset($_POST['post_id'])) {
    $post_id = intval($_POST['post_id']);

    // Query to fetch comments
    $query = $conn->prepare("
        SELECT comment.comment_text, student.firstName, student.lastName, profile.image AS profile_image
        FROM comment
        JOIN student ON comment.student_id = student.student_id
        LEFT JOIN profile ON student.student_id = profile.student_id
        WHERE comment.post_id = ?
        ORDER BY comment.created_at ASC
    ");
    $query->bind_param("i", $post_id);
    $query->execute();
    $result = $query->get_result();

    while ($row = $result->fetch_assoc()) {
        $firstName = htmlspecialchars($row['firstName']);
        $lastName = htmlspecialchars($row['lastName']);
        $commentText = htmlspecialchars($row['comment_text']);
        $profileImage = !empty($row['profile_image']) ? 'php/images/' . htmlspecialchars($row['profile_image']) : 'images/default-profile.png';

        echo '<div class="comment">';
        echo '<div class="comment-profile-pic"><img src="' . $profileImage . '" alt="Profile Image"></div>';
        echo '<div class="comment-text">';
        echo '<strong>' . $firstName . ' ' . $lastName . '</strong><p>' . $commentText . '</p>';
        echo '</div></div>';
    }

    $query->close();
    $conn->close();
}
?>