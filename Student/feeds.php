<div class="feeds">
    <?php
    // Fetch all posts from the logged-in student and their friends
$stmt = $conn->prepare("
SELECT post.*, student.firstName, student.lastName, profile.image AS profile_image,
       IF(`like`.student_id IS NOT NULL, 1, 0) AS liked,
       IF(shared_post.shared_post_id IS NOT NULL, 1, 0) AS shared
FROM post 
JOIN student ON post.student_id = student.student_id 
LEFT JOIN profile ON student.student_id = profile.student_id
LEFT JOIN `like` ON post.post_id = `like`.post_id AND `like`.student_id = ?
LEFT JOIN shared_post ON post.post_id = shared_post.original_post_id AND shared_post.student_id = ?
WHERE post.student_id = ? OR post.student_id IN (
    SELECT CASE 
        WHEN from_student_id = ? THEN to_student_id 
        ELSE from_student_id 
    END AS friend_id 
    FROM request 
    WHERE (from_student_id = ? OR to_student_id = ?) AND status = 'accepted'
)
ORDER BY post.created_at DESC
");

// Bind the parameters
$stmt->bind_param("iiiiii", $studentId, $studentId, $studentId, $studentId, $studentId, $studentId);
$stmt->execute();
$result = $stmt->get_result();


    // Loop through each post and generate the HTML
    while ($post = $result->fetch_assoc()) {
        $postText = htmlspecialchars($post['text_content']);
        $postImage = htmlspecialchars($post['image']);
        $firstName = htmlspecialchars($post['firstName']);
        $lastName = htmlspecialchars($post['lastName']);
        $profileImage = !empty($post['profile_image']) ? 'php/images/' . htmlspecialchars($post['profile_image']) : 'images/default-profile.png';
        $createdAt = date('F j, Y, g:i a', strtotime($post['created_at']));
        $isShared = $post['shared'];

        // Fetch likes for this post
        $likesStmt = $conn->prepare("
            SELECT student.firstName, student.lastName, profile.image
            FROM `like`
            JOIN student ON `like`.student_id = student.student_id
            LEFT JOIN profile ON student.student_id = profile.student_id
            WHERE `like`.post_id = ?
            LIMIT 3  -- Show only first 3 likes
        ");
        $likesStmt->bind_param("i", $post['post_id']);
        $likesStmt->execute();
        $likesResult = $likesStmt->get_result();
        
        $likedBy = [];
        while ($like = $likesResult->fetch_assoc()) {
            $likedBy[] = [
                'name' => $like['firstName'] . ' ' . $like['lastName'],
                'image' => !empty($like['image']) ? 'php/images/' . htmlspecialchars($like['image']) : 'images/default-profile.png'
            ];
        }
        $likesStmt->close();

        // Fetch total like count
        $likeCountStmt = $conn->prepare("
            SELECT COUNT(*) as like_count
            FROM `like`
            WHERE post_id = ?
        ");
        $likeCountStmt->bind_param("i", $post['post_id']);
        $likeCountStmt->execute();
        $likeCountResult = $likeCountStmt->get_result()->fetch_assoc();
        $totalLikes = $likeCountResult['like_count'];
        $likeCountStmt->close();
        ?>
        
        <div class="feed">
            <div class="feed-top">
                <div class="user">
                    <div class="profile-pic">
                        <img src="<?php echo $profileImage; ?>" alt="Profile Image">
                    </div>
                    <div class="info">
                    <div class="name-verification">
                                        <div class="profile-name">
                                        <h3>
    <a href="Student/FriendProfile.php?student_id=<?php echo $post['student_id']; ?>" style="text-decoration: none; color: inherit;">
        <?php echo $firstName . ' ' . $lastName; ?>
    </a>
</h3>
                        </div>
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                        <small><?php echo $createdAt; ?></small>
                    </div>
                    <span class="edit">
                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                        <ul class="edit-menu">
                        <form action="" method="get" class="delete-form">
    <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
    <button type="submit" class="delete-button"><i class="fa fa-trash"></i> Delete</button>
</form>
                        </ul>
                    </span>
                </div>
            </div>

            <div class="caption">
                            <div classs="title" style="margin-top:5px;color:#94827F;">
                            <h3><p><?php echo $postText; ?></p></h3>
                            </div>
                         </div>
            <?php if (!empty($postImage)): ?>
            <div class="feed-img">
                <img src="php/images/<?php echo $postImage; ?>" alt="Post Image">
            </div>
            <?php endif; ?>

            <div class="action-button">
    <div class="interaction-button">
        <span class="like-button" data-post-id="<?php echo $post['post_id']; ?>" data-liked="<?php echo $post['liked']; ?>">
            <i class="fa fa-heart <?php echo $post['liked'] ? 'liked' : ''; ?>" aria-hidden="true"></i>
        </span>
        <span class="comment-button" data-post-id="<?php echo $post['post_id']; ?>"><i class="fa fa-comment-dots" aria-hidden="true"></i></span>
        <span class="share-button" data-post-id="<?php echo $post['post_id']; ?>" data-shared="<?php echo isset($post['shared']) ? $post['shared'] : 0; ?>">
    <i class="fa fa-share" aria-hidden="true" <?php echo isset($post['shared']) && $post['shared'] ? 'style="color:#01B2FF;"' : ''; ?>></i>
</span>
    </div>
</div>

<div class="liked-by">
    <?php if ($totalLikes > 0): ?>
        <?php foreach ($likedBy as $like): ?>
            <span><img src="<?php echo $like['image']; ?>" alt="Liked by <?php echo $like['name']; ?>"></span>
        <?php endforeach; ?>
        <?php if ($totalLikes == 1): ?>
            <p>Liked by <b><?php echo $likedBy[0]['name']; ?></b></p>
        <?php else: ?>
            <p>Liked by <b><?php echo $likedBy[0]['name']; ?></b> and <b><?php echo $totalLikes - 1; ?> others</b></p>
        <?php endif; ?>
    <?php endif; ?>
</div>

            <div class="comment-form">
            <form id="comment-form-<?php echo $post['post_id']; ?>" action="php/submit_comment.php" method="POST">
        <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
        <div class="comment-input-container">
        <?php 
                // Query to get the profile image from the profile table
                $sql = mysqli_query($conn, "SELECT p.image FROM profile p INNER JOIN student s ON p.student_id = s.student_id WHERE s.studentNo = {$_SESSION['studentNo']}");
                if(mysqli_num_rows($sql) > 0){
                    $row = mysqli_fetch_assoc($sql);
                    $profileImage = !empty($row['image']) && file_exists("php/images/{$row['image']}") ? 'php/images/' . htmlspecialchars($row['image']) : null;
                }
                if ($profileImage) {
                    echo '<img src="' . $profileImage . '" alt="Profile Picture" class="comment-profile-pic">';
                } else {
                    echo '<i class="fas fa-user comment-profile-pic-icon" style="font-size: 30px; color: #94827F;"></i>';
                }
            ?>
            <input type="text" name="comment" placeholder="Write a comment..." required class="comment-input">
            <button type="submit" class="submit-comment-button">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </form>
</div>


<div class="comments text-gry" data-post-id="<?php echo $post['post_id']; ?>">View all comments</div>
<div class="comments-container" id="comments-container-<?php echo $post['post_id']; ?>"></div>
        </div>

    <?php } ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const likeButtons = document.querySelectorAll('.like-button');
    const commentButtons = document.querySelectorAll('.comment-button');

    commentButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const commentForm = document.getElementById('comment-form-' + postId);
            if (commentForm) {
                commentForm.style.display = commentForm.style.display === 'none' ? 'block' : 'none';
            }
        });
    });

    document.querySelectorAll('.share-button').forEach(button => {
    button.addEventListener('click', function () {
        const postId = this.getAttribute('data-post-id');
        const isShared = this.getAttribute('data-shared') === '1';

        // If the post is already shared, do nothing
        if (isShared) {
            return;
        }

        // Make an AJAX request to share the post
        fetch('Student/share_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `post_id=${postId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page after the post is successfully shared
                window.location.reload();
                this.setAttribute('data-shared', '1');
            } else {
                alert('Failed to share the post');
            }
        })
        .catch(error => {
            console.error('Error sharing the post:', error);
        });
    });
});

    document.querySelectorAll('.comments.text-gry').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const commentsContainer = document.getElementById('comments-container-' + postId);

            if (commentsContainer) {
                // Check if the comments container is already visible
                if (commentsContainer.style.display === 'block') {
                    // If visible, hide it
                    commentsContainer.style.display = 'none';
                    this.innerText = 'View all comments'; // Change button text
                } else {
                    // If hidden, show it and fetch comments
                    commentsContainer.style.display = 'block';
                    this.innerText = 'Hide comments'; // Change button text

                    fetch('php/fetch_comments.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            'post_id': postId
                        })
                    })
                    .then(response => response.text())
                    .then(html => {
                        commentsContainer.innerHTML = html;
                    })
                    .catch(error => console.error('Error:', error));
                }
            }
        });
    });

    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const isLiked = this.getAttribute('data-liked') === '1';
            const action = isLiked ? 'unlike' : 'like';

            fetch('php/like_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    'post_id': postId,
                    'action': action
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const icon = this.querySelector('i');
                    if (action === 'like') {
                        icon.classList.add('liked');
                        this.setAttribute('data-liked', '1');
                    } else {
                        icon.classList.remove('liked');
                        this.setAttribute('data-liked', '0');
                    }
                } else {
                    console.error('Error:', data.error);
                }
            });
        });
    });
});
</script>

