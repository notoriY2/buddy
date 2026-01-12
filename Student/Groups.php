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
    
    // Redirect to profile setup if the profile image is not set
    if (is_null($profileImage)) {
        header('Location: profile.php');
        exit;
    }
} else {
    // If no user data is found, force logout
    header('Location: ../logout.php');
    exit;
}

$stmt->close();

// Handle group creation form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['group_name'], $_POST['group_description'])) {
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
                
                if (move_uploaded_file($imageTmpName, $imageDestination)) {
                    // Insert group into the database
                    $stmt = $conn->prepare("INSERT INTO `group` (group_name, group_description, group_image) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $groupName, $groupDescription, $imageNewName);
                    if ($stmt->execute()) {
                        $groupId = $stmt->insert_id;

                        // Add the creator as an admin of the group
                        $stmt = $conn->prepare("INSERT INTO group_membership (group_id, student_id, role) VALUES (?, ?, 'admin')");
                        $stmt->bind_param("ii", $groupId, $studentId);
                        $stmt->execute();
                        $stmt->close();

                        // Redirect to groups page or show success message
                        header('Location: groups.php?success=1');
                        exit;
                    } else {
                        $error = "Failed to create the group.";
                    }
                } else {
                    $error = "Failed to upload the image.";
                }
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

// Handle group join request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['group_id'], $_POST['action']) && $_POST['action'] === 'join') {
    $groupId = intval($_POST['group_id']);
    
    // Check if the student has already requested to join this group
    $checkStmt = $conn->prepare("SELECT * FROM group_join_request WHERE group_id = ? AND student_id = ? AND status = 'pending'");
    $checkStmt->bind_param("ii", $groupId, $studentId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        // Insert a new join request
        $insertStmt = $conn->prepare("INSERT INTO group_join_request (group_id, student_id, status) VALUES (?, ?, 'pending')");
        $insertStmt->bind_param("ii", $groupId, $studentId);
        $insertStmt->execute();
        
        if ($insertStmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Join request sent.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send join request.']);
        }
        $insertStmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'You have already requested to join this group.']);
    }
    
    $checkStmt->close();
    exit;
}

// Fetch groups from the database
$groups = $conn->query("SELECT * FROM `group` ORDER BY created_at DESC");

// Fetch groups from the database with membership status
$stmt = $conn->prepare("
    SELECT `group`.group_id, group_name, group_image, 
           (SELECT COUNT(*) FROM group_membership WHERE group_id = `group`.group_id AND student_id = ? AND role = 'member') AS is_member,
           (SELECT COUNT(*) FROM group_join_request WHERE group_id = `group`.group_id AND student_id = ? AND status = 'pending') AS is_pending
    FROM `group`
    ORDER BY created_at DESC
");
$stmt->bind_param("ii", $studentId, $studentId);
$stmt->execute();
$groupsResult = $stmt->get_result();


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
/* General Button Styling */
button.view-group {
    background-color: #94827F; /* Green background */
    color: white; /* White text */
    border: none; /* Remove border */
    padding: 10px 20px; /* Padding for the button */
    text-align: center; /* Center text */
    text-decoration: none; /* Remove underline */
    display: inline-block; /* Inline-block for layout */
    font-size: 16px; /* Font size */
    margin: 10px 2px; /* Margin around the button */
    cursor: pointer; /* Pointer cursor on hover */
    border-radius: 4px; /* Rounded corners */
    transition: background-color 0.3s ease; /* Smooth background color transition */
}

/* Hover Effect */
button.view-group:hover {
    background-color: #A99B99; /* Darker background color on hover */
    transform: scale(1.1); /* Slightly enlarge the button */
}

/* Active/Pressed State */
button.view-group:active {
    box-shadow: 0 5px #666; /* Create a shadow effect */
    transform: translateY(4px); /* Push button down slightly */
}

/* Disabled State */
button.view-group:disabled {
    background-color: #888; /* Gray background for disabled state */
    cursor: not-allowed; /* Not-allowed cursor for disabled state */
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
            <a href="../users.php" class="back">
    <i class="fas fa-times"></i>
</a>
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
            <div class="gps">
    <?php
    // Fetch all groups
    $stmt = $conn->query("SELECT group_id, group_name, group_image FROM `group` ORDER BY created_at DESC");

    while ($group = $stmt->fetch_assoc()) {
        $groupId = htmlspecialchars($group['group_id']);
        $groupName = htmlspecialchars($group['group_name']);
        $groupImage = htmlspecialchars($group['group_image']);
        
        // Check if the logged-in student is already a member of this group
        $membershipStmt = $conn->prepare("SELECT * FROM group_membership WHERE group_id = ? AND student_id = ?");
        $membershipStmt->bind_param("ii", $groupId, $studentId);
        $membershipStmt->execute();
        $membershipResult = $membershipStmt->get_result();

        $isMember = $membershipResult->num_rows > 0;

        // Check if there is a pending join request for this group
        $pendingStmt = $conn->prepare("SELECT * FROM group_join_request WHERE group_id = ? AND student_id = ? AND status = 'pending'");
        $pendingStmt->bind_param("ii", $groupId, $studentId);
        $pendingStmt->execute();
        $pendingResult = $pendingStmt->get_result();

        $isPending = $pendingResult->num_rows > 0;
        ?>
        <div class="gp">
            <div class="gp-img">
                <img src="../php/images/<?= $groupImage ?>" alt="<?= $groupName ?>">
            </div>
            <div class="gp-details">
                <span><?= $groupName ?></span>
                <?php if ($isMember): ?>
                    <!-- Show the 'View Group' button if the student is already a member -->
                    <form action="GroupProfile.php" method="get">
    <input type="hidden" name="group_id" value="<?= $groupId ?>">
    <button type="submit" class="view-group">View Group</button>
</form>
<?php elseif ($isPending): ?>
                    <!-- Show 'Sent' button if there is a pending request -->
                    <button type="button" class="join-button" disabled>Sent</button>
                <?php else: ?>
                    <!-- Show 'Join' button if not a member and no pending request -->
                    <form action="join_group.php" method="post">
                        <input type="hidden" name="group_id" value="<?= $groupId ?>">
                        <button type="button" class="join-button" data-group-id="<?= $groupId ?>">Join</button>
                    </form>
                <?php endif; ?>
            </div>
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
document.querySelectorAll('.join-button').forEach(button => {
    // Check local storage to set the initial state
    const groupId = button.getAttribute('data-group-id');
    if (localStorage.getItem(`group_${groupId}_joined`) === 'true') {
        button.disabled = true;
        button.textContent = 'Sent';
    }

    button.addEventListener('click', function() {
        fetch('groups.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                'group_id': groupId,
                'action': 'join'
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); // Show feedback to the user
            if (data.status === 'success') {
                button.disabled = true; // Disable the button to prevent re-joining
                button.textContent = 'Sent';
                localStorage.setItem(`group_${groupId}_joined`, 'true');
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
</script>
</body>
</html>