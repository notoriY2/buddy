<?php
session_start();
require 'php/config.php'; // Ensure this contains secure database connection code
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['studentNo'])) {
    header('Location: login.php');
    exit;
}

// Fetch user and profile data
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
    header('Location: logout.php');
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Handle group join requests
    if (isset($_POST['request_type']) && $_POST['request_type'] === 'group_join') {
        $groupId = $_POST['group_id'];
        $requestId = $_POST['request_id'];
        $action = $_POST['action'];

        if ($action === 'accept') {
            // Accept the group join request
            $updateRequestStmt = $conn->prepare("UPDATE group_join_request SET status = 'accepted', responded_at = NOW() WHERE request_id = ?");
            $updateRequestStmt->bind_param("i", $requestId);
            $updateRequestStmt->execute();

            // Get the student_id associated with this request
        $studentIdStmt = $conn->prepare("SELECT student_id FROM group_join_request WHERE request_id = ?");
        $studentIdStmt->bind_param("i", $requestId);
        $studentIdStmt->execute();
        $studentIdResult = $studentIdStmt->get_result();

        if ($studentIdResult->num_rows > 0) {
            $studentRow = $studentIdResult->fetch_assoc();
            $studentId = $studentRow['student_id'];

            // Check if the student is already a member of the group
            $membershipCheckStmt = $conn->prepare("SELECT * FROM group_membership WHERE group_id = ? AND student_id = ?");
            $membershipCheckStmt->bind_param("ii", $groupId, $studentId);
            $membershipCheckStmt->execute();
            $membershipResult = $membershipCheckStmt->get_result();

            if ($membershipResult->num_rows == 0) {
                // Add the student to the group
                $addMemberStmt = $conn->prepare("INSERT INTO group_membership (group_id, student_id) VALUES (?, ?)");
                $addMemberStmt->bind_param("ii", $groupId, $studentId);
                $addMemberStmt->execute();

                // Add notification for group join acceptance
                $notificationStmt = $conn->prepare("INSERT INTO notification (student_id, sender_id, type, message) VALUES (?, ?, 'group_accept', 'has accepted your join group request')");
                $notificationStmt->bind_param("ii", $studentId, $studentId);
                $notificationStmt->execute();
            }
        }
        } elseif ($action === 'decline') {
            // Decline the group join request
            $updateRequestStmt = $conn->prepare("UPDATE group_join_request SET status = 'declined', responded_at = NOW() WHERE request_id = ?");
            $updateRequestStmt->bind_param("i", $requestId);
            $updateRequestStmt->execute();
        }

        echo json_encode(['status' => 'success']);
        exit;
    }

    // Handle friend requests
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];
        $action = $_POST['action'];

        if ($action === 'accept') {
            // Update request status to 'accepted'
            $updateRequestStmt = $conn->prepare("UPDATE request SET status = 'accepted' WHERE from_student_id = ? AND to_student_id = ?");
            $updateRequestStmt->bind_param("ii", $userId, $studentId);
            $updateRequestStmt->execute();

            // Check if the update was successful
        if ($updateRequestStmt->affected_rows > 0) {
            // Insert the reciprocal request with swapped IDs
            $insertReciprocalStmt = $conn->prepare("INSERT INTO request (from_student_id, to_student_id, status) VALUES (?, ?, 'accepted')");
            $insertReciprocalStmt->bind_param("ii", $studentId, $userId); // Swap the IDs
            $insertReciprocalStmt->execute();

            // Add notification for friend request acceptance
            $notificationStmt = $conn->prepare("INSERT INTO notification (student_id, sender_id, type, message) VALUES (?, ?, 'friend_accept', 'has accepted your friend request')");
            $notificationStmt->bind_param("ii", $userId, $studentId); // Here, userId is the recipient of the notification
            $notificationStmt->execute();
        }
        } elseif ($action === 'decline') {
            // Delete the request
            $deleteRequestStmt = $conn->prepare("DELETE FROM request WHERE from_student_id = ? AND to_student_id = ?");
            $deleteRequestStmt->bind_param("ii", $userId, $studentId);
            $deleteRequestStmt->execute();
        }

        echo json_encode(['status' => 'success']);
        exit;
    }
}

// Fetch group join requests
$groupRequestsStmt = $conn->prepare("
    SELECT g.group_id, g.group_name, gjr.request_id, s.firstName, s.lastName 
    FROM group_join_request gjr 
    JOIN `group` g ON gjr.group_id = g.group_id 
    JOIN student s ON gjr.student_id = s.student_id 
    WHERE gjr.status = 'pending' AND g.group_id IN (
        SELECT group_id FROM group_membership WHERE student_id = ? AND role = 'admin'
    )
");
$groupRequestsStmt->bind_param("i", $studentId);
$groupRequestsStmt->execute();
$groupRequestsResult = $groupRequestsStmt->get_result();

// Handle post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['text_content'])) {
    $textContent = htmlspecialchars($_POST['text_content']);
    $image = '';
    
    // Handle file upload
    if (!empty($_FILES['feed-pic-upload']['name'])) {
        $imageFileType = strtolower(pathinfo($_FILES['feed-pic-upload']['name'], PATHINFO_EXTENSION));
        $check = getimagesize($_FILES['feed-pic-upload']['tmp_name']);
        if ($check !== false && in_array($imageFileType, ['jpg', 'jpeg', 'png']) && $_FILES['feed-pic-upload']['size'] < 5000000) {
            $uniqueFileName = uniqid() . '.' . $imageFileType;
            $targetFile = "php/images/" . $uniqueFileName;
            if (move_uploaded_file($_FILES['feed-pic-upload']['tmp_name'], $targetFile)) {
                $image = $uniqueFileName;
            } else {
                echo "Failed to upload image.";
            }
        } else {
            echo "Invalid file type or size.";
        }
    }

    // Insert post into database
    $stmt = $conn->prepare("INSERT INTO post (student_id, text_content, image) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $studentId, $textContent, $image);
    if ($stmt->execute()) {
        // Redirect to avoid form resubmission on refresh
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Delete expired stories
$stmt = $conn->prepare("DELETE FROM story WHERE expires_at <= NOW()");
$stmt->execute();

// Fetch the latest 3 friend requests
$requestsStmt = $conn->prepare("
    SELECT student.student_id, student.firstName, student.lastName, profile.image, 
    (SELECT COUNT(*) FROM friends WHERE (friends.student_id1 = student.student_id OR friends.student_id2 = student.student_id) AND status = 'accepted') AS mutual_friends 
    FROM request 
    JOIN student ON request.from_student_id = student.student_id 
    LEFT JOIN profile ON student.student_id = profile.student_id 
    WHERE request.to_student_id = ? AND request.status = 'pending' 
    LIMIT 3
");
$requestsStmt->bind_param("i", $studentId);
$requestsStmt->execute();
$requestsResult = $requestsStmt->get_result();

if (isset($_GET['post_id'])) {
    $postId = intval($_GET['post_id']);

    // Start transaction to ensure all queries succeed or none
    $conn->begin_transaction();

    try {
        // Delete associated likes
        $stmt = $conn->prepare("DELETE FROM `like` WHERE post_id = ?");
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $stmt->close();

        // Delete associated comments
        $stmt = $conn->prepare("DELETE FROM comment WHERE post_id = ?");
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $stmt->close();

        // Delete the post itself
        $stmt = $conn->prepare("DELETE FROM post WHERE post_id = ?");
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $stmt->close();

        // Commit the transaction
        $conn->commit();

        // Redirect to the previous page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
    } catch (Exception $e) {
        // Rollback transaction if something went wrong
        $conn->rollback();
        echo "Failed to delete the post. Please try again later.";
    }
}

// Fetch unread notifications for the logged-in student
$notifications_stmt = $conn->prepare("
    SELECT notification.notification_id, notification.message, notification.created_at, notification.is_read, profile.image, student.firstName, student.lastName
    FROM notification
    LEFT JOIN student ON notification.sender_id = student.student_id
    LEFT JOIN profile ON student.student_id = profile.student_id
    WHERE notification.student_id = ? AND notification.is_read = 0
    ORDER BY notification.created_at DESC
    LIMIT 10
");
$notifications_stmt->bind_param("i", $studentId);
$notifications_stmt->execute();
$notifications_result = $notifications_stmt->get_result();
$notifications_count = $notifications_result->num_rows;

// Function to convert datetime to time elapsed
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Calculate the number of weeks
    $weeks = floor($diff->d / 7);
    $diff->d -= $weeks * 7;

    $string = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];

    // Manually add the weeks to the result if applicable
    $timeComponents = [];
    if ($diff->y) {
        $timeComponents['y'] = $diff->y . ' ' . ($diff->y > 1 ? 'years' : 'year');
    }
    if ($diff->m) {
        $timeComponents['m'] = $diff->m . ' ' . ($diff->m > 1 ? 'months' : 'month');
    }
    if ($weeks) {
        $timeComponents['w'] = $weeks . ' ' . ($weeks > 1 ? 'weeks' : 'week');
    }
    if ($diff->d) {
        $timeComponents['d'] = $diff->d . ' ' . ($diff->d > 1 ? 'days' : 'day');
    }
    if ($diff->h) {
        $timeComponents['h'] = $diff->h . ' ' . ($diff->h > 1 ? 'hours' : 'hour');
    }
    if ($diff->i) {
        $timeComponents['i'] = $diff->i . ' ' . ($diff->i > 1 ? 'minutes' : 'minute');
    }
    if ($diff->s) {
        $timeComponents['s'] = $diff->s . ' ' . ($diff->s > 1 ? 'seconds' : 'second');
    }

    // Slice the array if $full is false
    if (!$full) {
        $timeComponents = array_slice($timeComponents, 0, 1);
    }

    return $timeComponents ? implode(', ', $timeComponents) . ' ago' : 'just now';
}


// Check if the student is blocked
$checkBlockStmt = $conn->prepare("SELECT * FROM block WHERE blocked_id = ?");
$checkBlockStmt->bind_param("i", $studentId);
$checkBlockStmt->execute();
$blockResult = $checkBlockStmt->get_result();

if ($blockResult->num_rows > 0) {
    // Student is blocked, hide <main> and show "Blocked" message
    echo '
    <div style="text-align: center; font-size: 3em; font-weight: bold; color: red; margin-top: 100px;">
        Blocked
    </div>
    <script>
        setTimeout(function() {
            window.location.href = "./index.php";
        }, 5000); // Redirect after 5 seconds
    </script>';
    exit; // Stop further page rendering
}
?>

<?php include_once "header.php"; ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width= , initial-scale=1.0" />
    <title>Buddy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    />
    <link rel="stylesheet" href="Student/styles.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <style>
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
.menu-item:hover {
    transform: scale(1.05); /* Slight zoom effect */
}
.menu-item span, .menu-item h3 {
    transition: color 0.3s ease;
}
.menu-item:hover span, .menu-item:hover h3 {
    color: #94827F; /* Change color on hover */
}
.add-story {
    cursor: pointer; /* Changes the cursor to a pointer when hovering over the label */
    display: flex; /* Align items inside the label */
    align-items: center; /* Center items vertically */
    padding: 10px; /* Add some padding */
    border-radius: 5px; /* Optional: Adds rounded corners */
    background-color: #f0f0f0; /* Optional: Adds a background color */
    transition: background-color 0.3s; /* Optional: Adds a smooth transition effect */
}

.add-story:hover {
    background: linear-gradient(135deg, #94827F, white); /* Optional: Changes background color on hover */
}

.add-story i {
    margin-right: 8px; /* Space between icon and text */
}

.add-story p {
    margin: 0; /* Remove default margin from p element */
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

.view_request {
    text-align: right; /* Align the content to the right */
    margin-top: 20px; /* Add some space above the div */
    margin-right: 20px; /* Add some space on the right side */
}

.view_request a {
    text-decoration: none; /* Remove the underline from the link */
    color: #94827F; /* Set the link color */
    font-size: 16px; /* Set the font size */
    font-weight: bold; /* Make the link text bold */
    padding: 10px 20px; /* Add padding around the link */
    border-radius: 5px; /* Add rounded corners to the link */
    transition: background-color 0.3s ease, color 0.3s ease; /* Add a transition effect */
}

.view_request a:hover {
    background-color: #94827F; /* Change the background color on hover */
    color: #ffffff; /* Change the text color on hover */
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

.story h3 {
    font-size: 36px; /* Adjust size as needed */
    font-weight: bold;
    margin: 0;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.image-container {
    position: relative;
    width: 100%;
    height: 100%;
}

.image-container img {
    width: 100%;
    height: auto;
    border-radius: 10px; /* Optional, for rounded corners */
}
/* Style the overlay text to be centered in the middle of the image */
.overlay-text {
    position: absolute;
    top: 50%; /* Position from the top */
    left: 50%; /* Position from the left */
    transform: translate(-50%, -50%); /* Center the text */
    color: white; /* White text */
    font-size: 18px;
    font-weight: bold;
    padding: 10px;
    border-radius: 5px;
    text-align: center;
    width: 80%; /* Optional: Adjust width */
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
      <?php include_once "Student/nav.php"; ?>
      <main>
        <div class="container">
          <div class="left">
          <a class="profile">
  <div class="handle">
    <h4><?php echo $firstName . " " . $lastName; ?></h4>
    <p class="text-muted"><?php echo $email; ?></p>
  </div>
</a>

            <?php include_once "Student/sidebar.php"; ?>
          </div>
          <div class="middle">
            <div class="middle-contains">
              <?php include_once "Student/stories.php"; ?>
              <?php include_once "Student/create_post_form.php"; ?>
              <?php include_once "Student/feeds.php"; ?>
            </div>
          </div>
          <div class="right">
            <?php include_once "Student/friend_requests.php"; ?>
          </div>
        </div>
      </main>

      <div class="popup messages-popup" id="messages">
        <div>
            <?php include_once "Student/messages.php"; ?>
        </div>
      </div>

      <!-- Add Post Form rsa@9795-->
<div class="popup add-post-popup">
    <div>
        <form class="popup-box add-post-form" method="POST" enctype="multipart/form-data">
            <h1>Add New Post</h1>
            <div class="row post-title">
                <label for="create-post">Title</label>
                <input type="text" name="text_content" placeholder="What's on your mind?..." id="create-post">
            </div>
            <div class="row post-img">
                <img src="" id="postImg">
                <label for="feed-pic-upload" class="feed-upload-button">
                    <span><i class="fa fa-add"></i></span>
                    Upload A Picture
                </label>
                <input type="file" accept="image/jpg, image/png, image/jpeg" id="feed-pic-upload" name="feed-pic-upload">
            </div>
            <input type="submit" class="btn btn-primary btn-lg" value="Add Post">
        </form>
        <span class="close"><i class="fa fa-close"></i></span>
    </div>
</div>
    
    
      <div class="customize-theme">
        <div class="card">
            <h2>Customize your view</h2>
            <p class="text-muted">Manage your font size, color, and background.</p>

            <div class="font-size">
                <div>
                    <h6>Aa</h6>
                    <div class="choose-size">
                        <span class="font-size-1"></span>
                        <span class="font-size-2 active"></span>
                        <span class="font-size-3"></span>
                        <span class="font-size-4"></span>
                        <span class="font-size-5"></span>
                    </div>
                    <h3>Aa</h3>
                </div>
            </div>

            <div class="color">
                <h4>Color</h4>
                <div class="choose-color">
                    <span class="color-1 active"></span>
                    <span class="color-2"></span>
                    <span class="color-3"></span>
                    <span class="color-4"></span>
                    <span class="color-5"></span>
                </div>
            </div>

            <div class="background">
                <h4>Background</h4>
                <div class="choose-bg">
                    <div class="bg-1" active>
                        <span></span>
                        <h5 for="bg-1">Light</h5>
                    </div>
                    <div class="bg-2">
                        <span></span>
                        <h5 for="bg-2">Dim</h5>
                    </div>
                    <div class="bg-3">
                        <span></span>
                        <h5 for="bg-3">Light Out</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="javascript/users.js"></script>
    <script src="javascript/chat.js"></script>
    
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

        let swiper = new Swiper(".mySwiper", {
        slidesPerView: 6,
        spaceBetween: 5,
        });

        window.addEventListener('scroll', () => {
            document.querySelector('.add-post-popup').style.display = 'none';
            document.querySelector('.messages-popup').style.display = 'none';
            document.querySelector('.notifications-popup').style.display = 'none'
            
        });

        document.querySelectorAll('.close').forEach(AllCloser => {
            AllCloser.addEventListener('click', () => {
                document.querySelector('.add-post-popup').style.display = 'none';
            })
        });

        document.querySelector('#create-lg').addEventListener('click', () => {
            document.querySelector('.add-post-popup').style.display = 'flex';
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

        document.addEventListener('DOMContentLoaded', function () {
          const messagesNotifications = document.getElementById('messages-notifications');

          messagesNotifications.addEventListener('click', function () {
              document.querySelector('.popup.messages-popup').style.display = 'block';
          });
        });
        //sidebar
        const menuItems = document.querySelectorAll('.menu-item');

        //theme
        const theme = document.querySelector('#theme');
        const themeModal = document.querySelector('.customize-theme');
        const fontSizes = document.querySelectorAll('.choose-size span');
        const root = document.querySelector(':root');
        const colorPalette = document.querySelectorAll('.choose-color span');
        const Bg1 = document.querySelector('.bg-1');
        const Bg2 = document.querySelector('.bg-2');
        const Bg3 = document.querySelector('.bg-3');

        //remove active class from all menu items
        const changeActiveItem = () => {
            menuItems.forEach(item => {
                item.classList.remove('active');
            })
        }


        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                changeActiveItem();
                item.classList.add('active');
    
                if (item.id != 'notifications') {
                    document.querySelector('.notifications-popup').style.display = 'none';
                } else {
                    document.querySelector('.notifications-popup').style.display = 'block';
                    document.querySelector('#notifications .notification-count').style.display='none';
                }
            })
        })

        //Theme/display customization
        //opens modal
        const openThemeModal = () => {
            themeModal.style.display = 'grid';
        }

        //close modal
        const closeThemeModal = (e) => {
            if(e.target.classList.contains('customize-theme')){
                themeModal.style.display = 'none';
            }
        }
        themeModal.addEventListener('click', closeThemeModal);
        theme.addEventListener('click', openThemeModal);

        //fonts
        //remove active class span or fonts selector
        const removeSizeSelector = () => {
            fontSizes.forEach(size => {
                size.classList.remove('active');
            })
        }

        fontSizes.forEach(size => {
            size.addEventListener('click', () => {
                removeSizeSelector();
                let fontSize;
                size.classList.toggle('active');

                if(size.classList.contains('font-size-1')){
                    fontSize = '10px';
                    root.style.setProperty('----sticky-top-left', '5.4rem');
                    root.style.setProperty('----sticky-top-right', '5.4rem');
                }else if(size.classList.contains('font-size-2')){
                    fontSize = '13px';
                    root.style.setProperty('----sticky-top-left', '5.4rem');
                    root.style.setProperty('----sticky-top-right', '-7rem');
                }else if(size.classList.contains('font-size-3')){
                    fontSize = '16px';
                    root.style.setProperty('----sticky-top-left', '-2rem');
                    root.style.setProperty('----sticky-top-right', '-17rem');
                }else if(size.classList.contains('font-size-4')){
                    fontSize = '19px';
                    root.style.setProperty('----sticky-top-left', '-5rem');
                    root.style.setProperty('----sticky-top-right', '-25rem');
                }else if(size.classList.contains('font-size-5')){
                    fontSize = '22px';
                    root.style.setProperty('----sticky-top-left', '-12rem');
                    root.style.setProperty('----sticky-top-right', '-35rem');
                }

                //change font size of the root html element
                document.querySelector('html').style.fontSize = fontSize;
            })
        })

        //remove active class from colors
        const changeActiveColorClass =() => {
            colorPalette.forEach(colorPicker => {
                colorPicker.classList.remove('active');
            })
        }
        //change primary colors
        colorPalette.forEach(color => {
            color.addEventListener('click', () => {
                let primary;

                //remove active class from colors
                changeActiveColorClass();

                if(color.classList.contains('color-1')){
                    primaryHue = 9;
                } else if(color.classList.contains('color-2')){
                    primaryHue = 52;
                }else if(color.classList.contains('color-3')){
                    primaryHue = 352;
                }else if(color.classList.contains('color-4')){
                    primaryHue = 152;
                }else if(color.classList.contains('color-5')){
                    primaryHue = 202;
                }

                color.classList.add('active');
                root.style.setProperty('--primary-color-hue', primaryHue)
            })
        })

        //theme background values
        let lightColorLightness;
        let whiteColorLightness;
        let darkColorLightness;

        //changes background color
        const changeBG = () => {
            root.style.setProperty('--light-color-lightness', lightColorLightness);
            root.style.setProperty('--white-color-lightness', whiteColorLightness);
            root.style.setProperty('--dark-color-lightness', darkColorLightness);
        }

        Bg1.addEventListener('click', () => {
            //add active class
            Bg1.classList.add('active');
            //remove active class from the others
            Bg2.classList.remove('active');
            Bg3.classList.remove('active');
            window.location.reload();
        });
        
        Bg2.addEventListener('click', () => {
            darkColorLightness = '95%';
            whiteColorLightness = '20%';
            lightColorLightness = '15%';

            //add active class
            Bg2.classList.add('active');
            //remove active class from the others
            Bg1.classList.remove('active');
            Bg3.classList.remove('active');
            changeBG();
        });

        Bg3.addEventListener('click', () => {
            darkColorLightness = '95%';
            whiteColorLightness = '10%';
            lightColorLightness = '0%';

            //add active class
            Bg3.classList.add('active');
            //remove active class from the others
            Bg1.classList.remove('active');
            Bg2.classList.remove('active');
            changeBG();
        })

        
    </script>
    
    
  </body>
  
</html>