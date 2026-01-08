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
$stmt = $conn->prepare("SELECT student.student_id, student.firstName, student.lastName, student.email, profile.image FROM student LEFT JOIN profile ON student.student_id = profile.student_id WHERE student.studentNo = ?");
$stmt->bind_param("s", $studentNo);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $studentId = $row['student_id'];
    $firstName = htmlspecialchars($row['firstName']);
    $lastName = htmlspecialchars($row['lastName']);
    $email = htmlspecialchars($row['email']);
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $groupName = htmlspecialchars($_POST['group_name']);
    $groupDescription = htmlspecialchars($_POST['group_description']);

    // Handle image upload
    $imageName = $_FILES['group_picture']['name'];
    $imageTmpName = $_FILES['group_picture']['tmp_name'];
    $imageSize = $_FILES['group_picture']['size'];
    $imageError = $_FILES['group_picture']['error'];
    $imageType = $_FILES['group_picture']['type'];

    $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($imageExt, $allowed)) {
        if ($imageError === 0) {
            if ($imageSize < 5000000) { // 5MB limit
                $imageNewName = uniqid('', true) . "." . $imageExt;
                $imageDestination = '../php/images/' . $imageNewName;
move_uploaded_file($imageTmpName, $imageDestination);


                // Insert group into database
                $stmt = $conn->prepare("INSERT INTO `group` (group_name, group_description, group_image) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $groupName, $groupDescription, $imageNewName);
                $stmt->execute();
                $groupId = $stmt->insert_id;

                // Add the creator as an admin of the group
                $stmt = $conn->prepare("INSERT INTO group_membership (group_id, student_id, role) VALUES (?, ?, 'admin')");
                $stmt->bind_param("ii", $groupId, $studentId);
                $stmt->execute();

                // Redirect or show success message
                header('Location: groups.php?success=1');
                exit;
            } else {
                $error = "Your image is too large!";
            }
        } else {
            $error = "There was an error uploading your image!";
        }
    } else {
        $error = "You cannot upload files of this type!";
    }
}

// Fetch groups from the database
$groups = $conn->query("SELECT * FROM `group` ORDER BY created_at DESC");

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
        .back {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px; /* Set the width of the button */
    height: 50px; /* Set the height of the button */
    background-color: #94827F; /* Background color for the button */
    color: white; /* Color of the icon */
    border-radius: 50%; /* Make the button circular */
    cursor: pointer; /* Change cursor to pointer on hover */
    transition: background-color 0.3s ease, transform 0.2s ease; /* Add transitions for effects */
    margin: 10px; /* Add some margin around the button */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add shadow for better visibility */
}

/* Hover effect */
.back:hover {
    background-color: #A99B99; /* Darker background color on hover */
    transform: scale(1.1); /* Slightly enlarge the button */
}

/* Focus effect */
.back:focus {
    outline: none; /* Remove default focus outline */
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.5); /* Add shadow on focus */
}
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
        <div class="profile-info">
            <div class="info-col">
                <div class="profile-intro">
                <h3>Create a Group</h3>
                <hr>
                <?php
                if (isset($error)) {
                    echo '<p style="color:red;">' . $error . '</p>';
                }
                ?>
                <form class="create-group-form" action="groups.php" method="post" enctype="multipart/form-data">
                    <label for="group-name">Group Name:</label>
                    <input type="text" id="group-name" name="group_name" required>

                    <label for="group-description">Group Description:</label>
                    <input type="text" id="group-description" name="group_description" required>
                    
                    <label for="group-picture">Profile Picture:</label>
                    <input type="file" id="group-picture" name="group_picture" accept="image/*" required>
                    
                    <button type="submit">Create Group</button>
                </form>
                </div>
                <div class="profile-intro">
                    <div class="title-box">
                        <h3>Groups</h3>
                        <a href="#">All Groups</a>
                    </div>
                    <hr>
                    <div class="groups">
                    <?php while ($group = $groups->fetch_assoc()) : ?>
    <div class="group-item">
        <img src="../php/images/<?= htmlspecialchars($group['group_image']); ?>" alt="<?= htmlspecialchars($group['group_name']); ?>">
        <span><?= htmlspecialchars($group['group_name']); ?></span>
    </div>
<?php endwhile; ?>
                    </div>
                </div>
            </div>
            <div class="post-col">
                <div class="feeds">
                    <div class="feed">
                        <div class="feed-top">
                            <div class="user">
                                <div class="profile-pic">
                                    <img src="images/76.png" alt="">
                                </div>
                                <div class="profile-pic" id="my-profile-picture">
                                    <img src="../php/images/1.jpg" alt="pic 1">
                                    
                                </div>
                                <div class="info">
                                    <h3>Drama Club</h3>
                                    <div class="name-verification">
                                        <small class="bigger"><?php echo $firstName . ' ' . $lastName; ?></small>
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <small>Colesberg, 4 DAYS AGO</small>
                                </div>
                                <span class="edit">
                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    <ul class="edit-menu">
                                        <li><i class="fa fa-pen"></i>Edit</li>
                                        <li><i class="fa fa-trash"></i>Delete</li>
                                    </ul>
                                </span>
                            </div>
            
                                     <div class="feed-img">
                                         <img src="images/76.png" alt="">
                                     </div>
            
                                     <div class="action-button">
                                         <div class="interaction-button">
                                             <span><i class="fa fa-heart" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-comment-dots" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-share" aria-hidden="true"></i></span>
                                         </div>
                                     </div>
            
                                     <div class="liked-by">
                                         <span><img src="images/profile-17.jpg"></span>
                                         <span><img src="images/profile-18.jpg"></span>
                                         <span><img src="images/profile-19.jpg"></span>
                                         <p>Liked by <b>Enrest Achiever</b>and <b>220 others</b></p>
                                     </div>
            
                                     <div class="caption">
                                        <div classs="title">
                                            could be anything
                                        </div>
                                         <p><b>Lana Rose</b>Lorem ipsum dolor storiesquiquam eius.
                                        <span class="hars-tag">#lifestyle</span></p>
                                     </div>
                                     <div class="comments text-gry">View all 130 comments</div>
                                    </div>
                                </div>
                            
                                <div class="feed">
                                    <div class="feed-top">
                                     <div class="user">
                                         <div class="profile-pic">
                                             <img src="images/GK.jpg" alt="">
                                         </div>
                                         <div class="profile-pic" id="my-profile-picture">
                                            <img src="images/1.jpg" alt="pic 1">
                                            
                                        </div>
                                        <div class="info">
                                            <h3>GEEKS</h3>
                                            <div class="name-verification">
                                                <small class="bigger"><?php echo $firstName . ' ' . $lastName; ?></small>
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <small>Kimberly, 16 DAYS AGO</small>
                                        </div>
                                         <SPAN class="edit">
                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            <ul class="edit-menu">
                                                <li><i class="fa fa-pen"></i></i>Edit</li>
                                                <li><i class="fa fa-trash"></i></i>Delete</li>
                                            </ul>
                                        </SPAN>
                                     </div>
            
                                     <div class="feed-img">
                                         <img src="images/72.jpg" alt="">
                                     </div>
            
                                     <div class="action-button">
                                         <div class="interaction-button">
                                             <span><i class="fa fa-heart" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-comment-dots" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-share" aria-hidden="true"></i></span>
                                         </div>
                                     </div>
            
                                     <div class="liked-by">
                                        <span><img src="images/profile-12.jpg"></span>
                                        <span><img src="images/profile-14.jpg"></span>
                                        <span><img src="images/profile-16.jpg"></span>
                                         <p>Liked by <b>Enrest Achiever</b>and <b>220 others</b></p>
                                     </div>
            
                                     <div class="caption">
                                        <div classs="title">
                                            could be anything
                                        </div>
                                         <p><b>Lana Rose</b>Lorem ipsum dolor storiesquiquam eius.
                                        <span class="hars-tag">#lifestyle</span></p>
                                     </div>
                                     <div class="comments text-gry">View all 130 comments</div>
                                    </div>
                                </div>
                            
                                <div class="feed">
                                    <div class="feed-top">
                                        <div class="user">
                                            <div class="profile-pic">
                                                <img src="images/79.jpg" alt="">
                                            </div>
                                            <div class="profile-pic" id="my-profile-picture">
                                                <img src="images/1.jpg" alt="pic 1">
                                                
                                            </div>
                                            <div class="info">
                                                <h3>Music Band</h3>
                                                <div class="name-verification">
                                                    <small class="bigger"><?php echo $firstName . ' ' . $lastName; ?></small>
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <small>Lost Angeles, 4 MONTHS AGO</small>
                                            </div>
                                            <div class="edit">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                <ul class="edit-menu">
                                                    <li><i class="fa fa-pen"></i></i>Edit</li>
                                                    <li><i class="fa fa-trash"></i></i>Delete</li>
                                                </ul>
                                            </div>
                                        </div>
            
                                     <div class="feed-img">
                                        <div classs="title">
                                            <h3>New Music Coming!!</h3>
                                        </div>
                                     </div>
            
                                     <div class="action-button">
                                         <div class="interaction-button">
                                             <span><i class="fa fa-heart" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-comment-dots" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-share" aria-hidden="true"></i></span>
                                         </div>
                                     </div>
            
                                     <div class="liked-by">
                                         <span><img src="images/profile-13.jpg"></span>
                                         <span><img src="images/profile-11.jpg"></span>
                                         <span><img src="images/profile-10.jpg"></span>
                                         <p>Liked by <b>Enrest Achiever</b>and <b>220 others</b></p>
                                     </div>
            
                                     <div class="caption">
                                        <div classs="title">
                                            could be anything
                                        </div>
                                         <p><b>Lana Rose</b>Lorem ipsum dolor storiesquiquam eius.
                                        <span class="hars-tag">#lifestyle</span></p>
                                     </div>
                                     <div class="comments text-gry">View all 130 comments</div>
                                    </div>
                                </div>
                                
                                <div class="feed">
                                    <div class="feed-top">
                                        <div class="user">
                                            <div class="profile-pic">
                                                <img src="images/78.png" alt="">
                                            </div>
                                            <div class="profile-pic" id="my-profile-picture">
                                                <img src="images/1.jpg" alt="pic 1">
                                                
                                            </div>
                                            <div class="info">
                                                <h3>Sports Team</h3>
                                                <div class="name-verification">
                                                    <small class="bigger"><?php echo $firstName . ' ' . $lastName; ?></small>
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <small>Paris, 8 HOURS AGO</small>
                                            </div>
                                            <SPAN class="edit">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                <ul class="edit-menu">
                                                    <li><i class="fa fa-pen"></i></i>Edit</li>
                                                    <li><i class="fa fa-trash"></i></i>Delete</li>
                                                </ul>
                                            </SPAN>
                                        </div>
            
                                     <div class="feed-img">
                                         <img src="images/78.png" alt="">
                                     </div>
            
                                     <div class="action-button">
                                         <div class="interaction-button">
                                             <span><i class="fa fa-heart" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-comment-dots" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-share" aria-hidden="true"></i></span>
                                         </div>
                                     </div>
            
                                     <div class="liked-by">
                                        <span><img src="images/profile-11.jpg"></span>
                                        <span><img src="images/profile-19.jpg"></span>
                                        <span><img src="images/profile-13.jpg"></span>
                                         <p>Liked by <b>Enrest Achiever</b>and <b>220 others</b></p>
                                     </div>
            
                                     <div class="caption">
                                        <div classs="title">
                                            could be anything
                                        </div>
                                         <p><b>Lana Rose</b>Lorem ipsum dolor storiesquiquam eius.
                                        <span class="hars-tag">#lifestyle</span></p>
                                     </div>
                                     <div class="comments text-gry">View all 130 comments</div>
                                    </div>
                                </div>
            </div>
            </div>
        </div>
    </div>
</body>
</html>