<?php
session_start();
require '../php/config.php'; // Adjust the path to your config file as needed rsa@9795

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

    // Insert the story into the database
    $stmt = $conn->prepare("INSERT INTO story (text, student_id) VALUES (?, ?)");
    $stmt->bind_param("si", $storyText, $studentId);
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
                    <form class="create-group-form" action="" method="post" enctype="multipart/form-data">
                    <div class="profile-header" style="display: flex; align-items: center;">
                        <div class="profile-pic" style="height: 50px; width: 50px; background-color: #b9b7c1; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                            <?php
                            if ($profileImage) {
                                echo '<img src="../php/images/' . htmlspecialchars($profileImage) . '" alt="Profile Picture" style="height: 100%; width: 100%; object-fit: cover; border-radius: 50%;">';
                            } else {
                                echo '<i class="fas fa-user" style="font-size: 30px; color: #94827F;"></i>';
                            }
                            ?>
                        </div>
                        <div class="profile-name" style="margin-left: 20px;">
                            <h2><?php echo $firstName . ' ' . $lastName; ?></h2>
                        </div>
                    </div>
                    <div classs="title" style="margin-top: 5px;">
                        <h5>Tony Cox has been working in Hollywood a long time and deserves his flowers!</h5>
                    </div>
            <hr>
            <div class="interaction-button">
                                 <span><i class="fa fa-heart" aria-hidden="true"></i></span>
                                 <span><i class="fa fa-comment-dots" aria-hidden="true"></i></span>
                                 <span><i class="fa fa-share" aria-hidden="true"></i></span>
                        </div>
            <h3>Comments</h3>
            <div class="comments">
            <div>
                    </form>
                </div>
            </div>
            <div class="preview-box">
</div>

</div>

</body>
</html>