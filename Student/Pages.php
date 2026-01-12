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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture form data
    if (isset($_POST['page_title'], $_POST['page_description'])) {
        $pageTitle = $_POST['page_title'];
        $pageDescription = $_POST['page_description'];

        // Handle image upload
        $imageFileName = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $imageFileName = basename($_FILES['image']['name']);
            $targetFilePath = '../php/images/' . $imageFileName;
            move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath);
        }

        // Insert data into the page table
        $insertStmt = $conn->prepare("INSERT INTO page (page_title, page_description, image, student_id) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("sssi", $pageTitle, $pageDescription, $imageFileName, $studentId);

        if ($insertStmt->execute()) {
            // Redirect to avoid form resubmission
            header('Location: pages.php?status=success');
            exit;
        } else {
            echo "Error: " . $insertStmt->error;
        }
    } elseif (isset($_POST['page_id'], $_POST['action'])) {
        // Handle follow/unfollow actions
        $pageId = intval($_POST['page_id']);
        $action = $_POST['action'];

        if ($action === 'follow') {
            $checkStmt = $conn->prepare("SELECT * FROM page_followers WHERE page_id = ? AND follower_id = ?");
            $checkStmt->bind_param("ii", $pageId, $studentId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            if ($result->num_rows === 0) {
                $insertStmt = $conn->prepare("INSERT INTO page_followers (page_id, follower_id) VALUES (?, ?)");
                $insertStmt->bind_param("ii", $pageId, $studentId);
                if ($insertStmt->execute()) {
                    header('Location: pages.php?status=followed');
                    exit;
                } else {
                    echo "Error: " . $insertStmt->error;
                }
            } else {
                echo "You are already following this page.";
            }
        } elseif ($action === 'unfollow') {
            $deleteStmt = $conn->prepare("DELETE FROM page_followers WHERE page_id = ? AND follower_id = ?");
            $deleteStmt->bind_param("ii", $pageId, $studentId);
            if ($deleteStmt->execute()) {
                header('Location: pages.php?status=unfollowed');
                exit;
            } else {
                echo "Error: " . $deleteStmt->error;
            }
        }
    }
}

// Fetch the first 5 pages from the page table
$pageStmt = $conn->prepare("SELECT page_id, page_title, image FROM page ORDER BY created_at DESC");
$pageStmt->execute();
$pageResult = $pageStmt->get_result();
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
                    <h3>Create a Page</h3>
                    <hr>
                    <form class="create-group-form" action="" method="post" enctype="multipart/form-data">
        <label for="page-title">Page Title:</label>
        <input type="text" id="page-title" name="page_title" required>

        <label for="page-description">Page Description:</label>
        <input type="text" id="page-description" name="page_description" required>
        
        <label for="page-picture">Profile Picture:</label>
        <input type="file" id="page-picture" name="image" accept="image/*" required>
        
        <button type="submit">Create Page</button>
    </form>
                </div>
                <div class="profile-intro">
                    <div class="title-box">
                        <h3>Pages</h3>
                        <a href="#">All Pages</a>
                    </div>
                    <hr>
                    <div class="groups">
    <?php
    if ($pageResult->num_rows > 0) {
        while ($pageRow = $pageResult->fetch_assoc()) {
            $pageTitle = htmlspecialchars($pageRow['page_title']);
            $pageImage = htmlspecialchars($pageRow['image']);
            echo '
            <div class="group-item">
                <img src="../php/images/' . $pageImage . '" alt="' . $pageTitle . '">
                <div class="name-verification">
                    <span>' . $pageTitle . '</span>
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>';
        }
    } else {
        echo '<p>No pages available.</p>';
    }
    ?>
</div>
                </div>
            </div>
            <div class="post-col">
            <div class="gps">
                    <?php
                    // Fetch all pages from the database
                    $stmt = $conn->query("SELECT page_id, page_title, image, student_id FROM page ORDER BY created_at DESC");

                    while ($page = $stmt->fetch_assoc()) {
                        $pageId = htmlspecialchars($page['page_id']);
                        $pageTitle = htmlspecialchars($page['page_title']);
                        $pageImage = htmlspecialchars($page['image']);
                        $pageOwnerId = $page['student_id'];
                        $isOwner = $pageOwnerId == $studentId;

                        // Check follow status
                        $followStmt = $conn->prepare("SELECT * FROM page_followers WHERE page_id = ? AND follower_id = ?");
                        $followStmt->bind_param("ii", $pageId, $studentId);
                        $followStmt->execute();
                        $isFollowing = $followStmt->get_result()->num_rows > 0;
                        ?>
                        <div class="gp">
                            <div class="gp-img">
                                <img src="../php/images/<?= $pageImage ?>" alt="<?= $pageTitle ?>">
                            </div>
                            <div class="gp-details">
                                <span><?= $pageTitle ?></span>
                                <!-- View Page button -->
                                <form action="PageProfile.php" method="get">
                                    <input type="hidden" name="page_id" value="<?= $pageId ?>">
                                    <button type="submit" class="view-page">View Page</button>
                                </form>
                                <!-- Follow/Unfollow Form -->
                                <?php if (!$isOwner): ?>
                                    <form action="" method="post" style="margin-top:10px">
                                        <input type="hidden" name="page_id" value="<?php echo $pageId; ?>">
                                        <button type="submit" name="action" value="<?php echo $isFollowing ? 'unfollow' : 'follow'; ?>">
                                            <?php echo $isFollowing ? 'Unfollow' : 'Follow'; ?>
                                        </button>
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
    </script>
</body>
</html>