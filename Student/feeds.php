<div class="feeds">
    <?php
    // Fetch all posts from the database
$stmt = $conn->prepare("
SELECT post.*, student.firstName, student.lastName, profile.image AS profile_image,
       IF(`like`.student_id IS NOT NULL, 1, 0) AS liked
FROM post 
JOIN student ON post.student_id = student.student_id 
LEFT JOIN profile ON student.student_id = profile.student_id
LEFT JOIN `like` ON post.post_id = `like`.post_id AND `like`.student_id = ?
ORDER BY post.created_at DESC
");
$stmt->bind_param("i", $studentId);
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
                            <h3><?php echo $firstName . ' ' . $lastName; ?></h3>
                        </div>
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                        <small><?php echo $createdAt; ?></small>
                    </div>
                    <span class="edit">
                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                        <ul class="edit-menu">
                            <li><i class="fa fa-pen"></i>Edit</li>
                            <li><i class="fa fa-trash"></i>Delete</li>
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
        <span><i class="fa fa-share" aria-hidden="true"></i></span>
    </div>
</div>

<div class="liked-by">
                                             <span><img src="images/profile-17.jpg"></span>
                                             <span><img src="images/profile-18.jpg"></span>
                                             <span><img src="images/profile-19.jpg"></span>
                                             <p>Liked by <b>Enrest Achiever</b>and <b>220 others</b></p>
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


    document.querySelectorAll('.comments.text-gry').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const commentsContainer = document.getElementById('comments-container-' + postId);
            
            if (commentsContainer) {
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
        });
    });

    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const isLiked = this.getAttribute('data-liked') === '1';
            const action = isLiked ? 'unlike' : 'like';

            fetch('like_post.php', {
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

