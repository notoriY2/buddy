<?php
include('../php/config.php');
include('../php/session.php');

if (isset($_GET['id'])) {
    $page_id = intval($_GET['id']);

    // Delete comments associated with posts of the page
    $deleteCommentsQuery = "
        DELETE c 
        FROM comment c
        INNER JOIN post p ON c.post_id = p.post_id
        WHERE p.page_id = $page_id";
    mysqli_query($conn, $deleteCommentsQuery);

    // Delete likes associated with posts of the page
    $deleteLikesQuery = "
        DELETE l 
        FROM `like` l
        INNER JOIN post p ON l.post_id = p.post_id
        WHERE p.page_id = $page_id";
    mysqli_query($conn, $deleteLikesQuery);

    // Delete shared posts referencing posts of the page
    $deleteSharedPostsQuery = "
        DELETE sp 
        FROM shared_post sp
        INNER JOIN post p ON sp.original_post_id = p.post_id
        WHERE p.page_id = $page_id";
    mysqli_query($conn, $deleteSharedPostsQuery);

    // Delete the posts associated with the page
    $deletePostsQuery = "DELETE FROM post WHERE page_id = $page_id";
    mysqli_query($conn, $deletePostsQuery);

    // Finally, delete the page itself
    $deletePageQuery = "DELETE FROM page WHERE page_id = $page_id";
    if (mysqli_query($conn, $deletePageQuery)) {
        // Redirect to the previous page
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        // Redirect to the previous page with an error message
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // Redirect to the previous page if no ID is provided
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>