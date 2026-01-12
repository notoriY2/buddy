<?php
include('../php/config.php');
include('../php/session.php');

if (isset($_GET['id'])) {
    $story_id = intval($_GET['id']);

    // Delete the story
    $deleteQuery = "DELETE FROM story WHERE story_id = $story_id";
    if (mysqli_query($conn, $deleteQuery)) {
        // Redirect to the previous page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
    } else {
        // Redirect to the previous page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
    }
} else {
    // Redirect to the previous page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>