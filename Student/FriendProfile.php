<?php
session_start();
require '../php/config.php'; // Adjust the path to your config file as needed

// Check if the user is logged in
if (!isset($_SESSION['studentNo'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch logged-in student's profile data for the navbar rsa@9795
$loggedStudentNo = $_SESSION['studentNo'];
$stmt = $conn->prepare("
    SELECT 
        student.student_id, 
        student.firstName, 
        student.lastName, 
        profile.image 
    FROM student 
    LEFT JOIN profile ON student.student_id = profile.student_id 
    WHERE student.studentNo = ?
");
$stmt->bind_param("s", $loggedStudentNo);
$stmt->execute();
$navbarResult = $stmt->get_result();

if ($navbarResult->num_rows > 0) {
    $navbarRow = $navbarResult->fetch_assoc();
    $navbarProfileImage = htmlspecialchars($navbarRow['image']);
} else {
    // If no user data found, force logout
    header('Location: ../logout.php');
    exit;
}

// Fetch the details of the passed student_id
if (isset($_GET['student_id'])) {
    $studentId = intval($_GET['student_id']);
    $stmt = $conn->prepare("
        SELECT 
            student.firstName, 
            student.lastName, 
            student.email, 
            profile.bio, 
            profile.home_language, 
            profile.high_school, 
            profile.city, 
            profile.country, 
            profile.image 
        FROM student 
        LEFT JOIN profile ON student.student_id = profile.student_id 
        WHERE student.student_id = ?
    ");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $firstName = htmlspecialchars($row['firstName']);
        $lastName = htmlspecialchars($row['lastName']);
        $email = htmlspecialchars($row['email']);
        $bio = htmlspecialchars($row['bio']);
        $homeLanguage = htmlspecialchars($row['home_language']);
        $highSchool = htmlspecialchars($row['high_school']);
        $city = htmlspecialchars($row['city']);
        $country = htmlspecialchars($row['country']);
        $profileImage = htmlspecialchars($row['image']);
    } else {
        echo "Student not found.";
        exit;
    }
} else {
    echo "No student ID provided.";
    exit;
}

// Fetch posts for the logged-in student
$stmt = $conn->prepare("SELECT image FROM post WHERE student_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$postsResult = $stmt->get_result();

// Check which view is requested, default to 'friends'
$view = isset($_GET['view']) ? $_GET['view'] : 'friends';

// Fetch data based on view
if ($view === 'requests') {
    // Fetch friend requests sent to the logged-in user
    $requestQuery = "
        SELECT 
            student.student_id AS student_id,
            student.firstName AS firstName,
            student.lastName AS lastName,
            profile.image AS profile_image
        FROM request
        JOIN student ON request.from_student_id = student.student_id
        LEFT JOIN profile ON student.student_id = profile.student_id
        WHERE request.to_student_id = ? AND request.status = 'pending'
    ";
    $stmt = $conn->prepare($requestQuery);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $dataResult = $stmt->get_result();
} else {
    // Fetch all students except the logged-in student
    $studentsQuery = "
        SELECT 
            student.student_id AS student_id,
            student.firstName AS firstName,
            student.lastName AS lastName,
            profile.image AS profile_image,
            COALESCE((SELECT status FROM request WHERE from_student_id = ? AND to_student_id = student.student_id), 'none') AS request_status
        FROM student
        LEFT JOIN profile ON student.student_id = profile.student_id
        WHERE student.student_id != ?
    ";
    $stmt = $conn->prepare($studentsQuery);
    $stmt->bind_param("ii", $studentId, $studentId);
    $stmt->execute();
    $dataResult = $stmt->get_result();
}
// Fetch friends where the status is 'accepted' both ways
$stmt = $conn->prepare("
    SELECT DISTINCT
        student.student_id, 
        student.firstName, 
        student.lastName, 
        profile.image 
    FROM request
    JOIN student ON (student.student_id = request.from_student_id OR student.student_id = request.to_student_id)
    LEFT JOIN profile ON student.student_id = profile.student_id
    WHERE 
        (request.from_student_id = ? OR request.to_student_id = ?) 
        AND request.status = 'accepted'
        AND student.student_id != ?
");
$stmt->bind_param("iii", $studentId, $studentId, $studentId);
$stmt->execute();
$friendsResult = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="Buddy-icon" href="../images/12.png">
    <title>Buddy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="./styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <style>
        .action-button {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
    padding: 10px 0;
    border-top: 1px solid #e0e0e0;
}

.interaction-button {
    display: flex;
    justify-content: space-around;
    flex-grow: 1; /* This makes the interaction buttons occupy full length */
}

.interaction-button span {
    cursor: pointer;
    font-size: 25px;
    color: #7a7a7a;
    transition: color 0.3s ease;
}

.interaction-button span:hover {
    color: #94827F;
    transform: scale(1.05);
    transition: transform 0.3s ease-in-out;
}
.interaction-button i {
    color: #94827F; /* Default color for the unliked state */
}

.interaction-button i.liked {
    color: red; /* Color for the liked state */
}

.edit-icon {
    text-decoration: none;
    color: #94827F;
    margin-left: 10px;
}

.edit-icon i {
    font-size: 20px;
}

.edit-icon:hover i {
    color: #6c6c6c; /* Color on hover */
}
.profile-pic-container {
    position: relative;
    display: inline-block;
}

#file-input {
    display: none;
}
.delete-button {
    background: none;
    border: none;
    color: #d9534f; /* Bootstrap danger color */
    cursor: pointer;
    font-size: 16px;
    transition: color 0.3s ease;
}

.delete-button:hover {
    color: #c9302c; /* Darker red color on hover */
    background: white;
}

.delete-button i {
    margin-right: 5px;
}
.profile-pic {
    width: 40px; /* Adjust size as needed */
    height: 40px; /* Adjust size as needed */
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #b9b7c1; /* Default background color if no image */
}
.profile-details .pd-left .pd-row img {
    width: 80px; /* Adjust the width as needed */
    height: 80px; /* Ensure the height matches the width for a perfect circle */
    border-radius: 50%; /* Makes the image circular */
    object-fit: cover; /* Ensures the image covers the entire area without distortion */
    border: 2px solid #ccc; /* Optional: Add a border around the image */
}
.comment-form {
    margin-top: 15px;
}

.comment-input-container {
    display: flex;
    align-items: center;
    background-color: #E1DEED;
    border-radius: 30px;
    padding: 10px;
    position: relative;
    max-width: 100%;
}

.comment-profile-pic {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
}

.comment-input {
    flex-grow: 1;
    background: none;
    border: none;
    padding: 10px;
    padding-right: 40px; /* Leave space for the submit button */
    font-size: 16px;
    color: #94827F;
    border-radius: 30px;
    outline: none;
}

.submit-comment-button {
    background: none;
    border: none;
    color: #94827F;
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 20px;
    cursor: pointer;
    outline: none;
}

.submit-comment-button:hover {
    color: #94827F;
    transition: transform 0.3s ease-in-out;
}
.comments {
    font-size: 14px;
    color: #6c757d; /* Gray color for the text */
    cursor: pointer; /* Shows a pointer cursor on hover */
    padding: 5px 10px; /* Padding for spacing */
    border-radius: 5px; /* Rounded corners */
    display: inline-block; /* Aligns as an inline block */
    text-decoration: none; /* Removes underline from links */
    transition: background-color 0.3s, color 0.3s; /* Smooth transition for background and text color */
}

.comments:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease-in-out;
    color: #94827F; /* Darker text color on hover */
}

.text-gry {
    font-weight: bold; /* Makes text bold */
}
.comments-container {
    margin-top: 20px;
    padding: 10px;
    background-color: #E1DEED; /* Light background for the comments container */
    border-radius: 30px; /* Rounded corners */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    max-width: 600px; /* Max width for the container */
    margin-left: auto;
    margin-right: auto;
    max-height: 350px; /* Maximum height for the comments container */
    overflow-y: auto; /* Enable vertical scrolling if content exceeds max height */
}

.comment {
    display: flex;
    align-items: flex-start;
    margin-bottom: 15px; /* Space between comments */
}

.comment-profile-pic {
    flex-shrink: 0; /* Prevent the profile pic from shrinking */
    margin-right: 10px; /* Space between profile pic and text */
}

.comment-profile-pic img {
    width: 40px;
    height: 40px;
    border-radius: 50%; /* Circular profile image */
    object-fit: cover; /* Ensure the image covers the area without distortion */
    border: 2px solid #ddd; /* Border around the profile image */
}

.comment-text {
    background-color: #ffffff; /* White background for the comment text */
    border-radius: 30px; /* Rounded corners */
    padding: 10px; /* Padding inside the comment text box */
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    max-width: 500px; /* Ensure the comment text doesn't stretch too far */
}

.comment-text strong {
    display: block;
    margin-bottom: 5px; /* Space between name and comment text */
    color: #333; /* Darker color for the name */
    font-size: 16px; /* Slightly larger font for the name */
}

.comment-text p {
    margin: 0;
    color: #94827F; /* Softer color for the comment text */
    font-size: 14px; /* Standard font size for the comment text */
    line-height: 1.5; /* Better readability with line height */
}

.search-results {
    position: absolute;
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 460px;
    max-height: 300px;
    overflow-y: auto;
    display: none;
    z-index: 10;
}

.search-results a {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #f0f0f0;
    text-decoration: none;
    color: #333;
    transition: background-color 0.3s ease;
}

.search-results a:last-child {
    border-bottom: none;
}

.search-results a:hover {
    background-color: #f9f9f9;
}

.search-results img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 15px;
    object-fit: cover;
}

.search-results span {
    font-size: 16px;
    font-weight: 500;
    color: #444;
}

.search-results p {
    padding: 15px;
    color: #999;
    text-align: center;
    font-size: 14px;
}

/* Mobile-friendly adjustments */
@media (max-width: 600px) {
    .search-results {
        width: 90%;
    }

    .search-results a {
        padding: 8px;
    }

    .search-results img {
        width: 35px;
        height: 35px;
    }

    .search-results span {
        font-size: 14px;
    }
}

.logout-btn {
    background-color: red;
    color: white;
    padding: 0.6rem 2rem;
    border-radius:2rem;
    cursor: pointer;
    border: none;
  }

  .logout-btn:hover {
    background-color: darkred;
  }
        </style>
</head>
<body>
<nav>
    <div class="container">
        <img src="../images/12.png" alt="Buddy Logo" style="width: 50px; margin-left: 100px;">
        <div class="search-bar">
    <i class="uil uil-search"></i>
    <input type="search" id="search-input" placeholder="Search for Students, Groups and Pages" />
    <div id="search-results" class="search-results"></div>
</div>

<a href="../php/logout.php?logout_id=<?php echo urlencode($_SESSION['studentNo']); ?>" class="logout-btn">Log Out</a>
        <a href="StudentProfile.php">
            <div class="profile-pic" id="my-profile-picture" style="height: 40px; width: 40px; background-color: #b9b7c1; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                <?php
                    if (!empty($navbarProfileImage)) {
                        echo '<img src="../php/images/' . $navbarProfileImage . '" alt="Profile Picture" style="height: 100%; width: 100%; object-fit: cover; border-radius: 50%;">';
                    } else {
                        echo '<i class="fas fa-user" style="font-size: 30px; color: #94827F;"></i>';
                    }
                ?>
            </div>
        </a>
    </div>
</nav>
    <div class="profile-container" style="margin-top: 60px;">
        <img src="../images/58i.png" class="cover-img" alt="Cover Image">
        <div class="profile-details">
            <div class="pd-left">
                <div class="pd-row">
                <div class="profile-pic-container">
    <div class="profile-pic" id="profile-pic" style="height: 90px; width: 90px; background-color: #b9b7c1; display: flex; align-items: center; justify-content: center; cursor: pointer;">
        <?php
        if ($profileImage) {
            echo '<img src="../php/images/' . htmlspecialchars($profileImage) . '" alt="Profile Picture" style="height: 100%; width: 100%;">';
        } else {
            echo '<i class="fas fa-user" style="font-size: 50px; color: #94827F;"></i>';
        }
        ?>
    </div>
    <input type="file" id="file-input" style="display: none;" accept="image/x-png,image/gif,image/jpeg,image/jpg">
</div>

                    <div>
                        <div class="name-verification">
                        <div class="profile-name" style="margin-left: 20px;margin-top:20px">
                            <h2><?php echo $firstName . ' ' . $lastName; ?></h2>
                        </div>
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="profile-info">
            <div class="info-col">
            <div class="profile-intro">
    <h3>Intro</h3>
    <p class="intro-text">
        <?php echo $bio; ?>
        <i class="fas fa-feather-alt" style="color:#94827F; font-size: 20px;"></i>
    </p>
    <hr>
    <ul>
        <li><i class="fas fa-user-circle"style="color:#94827F; font-size: 15px;"></i> Home Language: <?php echo $homeLanguage; ?></li>
        <li><i class="fas fa-user-graduate"style="color:#94827F; font-size: 15px;"></i> High School: <?php echo $highSchool; ?></li>
        <li><i class="fas fa-school"style="color:#94827F; font-size: 15px;"></i> City: <?php echo $city; ?></li>
        <li><i class="fas fa-home"style="color:#94827F; font-size: 15px;"></i> Country: <?php echo $country; ?></li>
    </ul>
</div>

                <div class="profile-intro">
                    <div class="title-box">
                        <h3>Photos</h3>
                    </div>
                    <div class="photo-box">
            <?php while ($post = $postsResult->fetch_assoc()): ?>
                <?php if ($post['image']): ?>
                    <div>
                        <img src="../php/images/<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image">
                    </div>
                <?php endif; ?>
            <?php endwhile; ?>
        </div>
                </div>
                <div class="profile-intro">
    <div class="title-box">
        <h3>Friends</h3>
        <a href="myFriends.php?student_id=<?php echo urlencode($studentId); ?>">All Friends</a>
    </div>
    <p><?php echo $friendsResult->num_rows; ?> (10 mutual)</p>
    <div class="friends-box">
        <?php while ($friend = $friendsResult->fetch_assoc()) { ?>
            <div>
                <img src="../php/images/<?php echo htmlspecialchars($friend['image']); ?>" alt="<?php echo htmlspecialchars($friend['firstName'] . ' ' . $friend['lastName']); ?>">
                <p><?php echo htmlspecialchars($friend['firstName'] . ' ' . $friend['lastName']); ?></p>
            </div>
        <?php } ?>
    </div>
</div>
            </div>
            <div class="post-col" style="margin-top: -15px;">
                <div class="feeds">
                <?php
    // Fetch only the logged-in student's posts
    // Updated query to fetch posts along with like and shared status
$stmt = $conn->prepare("
SELECT DISTINCT post.*, student.firstName, student.lastName, profile.image AS profile_image,
       IF(`like`.student_id IS NOT NULL, 1, 0) AS liked, 
       IF(shared_post.original_post_id IS NOT NULL, 1, 0) AS shared
FROM post 
JOIN student ON post.student_id = student.student_id 
LEFT JOIN profile ON student.student_id = profile.student_id
LEFT JOIN `like` ON post.post_id = `like`.post_id AND `like`.student_id = ?
LEFT JOIN shared_post ON post.post_id = shared_post.original_post_id
WHERE post.student_id = ?  -- Filter for only the logged-in student's posts
ORDER BY post.created_at DESC
");
$stmt->bind_param("ii", $studentId, $studentId);
$stmt->execute();
$result = $stmt->get_result();

    // Loop through each post and generate the HTML
    while ($post = $result->fetch_assoc()) {
        $postText = htmlspecialchars($post['text_content']);
        $postImage = htmlspecialchars($post['image']);
        $firstName = htmlspecialchars($post['firstName']);
        $lastName = htmlspecialchars($post['lastName']);
        $profileImage = !empty($post['profile_image']) ? '../php/images/' . htmlspecialchars($post['profile_image']) : 'images/default-profile.png';
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
                'image' => !empty($like['image']) ? '../php/images/' . htmlspecialchars($like['image']) : 'images/default-profile.png'
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
                            <h3><?php echo $firstName . ' ' . $lastName; ?></h3>
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
                <img src="../php/images/<?php echo $postImage; ?>" alt="Post Image">
            </div>
            <?php endif; ?>

            <div class="action-button">
    <div class="interaction-button">
        <!-- Like button -->
        <span class="like-button" data-post-id="<?php echo $post['post_id']; ?>"
            data-liked="<?php echo $post['liked']; ?>">
            <i class="fa fa-heart <?php echo $post['liked'] ? 'liked' : ''; ?>" aria-hidden="true"></i>
        </span>
        
        <!-- Comment button -->
        <span class="comment-button" data-post-id="<?php echo $post['post_id']; ?>">
            <i class="fa fa-comment-dots" aria-hidden="true"></i>
        </span>
        
        <!-- Share button -->
        <span class="share-button" data-post-id="<?php echo $post['post_id']; ?>" data-shared="<?php echo $post['shared']; ?>">
            <i class="fa fa-share" aria-hidden="true" <?php echo $post['shared'] ? 'style="color:#01B2FF;"' : ''; ?>></i>
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
            <form id="comment-form-<?php echo $post['post_id']; ?>" action="../php/submit_comment1.php" method="POST">
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
            </div>
        </div>
    </div>
    <script>

document.getElementById('search-input').addEventListener('keyup', function() {
    let searchQuery = this.value.trim();
    
    if (searchQuery.length >= 3) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'search.php?q=' + encodeURIComponent(searchQuery), true);
        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('search-results').innerHTML = this.responseText;
                document.getElementById('search-results').style.display = 'block';
            }
        };
        xhr.send();
    } else {
        document.getElementById('search-results').style.display = 'none';
    }
});

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
        fetch('share_post.php', {
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

                    fetch('../php/fetch_comments1.php', {
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

            fetch('../php/like_post.php', {
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
document.querySelector('#feed-pic-upload').addEventListener('change', () => {
            const fileInput = document.querySelector('#feed-pic-upload');
            if (fileInput.files.length > 0) {
                document.querySelector('#postImg').src = URL.createObjectURL(fileInput.files[0]);
            }
        });
window.addEventListener('scroll', () => {
            document.querySelector('.add-post-popup').style.display = 'none';
            
        });

document.querySelectorAll('.close').forEach(AllCloser => {
            AllCloser.addEventListener('click', () => {
                document.querySelector('.add-post-popup').style.display = 'none';
            })
        });

document.querySelector('#create-lg1').addEventListener('click', () => {
            document.querySelector('.add-post-popup').style.display = 'flex';
        });

        document.querySelector('#create-lg2').addEventListener('click', () => {
            document.querySelector('.add-post-popup').style.display = 'flex';
        });
document.querySelector('#feed-pic-upload').addEventListener('change', () => {
            const fileInput = document.querySelector('#feed-pic-upload');
            if (fileInput.files.length > 0) {
                document.querySelector('#postImg').src = URL.createObjectURL(fileInput.files[0]);
            }
        });
    

        document.querySelectorAll('.action-button span:first-child i').forEach(liked=> {
            liked.addEventListener('click',()=>{
            liked.classList.toggle('liked');
            })
        });
</script>
</body>
</html>