<?php
include('../php/config.php');
include('../php/session.php');

if (isset($_GET['id'])) {
    $post_id = intval($_GET['id']);

    // First, delete likes related to this post
    $deleteLikesQuery = "DELETE FROM `like` WHERE post_id = $post_id";
    mysqli_query($conn, $deleteLikesQuery);

    // Next, delete comments related to this post
    $deleteCommentsQuery = "DELETE FROM comment WHERE post_id = $post_id";
    mysqli_query($conn, $deleteCommentsQuery);

    // Now, delete the post
    $deletePostQuery = "DELETE FROM post WHERE post_id = $post_id";
    if (mysqli_query($conn, $deletePostQuery)) {
        // Redirect to the previous page
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        // Redirect to the previous page with an error message
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // Redirect to the previous page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>