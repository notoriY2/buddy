<?php
include('../php/config.php');
include('../php/session.php');

if (isset($_GET['id'])) {
    $group_id = intval($_GET['id']);

    // Step 1: Delete comments associated with posts of the group
    $deleteCommentsQuery = "
        DELETE c 
        FROM comment c
        INNER JOIN post p ON c.post_id = p.post_id
        WHERE p.group_id = $group_id";
    mysqli_query($conn, $deleteCommentsQuery);

    // Step 2: Delete likes associated with posts of the group
    $deleteLikesQuery = "
        DELETE l 
        FROM `like` l
        INNER JOIN post p ON l.post_id = p.post_id
        WHERE p.group_id = $group_id";
    mysqli_query($conn, $deleteLikesQuery);

    // Step 3: Delete shared posts related to the group's posts
    $deleteSharedPostsQuery = "
        DELETE sp
        FROM shared_post sp
        INNER JOIN post p ON sp.original_post_id = p.post_id
        WHERE p.group_id = $group_id";
    mysqli_query($conn, $deleteSharedPostsQuery);

    // Step 4: Delete posts associated with the group
    $deletePostsQuery = "DELETE FROM post WHERE group_id = $group_id";
    mysqli_query($conn, $deletePostsQuery);

    // Step 5: Delete group memberships
    $deleteMembershipsQuery = "DELETE FROM group_membership WHERE group_id = $group_id";
    mysqli_query($conn, $deleteMembershipsQuery);

    // Step 6: Delete join requests related to the group
    $deleteJoinRequestsQuery = "DELETE FROM group_join_request WHERE group_id = $group_id";
    mysqli_query($conn, $deleteJoinRequestsQuery);

    // Step 7: Delete the group itself
    $deleteGroupQuery = "DELETE FROM `group` WHERE group_id = $group_id";
    if (mysqli_query($conn, $deleteGroupQuery)) {
        // Redirect to the previous page
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        // Handle error during group deletion
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // Redirect to the previous page if no group ID is provided
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>