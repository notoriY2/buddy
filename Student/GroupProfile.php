-<?php
session_start();
require '../php/config.php'; // Adjust the path to your config file as needed
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Check if the user is logged in
if (!isset($_SESSION['studentNo'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch user and profile data in one query
$studentNo = $_SESSION['studentNo'];
$stmt = $conn->prepare("SELECT student.student_id, student.firstName, student.lastName, student.email, profile.image 
    FROM student 
    LEFT JOIN profile ON student.student_id = profile.student_id 
    WHERE student.studentNo = ?");
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
// Fetch group data (assuming group_id is passed as a GET parameter)
$groupId = $_GET['group_id'] ?? null;
if ($groupId) {
    $stmtGroup = $conn->prepare("SELECT group_id, group_name, group_description, group_image FROM `group` WHERE group_id = ?");
    $stmtGroup->bind_param("i", $groupId);
    $stmtGroup->execute();
    $resultGroup = $stmtGroup->get_result();
    
    if ($resultGroup->num_rows > 0) {
        $groupRow = $resultGroup->fetch_assoc();
        $groupName = htmlspecialchars($groupRow['group_name']);
        $groupDescription = htmlspecialchars($groupRow['group_description']);
        $groupImage = htmlspecialchars($groupRow['group_image']);
    } else {
        // If no group data found, redirect or show an error
        header('Location: Groups.php');
        exit;
    }
} else {
    // If no group_id is provided, redirect or show an error
    header('Location: Groups.php');
    exit;
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
    <div class="profile-container">
        <img src="images/G.png" class="cover-img" alt="Cover Image">
        <div class="profile-details">
            <div class="pd-left">
                <div class="pd-row">
                <img src="../php/images/<?= $groupImage ?>" alt="">
                    <div>
                        <div class="name-verification">
                            <h3><?= $groupName ?></h3>
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="pd-right">
                <button type="button"><i class="fas fa-check-double"></i>  Following</button>
                <button type="button"><i class="fas fa-user-plus"></i> Invite</button>
                <a href="#"><i class="fas fa-ellipsis-v"></i></a>
            </div>
        </div>
        <div class="profile-info">
            <div class="info-col">
                <div class="profile-intro">
                    <h3>Intro</h3>
                    <p class="intro-text">Welcome to the Official Buddy Tech Page. Stay informed with the latest technological advancements, industry news, and innovative solutions. Engage with our community and collaborate with fellow tech enthusiasts.</p>
                    <hr>
                    <ul>
                        <li><i class="fa-solid fa-circle-exclamation"></i> Page , Tech</li>
                        <li><i class="fa-solid fa-globe"></i> https/:GEEKS</li>
                        <li><i class="fa-solid fa-star"></i> Highly Recommend</li>
                    </ul>
                </div>
                <div class="profile-intro">
                    <div class="title-box">
                        <h3>Photos</h3>
                        <a href="#">All Photos</a>
                    </div>
                    <div class="photo-box">
                        <div><img src="images/71.jpg" alt="Photo 1"></div>
                        <div><img src="images/70.png" alt="Photo 2"></div>
                        <div><img src="images/75.jpg" alt="Photo 3"></div>
                        <div><img src="images/73.jpg" alt="Photo 4"></div>
                        <div><img src="images/GK.jpg" alt="Photo 5"></div>
                        <div><img src="images/72.jpg" alt="Photo 6"></div>
                    </div>
                </div>
                <div class="profile-intro">
                    <div class="title-box">
                        <h3>Members</h3>
                        <a href="#">All Members</a>
                    </div>
                    <p>120 (10 mutual)</p>
                    <div class="friends-box">
                        <div><img src="images/profile-18.jpg" alt="Friend 1"><p>Mothibi Bantjies</p></div>
                        <div><img src="images/profile-17.jpg" alt="Friend 2"><p>Lopang Leepo</p></div>
                        <div><img src="images/profile-12.jpg" alt="Friend 3"><p>Ingah Pahleni</p></div>
                        <div><img src="images/profile-14.jpg" alt="Friend 4"><p>Mpho Thomas</p></div>
                        <div><img src="images/profile-19.jpg" alt="Friend 5"><p>Itemogeng Hunt</p></div>
                        <div><img src="images/profile-15.jpg" alt="Friend 6"><p>Lemogang Rakgwele</p></div>
                    </div>
                </div>
            </div>
            <div class="post-col">
                <div class="post">
                <form class="create-post">
                    <div class="profile-pic" id="my-profile-picture">
                        <img src="images/1.jpg" alt="Profile Picture">
                    </div>
                    <input type="text" placeholder="What's on your mind ?" id="create-lg2">
                    <input type="submit" value="Post" class="btn btn-primary">
                </form>
            </div>
                <div class="feeds">
                        <div class="feed">
                            <div class="feed-top">
                                <div class="user">
                                <div class="profile-pic">
    <img src="../php/images/<?= $groupImage ?>" alt="Group Image">
</div>
                                    <div class="profile-pic" id="my-profile-picture">
    <?php
        if ($profileImage) {
            echo '<img src="../php/images/' . htmlspecialchars($profileImage) . '" alt="Profile Picture">';
        } else {
            echo '<i class="fas fa-user"></i>';
        }
    ?>
</div>
                                    <div class="info">
                                        <h3><?= $groupName ?></h3>
                                        <div class="name-verification">
                                            <small class="bigger"><?php echo $firstName . ' ' . $lastName; ?></small>
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <small>Paris, 4 DAYS AGO</small>
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
                                             <img src="images/75.jpg" alt="">
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
    <img src="../php/images/<?= $groupImage ?>" alt="Group Image">
</div>
<div class="profile-pic" id="my-profile-picture">
    <?php
        if ($profileImage) {
            echo '<img src="../php/images/' . htmlspecialchars($profileImage) . '" alt="Profile Picture">';
        } else {
            echo '<i class="fas fa-user"></i>';
        }
    ?>
</div>
                                            <div class="info">
                                                <h3><?= $groupName ?></h3>
                                                <div class="name-verification">
                                                    <small class="bigger"><?php echo $firstName . ' ' . $lastName; ?></small>
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <small>Paris, 16 DAYS AGO</small>
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
    <img src="../php/images/<?= $groupImage ?>" alt="Group Image">
</div>
                                                <div class="profile-pic" id="my-profile-picture">
    <?php
        if ($profileImage) {
            echo '<img src="../php/images/' . htmlspecialchars($profileImage) . '" alt="Profile Picture">';
        } else {
            echo '<i class="fas fa-user"></i>';
        }
    ?>
</div>
                                                <div class="info">
                                                    <h3><?= $groupName ?></h3>
                                                    <div class="name-verification">
                                                        <small class="bigger"><?php echo $firstName . ' ' . $lastName; ?></small>
                                                        <i class="fas fa-check-circle"></i>
                                                    </div>
                                                    <small>Colesberg, 4 HOURS AGO</small>
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
                                                <h3>Your GEEKS</h3>
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
    <img src="../php/images/<?= $groupImage ?>" alt="Group Image">
</div>
                                                <div class="profile-pic" id="my-profile-picture">
    <?php
        if ($profileImage) {
            echo '<img src="../php/images/' . htmlspecialchars($profileImage) . '" alt="Profile Picture">';
        } else {
            echo '<i class="fas fa-user"></i>';
        }
    ?>
</div>
                                                <div class="info">
                                                    <h3><?= $groupName ?></h3>
                                                    <div class="name-verification">
                                                        <small class="bigger"><?php echo $firstName . ' ' . $lastName; ?></small>
                                                        <i class="fas fa-check-circle"></i>
                                                    </div>
                                                    <small>Kimberly, 2 MONTHS AGO</small>
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
                                             <img src="images/71.jpg" alt="">
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