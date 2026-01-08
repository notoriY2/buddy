<?php
session_start();
require '../php/config.php'; // Adjust the path to your config file as needed

// Check if the user is logged in
if (!isset($_SESSION['studentNo'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch user and profile data in one query
$studentNo = $_SESSION['studentNo'];
$stmt = $conn->prepare("
    SELECT 
        student.student_id, 
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
    WHERE student.studentNo = ?
");
$stmt->bind_param("s", $studentNo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $studentId = $row['student_id'];
    $firstName = htmlspecialchars($row['firstName']);
    $lastName = htmlspecialchars($row['lastName']);
    $email = htmlspecialchars($row['email']);
    $bio = htmlspecialchars($row['bio']);
    $homeLanguage = htmlspecialchars($row['home_language']);
    $highSchool = htmlspecialchars($row['high_school']);
    $city = htmlspecialchars($row['city']);
    $country = htmlspecialchars($row['country']);
    $profileImage = htmlspecialchars($row['image']);
    
    // Redirect to profile setup if the profile is not set
    if (is_null($profileImage)) {
        header('Location: profile.php');
        exit;
    }
} else {
    // If no user data found, force logout
    header('Location: ../logout.php');
    exit;
}

// Fetch posts for the logged-in student
$stmt = $conn->prepare("SELECT image FROM post WHERE student_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$postsResult = $stmt->get_result();

if (isset($_FILES['profileImage']) && isset($_SESSION['studentNo'])) {
    $studentNo = $_SESSION['studentNo'];
    $file = $_FILES['profileImage'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $file['tmp_name'];
        $fileName = basename($file['name']);
        $fileSize = $file['size'];
        $fileType = $file['type'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Check file type and size here if needed

        // Define the upload directory
        $uploadDir = '../php/images/';
        $newFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $newFilePath)) {
            // Update profile image path in the database
            $stmt = $conn->prepare("UPDATE profile SET image = ? WHERE student_id = (SELECT student_id FROM student WHERE studentNo = ?)");
            $stmt->bind_param("ss", $fileName, $studentNo);
            $stmt->execute();
            $stmt->close();
        } else {
            echo 'Error moving uploaded file.';
        }
    } else {
        echo 'File upload error.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chirag Social</title>
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

        </style>
</head>
<body>
<nav>
<div class="container">
        <img src="../images/12.png" alt="Buddy Logo" style="width: 50px; margin-left: 100px;">
        <div class="search-bar">
            <i class="uil uil-search"></i>
            <input type="search" placeholder="Search for creators, inspirations and projects" />
        </div>
        <label class="btn btn-primary" for="create-post" id="create-lg1">Create Post</label>
        
        <a href="Student/StudentProfile.php">
            <div class="profile-pic" id="my-profile-picture" style="height: 40px; width: 40px; background-color: #b9b7c1; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                <?php
                    if ($profileImage) {
                        echo '<img src="../php/images/' . htmlspecialchars($profileImage) . '" alt="Profile Picture" style="height: 100%; width: 100%; object-fit: cover; border-radius: 50%;">';
                    } else {
                        echo '<i class="fas fa-user" style="font-size: 30px; color: #94827F;"></i>';
                    }
                ?>
            </div>
        </a>
    </div>
</nav>
    <div class="profile-container" style="margin-top: 50px;">
        <img src="../images/22.jpg" class="cover-img" alt="Cover Image">
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
                        <div class="profile-name" style="margin-left: 20px;">
                            <h2><?php echo $firstName . ' ' . $lastName; ?></h2>
                        </div>
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="mutual-friends" style="margin-left: 20px">
                            <img src="../images/4.jpg" alt="Friend 1">
                            <img src="../images/5.jpg" alt="Friend 2">
                            <img src="../images/7.jpg" alt="Friend 3">
                            <img src="../images/13.jpg" alt="Friend 4">
                        </div>
                    </div>
                </div>
            </div>
            <div class="pd-right">
                <button type="button"><i class="fas fa-user-plus"></i> Friend</button>
                <button type="button"><i class="fas fa-comment-dots"></i> Message</button>
                <a href="#"><i class="fas fa-ellipsis-v"></i></a>
            </div>
        </div>
        <div class="profile-info">
            <div class="info-col">
            <div class="profile-intro">
    <h3>Intro</h3>
    <p class="intro-text">
        <?php echo $bio; ?>
        <i class="fas fa-feather-alt" style="color:#94827F; font-size: 20px;"></i>
        <!-- Add the Edit icon here -->
        <a href="../profile.php" class="edit-icon">
            <i class="fas fa-edit" style="color:#94827F; font-size: 20px;"></i>
        </a>
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
                        <a href="#">All Photos</a>
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
                        <a href="#">All Friends</a>
                    </div>
                    <p>120 (10 mutual)</p>
                    <div class="friends-box">
                        <div><img src="../images/7.jpg" alt="Friend 1"><p>Mothibi Bantjies</p></div>
                        <div><img src="../images/4.jpg" alt="Friend 2"><p>Lopang Leepo</p></div>
                        <div><img src="../images/5.jpg" alt="Friend 3"><p>Ingah Pahleni</p></div>
                        <div><img src="../images/13.jpg" alt="Friend 4"><p>Mpho Thomas</p></div>
                        <div><img src="../images/11.jpg" alt="Friend 5"><p>Itemogeng Hunt</p></div>
                        <div><img src="../images/16.jpg" alt="Friend 6"><p>Lemogang Rakgwele</p></div>
                    </div>
                </div>
            </div>
            <div class="post-col" style="margin-top: -15px;">
                <div class="feeds">
                <?php
    // Fetch only the logged-in student's posts
    $stmt = $conn->prepare("
    SELECT post.*, student.firstName, student.lastName, profile.image AS profile_image,
           IF(`like`.student_id IS NOT NULL, 1, 0) AS liked
    FROM post 
    JOIN student ON post.student_id = student.student_id 
    LEFT JOIN profile ON student.student_id = profile.student_id
    LEFT JOIN `like` ON post.post_id = `like`.post_id AND `like`.student_id = ?
    WHERE post.student_id = ?  -- Filter for only the logged-in student's posts
    ORDER BY post.created_at DESC
    ");
    $stmt->bind_param("ii", $studentId, $studentId); // Bind the logged-in student's ID
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
                <img src="../php/images/<?php echo $postImage; ?>" alt="Post Image">
            </div>
            <?php endif; ?>

            <div class="action-button">
                <div class="interaction-button">
                    <span class="like-button" data-post-id="<?php echo $post['post_id']; ?>" data-liked="<?php echo $post['liked']; ?>">
                        <i class="fa fa-heart <?php echo $post['liked'] ? 'liked' : ''; ?>" aria-hidden="true"></i>
                    </span>
                    <span><i class="fa fa-comment-dots" aria-hidden="true"></i></span>
                    <span><i class="fa fa-share" aria-hidden="true"></i></span>
                </div>
            </div>

            <div class="liked-by">
                <!-- You can dynamically display the people who liked this post here -->
                <p>Liked by <b>Username</b> and <b>others</b></p>
            </div>

            <div class="comments text-gry">View all comments</div>
        </div>

    <?php } ?>
                                    
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('profile-pic').addEventListener('click', function() {
        document.getElementById('file-input').click();
    });

    document.getElementById('file-input').addEventListener('change', function(event) {
        var file = event.target.files[0];
        if (file) {
            var formData = new FormData();
            formData.append('profileImage', file);

            // Send the file to the server via AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'upload_profile_image.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Refresh the page or update the profile picture
                    location.reload();
                } else {
                    alert('An error occurred while uploading the image.');
                }
            };
            xhr.send(formData);
        }
    });
</script>

</body>
</html>