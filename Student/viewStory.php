<?php
session_start();
require '../php/config.php'; // Adjust the path to your config file as needed

// Check if the user is logged in
if (!isset($_SESSION['studentNo'])) {
    header('Location: ../login.php');
    exit;
}

// Function to calculate the time elapsed
function timeElapsed($timestamp) {
    $time = strtotime($timestamp);
    if ($time === false) {
        return "Invalid date";
    }
    
    $currentTime = time();
    $timeDifference = $currentTime - $time;

    if ($timeDifference < 60) {
        return "Just now";
    } elseif ($timeDifference < 3600) {
        $minutes = round($timeDifference / 60);
        return "$minutes minutes ago";
    } elseif ($timeDifference < 86400) {
        $hours = round($timeDifference / 3600);
        return "$hours hours ago";
    } elseif ($timeDifference < 604800) { // 7 days in seconds
        $days = round($timeDifference / 86400);
        return "$days days ago";
    } else {
        return date('F j, Y', $time); // Return a formatted date if more than a week ago
    }
}

// Fetch user and profile data for the logged-in student
$studentNo = $_SESSION['studentNo'];
$stmt = $conn->prepare("SELECT student.student_id, student.firstName, student.lastName, student.email, profile.image FROM student LEFT JOIN profile ON student.student_id = profile.student_id WHERE student.studentNo = ?");
$stmt->bind_param("s", $studentNo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $loggedInStudentId = $row['student_id'];

    // Logged-in user's data for the navigation bar
    $navFirstName = htmlspecialchars($row['firstName']);
    $navLastName = htmlspecialchars($row['lastName']);
    $navProfileImage = htmlspecialchars($row['image']);
} else {
    // If no user data found for the logged-in student, force logout
    header('Location: ../logout.php');
    exit;
}

// Fetch profile data for the viewed student
$viewStudentId = intval($_GET['student_id']);
$stmt = $conn->prepare("SELECT student.firstName, student.lastName, profile.image FROM student LEFT JOIN profile ON student.student_id = profile.student_id WHERE student.student_id = ?");
$stmt->bind_param("i", $viewStudentId);
$stmt->execute();
$viewStudentResult = $stmt->get_result();

if ($viewStudentResult->num_rows > 0) {
    $viewRow = $viewStudentResult->fetch_assoc();
    $viewFirstName = htmlspecialchars($viewRow['firstName']);
    $viewLastName = htmlspecialchars($viewRow['lastName']);
    $viewProfileImage = htmlspecialchars($viewRow['image']);
} else {
    // If no user data found for the viewed student, redirect to users.php
    header('Location: ../users.php');
    exit;
}

// Fetch stories for the viewed student
$stmt = $conn->prepare("SELECT story_id, image, created_at FROM story WHERE student_id = ? AND expires_at > NOW() ORDER BY created_at ASC");
$stmt->bind_param("i", $viewStudentId);
$stmt->execute();
$storiesResult = $stmt->get_result();

if ($storiesResult->num_rows > 0) {
    $stories = [];
    while ($storyRow = $storiesResult->fetch_assoc()) {
        $stories[] = [
            'image' => htmlspecialchars($storyRow['image']),
            'created_at' => htmlspecialchars($storyRow['created_at']),
            'time_elapsed' => timeElapsed($storyRow['created_at']),
        ];
    }
} else {
    // No stories found for this student, redirect to users.php
    header('Location: ../users.php');
    exit;
}

// Fetch the next student with active stories
$nextStudentStmt = $conn->prepare("
    SELECT student_id 
    FROM story 
    WHERE expires_at > NOW() 
    AND student_id > ?
    ORDER BY student_id ASC
    LIMIT 1
");
$nextStudentStmt->bind_param("i", $viewStudentId);
$nextStudentStmt->execute();
$nextStudentResult = $nextStudentStmt->get_result();

$nextStudentId = null;
if ($nextStudentResult->num_rows > 0) {
    $nextStudentRow = $nextStudentResult->fetch_assoc();
    $nextStudentId = $nextStudentRow['student_id'];
}

// Fetch the previous student with active stories
$prevStudentStmt = $conn->prepare("
    SELECT student_id 
    FROM story 
    WHERE expires_at > NOW() 
    AND student_id < ?
    ORDER BY student_id DESC
    LIMIT 1
");
$prevStudentStmt->bind_param("i", $viewStudentId);
$prevStudentStmt->execute();
$prevStudentResult = $prevStudentStmt->get_result();

$prevStudentId = null;
if ($prevStudentResult->num_rows > 0) {
    $prevStudentRow = $prevStudentResult->fetch_assoc();
    $prevStudentId = $prevStudentRow['student_id'];
}
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
button[type="submit"] {
    width: 100%; /* Make submit buttons full width */
    margin-top: 10px; /* Add some space between buttons */
    padding: 10px;
    font-size: 16px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    background-color: #94827F; /* Button background color */
    color: white; /* Button text color */
    transition: background-color 0.3s ease, transform 0.3s ease; /* Smooth background change on hover */
}

button[type="submit"]:hover {
    background-color: #A99B99; /* Darker shade on hover */
    transform: scale(1.05); /* Slightly enlarge on hover */
}
/* Container for the preview section */
.preview-box {
            width: 100%; /* Full width */
            max-width: 700px; /* Maximum width for larger screens */
            height: 650px; /* Fixed height */
            padding: 20px;
            background-color: white; /* Background color for the preview box */
            border-radius: 10px; /* Rounded corners */
            margin-bottom: 20px; /* Space below the preview box */
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* Soft shadow */
            position: relative; /* For positioning child elements */
        }

        /* Styling for the "Preview" text */
        .preview-box h2 {
            margin: 0; /* Remove margin */
            font-size: 24px; /* Font size for the heading */
            color: #333; /* Text color */
            text-align: center; /* Center align the text */
            margin-bottom: 15px; /* Space below the text */
        }

        /* Smartphone crop box */
        .smartphone-crop-box {
            width: 100%; /* Full width of the preview box */
            height: 600px; /* Adjust height as needed */
            position: relative; /* For positioning child elements */
            overflow: hidden; /* Hide overflow */
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 10px;
        }

        /* Image container */
        .smartphone-screen img {
    width: 100%; /* Initially set width to 100% of the overlay box */
    height: auto; /* Maintain aspect ratio */
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%); /* Center the image */
    z-index: 1;
}


        /* Grey overlay with clear center */
        .overlay-box {
    width: 100%; /* Full width of the smartphone-crop-box */
    height: 100%; /* Full height of the smartphone-crop-box */
    position: absolute; /* Position relative to smartphone-crop-box */
    top: 0;
    left: 0;
    background-color: rgba(0, 0, 0, 0); /* Make it fully transparent */
    display: flex;
    justify-content: center; /* Center the clear box horizontally */
    align-items: center; /* Center the clear box vertically */
    z-index: 2; /* Place the overlay above the image */
}

       /* Ensure the clear-box shows the text clearly */
.clear-box {
    width: 350px;
    height: 600px;
    background-color: rgba(255, 255, 255, 0);
    border: 2px solid #94827F;
    pointer-events: auto; /* Make it interactive */
    position: relative;
    overflow: hidden;
    border-radius: 10px;
    background-size: cover;
    background-position: center;
}

.clear-box {
    width: 350px;
    height: 600px;
    background-color: rgba(255, 255, 255, 0);
    border: 2px solid #94827F;
    pointer-events: none;
    position: relative;
    overflow: hidden;
    border-radius: 10px;
}

/* Story header */
.story-header {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    padding: 10px;
    display: flex;
    flex-direction: column;
    pointer-events: none; /* Ensure it's non-interactive */
}

/* Progress bar */
.story-progress {
    height: 4px;
    background-color: #ddd;
    margin-bottom: 10px;
    border-radius: 2px;
    overflow: hidden;
}

.progress-bar {
    width: 0%;
    height: 100%;
    background-color: #94827F;
    animation: progress 15s linear forwards;
}

@keyframes progress {
    to {
        width: 100%;
    }
}

/* User and controls */
.story-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.user-info {
    display: flex;
    align-items: center;
}


.user-name h4 {
    margin: 0;
    font-size: 16px;
}

.user-name p {
    margin: 0;
    font-size: 12px;
    color: #666;
}

/* Ensure the story-controls are interactive */
.story-controls {
    pointer-events: auto;
}


.story-controls i {
    font-size: 18px;
    margin-left: 10px;
    cursor: pointer;
}

/* Navigation arrows */
.nav-arrows {
    position: absolute;
    top: 50%;
    width: 100%;
    display: flex;
    justify-content: space-between;
    transform: translateY(-50%);
    pointer-events: auto;
}

.nav-arrows i {
    color: white;
    font-size: 24px;
    padding: 10px;
    background-color: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    cursor: pointer;
}

/* Group Image */
.group-item img {
    width: 50px; /* Set the size of the group images */
    height: 50px;
    border-radius: 50%; /* Make the images circular */
    object-fit: cover; /* Maintain aspect ratio */
    transition: transform 0.3s ease; /* Smooth scale on hover */
}

/* Group Image Hover Animation */
.group-item img:hover {
    transform: scale(1.1); /* Slightly enlarge the image on hover */
}

/* Group Name */
.group-item span {
    margin-top: 10px;
    font-size: 16px;
    color: #333;
    font-weight: 500;
}
.group-item span:hover {
    transform: scale(1.1); /* Slightly enlarge the image on hover */
}


/* Plus Icon */
.profile-pic span i {
    font-size: 30px;
    padding: 10px;
    border-radius: 50%; /* Circle background for the icon */
    transition: background-color 0.3s ease, transform 0.3s ease;
    cursor: pointer;
}

/* Plus Icon Hover Effects */
.profile-pic span i:hover {
    transform: scale(1.1); /* Slightly enlarge the icon on hover */
}
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
/* Container for each story */
.story-container {
    position: relative; /* Allow positioning of overlay text */
    width: 100%; /* Adjust width as needed */
    max-width: 700px; /* Set max width */
    margin-bottom: 20px; /* Space below each story */
}

/* Story image container */
.story-image {
    width: 100%; /* Full width */
    height: 600px; /* Fixed height for stories */
    background-size: cover; /* Ensure image covers container */
    background-position: center; /* Center image */
    position: relative; /* For overlay positioning */
}

/* Overlay text */
.overlay-text {
    position: absolute; /* Position text over the image */
    top: 50%; /* Center vertically */
    left: 50%; /* Center horizontally */
    transform: translate(-50%, -50%); /* Adjust positioning */
    color: white; /* White text */
    font-size: 18px;
    font-weight: bold;
    padding: 10px;
    border-radius: 5px;
    text-align: center;
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
    width: 80%; /* Adjust width as needed */
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
        <input type="search" placeholder="Search for creators, inspirations and projects" />
    </div>
    <label class="btn btn-primary" for="create-post" id="create-lg1">Create Post</label>

    <a href="../php/logout.php?logout_id=<?php echo urlencode($_SESSION['studentNo']); ?>" class="logout-btn">Log Out</a>
    
    <a href="Student/StudentProfile.php">
        <div class="profile-pic" id="my-profile-picture" style="height: 40px; width: 40px; background-color: #b9b7c1; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
            <?php
            if ($navProfileImage) {
                echo '<img src="../php/images/' . htmlspecialchars($navProfileImage) . '" alt="Profile Picture" style="height: 100%; width: 100%; object-fit: cover; border-radius: 50%;">';
            } else {
                echo '<i class="fas fa-user" style="font-size: 18px; color: #fff;"></i>';
            }
            ?>
        </div>
    </a>
</div>
</nav>

    <div class="profile-container"- style="margin-top: 50px;">
        <div class="profile-info">
            <div class="info-col">
            <a href="../users.php" class="back">
    <i class="fas fa-times"></i>
</a>
                <div class="profile-intro">
                    <h3>Your Story</h3>
                    <hr>
                    <form class="create-group-form" action="" method="post" enctype="multipart/form-data">
                    <div class="profile-header" style="display: flex; align-items: center;">
                        <div class="profile-pic" style="height: 80px; width: 80px; background-color: #b9b7c1; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <a href="addStory.php" style="text-decoration: none; color: inherit;">
    <span><i class="fas fa-plus"></i></span>
</a>

                        </div>
                        <div class="profile-name" style="margin-left: 20px;">
                            <h2>Create a Story</h2>
                        </div>
                    </div>
            <hr>
            <h3>All Stories</h3>
            <div class="groups">
    <?php
    // Query to get students with active stories and their most recent story image
    $stmt = $conn->prepare("
        SELECT st.firstName, st.lastName, s.image AS recent_story_image 
        FROM story s
        JOIN student st ON s.student_id = st.student_id  
        WHERE s.expires_at > NOW() 
        AND s.created_at = (
            SELECT MAX(created_at) 
            FROM story 
            WHERE student_id = s.student_id
        )
        GROUP BY s.student_id
        ORDER BY s.created_at DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    // Loop through the results and display each student with their recent story image
    while ($story = $result->fetch_assoc()) {
        $firstName = htmlspecialchars($story['firstName']);
        $lastName = htmlspecialchars($story['lastName']);
        $recentStoryImage = !empty($story['recent_story_image']) ? '../php/images/' . htmlspecialchars($story['recent_story_image']) : '../images/78.png';
    ?>
        <!-- Display student's most recent story image -->
        <div class="group-item">
            <img src="<?php echo $recentStoryImage; ?>" alt="Recent Story">
            <span><?php echo $firstName . ' ' . $lastName; ?></span>
        </div>
    <?php } ?>
</div>
                    </form>
                </div>
            </div>
            <div class="preview-box" style="margin-left: 50px">
    <div class="smartphone-crop-box">
        <div class="smartphone-screen">
            <img id="preview-image" src="" alt="" style="transform: translate(-50%, -50%); position: absolute;">
            <div class="overlay-box">
                <div class="clear-box" id="clearbox">
                    <div class="story-header">
                        <div class="story-progress">
                            <div class="progress-bar"></div>
                        </div>
                        <div class="story-info" style="color:white;">
                        <div class="user-info">
                                    <!-- Profile Picture -->
                                    <?php
                                    echo '<img src="../php/images/' . htmlspecialchars($viewProfileImage) . '" alt="Profile Picture" class="user-profile-pic" style="height: 40px; width: 40px; border-radius: 50%; margin-right: 2px; object-fit: cover;">';
                                    ?>
                                    <!-- User Name and Time Left -->
                                    <div class="user-name">
                                        <h4><?php echo $viewFirstName . " " . $viewLastName; ?></h4>
                                        <p style="color: white;"><?php echo timeElapsed($story['created_at']); ?></p>
                                    </div>
                                </div>
                            <div class="story-controls">
    <i class="fas fa-pause" id="playPauseBtn" data-action="pause"></i>
    <i class="fas fa-ellipsis-h"></i>
</div>
                        </div>
                    </div>
                    
                </div>
                <div class="nav-arrows">
                        <i class="fas fa-chevron-left" id="prevBtn"></i>
                        <i class="fas fa-chevron-right" id="nextBtn"></i>
                    </div>
            </div>
        </div>
    </div>
</div>




</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const clearBox = document.querySelector('.clear-box');
    const progressBar = document.querySelector('.progress-bar');
    const playPauseBtn = document.getElementById('playPauseBtn');
    const stories = <?php echo json_encode($stories); ?>;
    
    let currentIndex = 0;
    let isPaused = false;
    let interval;

    const nextStudentId = <?php echo json_encode($nextStudentId); ?>;
    const prevStudentId = <?php echo json_encode($prevStudentId); ?>;

    function timeElapsed(timestamp) {
        const time = new Date(timestamp).getTime();
        const currentTime = Date.now();
        const timeDifference = currentTime - time;
        const seconds = Math.floor(timeDifference / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (seconds < 60) return "Just now";
        if (minutes === 1) return "1 minute ago";
        if (minutes < 60) return `${minutes} minutes ago`;
        if (hours === 1) return "1 hour ago";
        if (hours < 24) return `${hours} hours ago`;
        if (days === 1) return "1 day ago";
        return `${days} days ago`;
    }

    function resetProgressBar() {
        progressBar.style.animation = "none";
        progressBar.offsetHeight;  // Trigger reflow
        progressBar.style.animation = "";
    }

    function updateStory() {
        clearTimeout(interval);
        clearBox.style.backgroundImage = `url('../php/images/${stories[currentIndex].image}')`;
        clearBox.style.backgroundSize = "cover";
        clearBox.style.backgroundPosition = "center";
        document.querySelector('.user-name p').textContent = timeElapsed(stories[currentIndex].created_at);
        resetProgressBar();  // Reset the progress bar animation
        progressBar.style.animation = "progress 15s linear forwards";
        if (!isPaused) {
            interval = setTimeout(nextStory, 15000);
        }
    }

    function nextStory() {
        if (currentIndex < stories.length - 1) {
            currentIndex++;
            updateStory();
        } else if (nextStudentId) {
            // Redirect to the next student's stories
            window.location.href = `viewStory.php?student_id=${nextStudentId}`;
        }
    }

    function prevStory() {
        if (currentIndex > 0) {
            currentIndex--;
            updateStory();
        } else if (prevStudentId) {
            // Redirect to the previous student's stories
            window.location.href = `viewStory.php?student_id=${prevStudentId}`;
        }
    }

    function togglePlayPause() {
        if (isPaused) {
            playPauseBtn.classList.replace('fa-play', 'fa-pause');
            playPauseBtn.setAttribute('data-action', 'pause');
            progressBar.style.animationPlayState = 'running';
            interval = setTimeout(nextStory, 15000);
        } else {
            playPauseBtn.classList.replace('fa-pause', 'fa-play');
            playPauseBtn.setAttribute('data-action', 'play');
            progressBar.style.animationPlayState = 'paused';
            clearTimeout(interval);
        }
        isPaused = !isPaused;
    }

    // Event Listeners
    document.getElementById('nextBtn').addEventListener('click', nextStory);
    document.getElementById('prevBtn').addEventListener('click', prevStory);
    playPauseBtn.addEventListener('click', togglePlayPause);

    // Initial Story Load
    updateStory();
});

</script>

</body>
</html>