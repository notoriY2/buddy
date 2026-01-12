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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storyText = trim($_POST['story_text']);

    // Default image if none is provided
    $storyImage = '009.png'; // Default image path

    // Insert the story into the database
    $stmt = $conn->prepare("INSERT INTO story (text, image, student_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $storyText, $storyImage, $studentId);

    // Set expiry time for the story (1 day from now)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));

    // Insert the story into the database with the expiry time
    $stmt = $conn->prepare("INSERT INTO story (text, image, student_id, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $storyText, $storyImage, $studentId, $expiresAt);
    if ($stmt->execute()) {
        header('Location: ../users.php'); // Redirect after successful submission
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
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
            height: 550px; /* Adjust height as needed */
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
    width: 300px; /* Set width of the clear box */
    height: 550px; /* Set height of the clear box */
    background-color: rgba(255, 255, 255, 0); /* Transparent to show the image */
    border: 2px solid #94827F; /* Optional: Add a border to visualize the clear box */
    box-shadow: none; /* Remove the shadow to ensure no overlay effect */
    pointer-events: none; /* Make the clear box non-interactive */
    display: flex;
    justify-content: center; /* Center text horizontally */
    align-items: center; /* Center text vertically */
    font-size: 24px; /* Font size for the text */
    color: #333; /* Text color */
    text-align: center; /* Center text alignment */
    overflow: hidden; /* Hide overflow */
    padding: 10px; /* Add some padding to ensure text doesn't touch edges */
    word-wrap: break-word; /* Allow text to wrap onto the next line */
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
                    <h3>Your Story</h3>
                    <hr>
                    <form class="create-group-form" action="" method="post" enctype="multipart/form-data">
                    <div class="profile-header" style="display: flex; align-items: center;">
                        <div class="profile-pic" style="height: 80px; width: 80px; background-color: #b9b7c1; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                            <?php
                            if ($profileImage) {
                                echo '<img src="../php/images/' . htmlspecialchars($profileImage) . '" alt="Profile Picture" style="height: 100%; width: 100%; object-fit: cover; border-radius: 50%;">';
                            } else {
                                echo '<i class="fas fa-user" style="font-size: 50px; color: #94827F;"></i>';
                            }
                            ?>
                        </div>
                        <div class="profile-name" style="margin-left: 20px;">
                            <h2><?php echo $firstName . ' ' . $lastName; ?></h2>
                        </div>
                    </div>
            <hr>
            <input type="text" id="text-input" name="story_text" required placeholder="Start Typing" style="height: 100px; font-size: 20px; padding: 10px; width: 100%;">

<button type="submit" style ="background: #E1DEED">Discard</button>
<button type="submit">Share</button>
                    </form>
                </div>
            </div>
            <div class="preview-box">
    <h2>Preview</h2>
    <div class="smartphone-crop-box">
        <div class="smartphone-screen">
            <img id="preview-image" src="" alt="" style="transform: translate(-50%, -50%); position: absolute;">
            <div class="overlay-box">
                <div class="clear-box"></div>
            </div>
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
    const textInput = document.getElementById('text-input');
    const clearBox = document.querySelector('.clear-box');

    // Set initial placeholder text
    clearBox.textContent = 'Start Typing';

    // Update clear-box text on input change
    textInput.addEventListener('input', function(event) {
        clearBox.textContent = event.target.value || 'Start Typing'; // Display typed text or placeholder
    });
});
</script>

</body>
</html>