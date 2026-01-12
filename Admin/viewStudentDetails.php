<?php
// Include necessary configuration and session files
include('../php/config.php');
include('../php/session.php');

// Check if the staffId is set in the session
if (!isset($_SESSION['staffId'])) {
    // If staffId is not set, redirect to the login page or show an error message
    header('Location: ../login_form.php'); // Redirect to login page
    exit(); // Stop further execution
}

// Assuming the staffId of the logged-in lecturer is stored in a session variable
$staff_id = $_SESSION['staffId'];
if (isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);

    // Fetch student details
    $studentQuery = "SELECT firstName, lastName FROM student WHERE student_id = $student_id";
    $studentResult = mysqli_query($conn, $studentQuery);
    $student = mysqli_fetch_assoc($studentResult);

    // Fetch stories
    $storiesQuery = "SELECT * FROM story WHERE student_id = $student_id";
    $storiesResult = mysqli_query($conn, $storiesQuery);

    // Fetch posts
    $postsQuery = "SELECT * FROM post WHERE student_id = $student_id";
    $postsResult = mysqli_query($conn, $postsQuery);

    // Fetch groups
    $groupsQuery = "SELECT * FROM `group` WHERE group_id IN (SELECT group_id FROM post WHERE student_id = $student_id)";
    $groupsResult = mysqli_query($conn, $groupsQuery);

    // Fetch pages
    $pagesQuery = "SELECT * FROM page WHERE student_id = $student_id";
    $pagesResult = mysqli_query($conn, $pagesQuery);
} else {
    echo "No student selected.";
    exit;
}

// Handle blocking and unblocking
$blocker_id = $staff_id; // Set blocker_id to the logged-in staff_id
$blocked_id = $student_id;

if (isset($_POST['block'])) {
    // Check if already blocked
    $checkQuery = "SELECT * FROM block WHERE blocker_id = ? AND blocked_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('ii', $blocker_id, $blocked_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Not blocked, so insert into block table
        $insertQuery = "INSERT INTO block (blocker_id, blocked_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param('ii', $blocker_id, $blocked_id);
        $stmt->execute();
        $action = 'blocked';
    } else {
        // Already blocked, so delete from block table
        $deleteQuery = "DELETE FROM block WHERE blocker_id = ? AND blocked_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param('ii', $blocker_id, $blocked_id);
        $stmt->execute();
        $action = 'unblocked';
    }
}

// Fetch the staff image
$queryStaffImage = "SELECT image FROM staff WHERE staff_id = ?";
$stmt = $conn->prepare($queryStaffImage);
$stmt->bind_param('i', $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staffData = $result->fetch_assoc();

// Set the path for the profile image
$profileImagePath = '../php/images/' . ($staffData['image'] ?? 'default.png');

// Check if the student is blocked
$checkBlockStatus = "SELECT * FROM block WHERE blocker_id = ? AND blocked_id = ?";
$stmt = $conn->prepare($checkBlockStatus);
$stmt->bind_param('ii', $blocker_id, $blocked_id);
$stmt->execute();
$blockResult = $stmt->get_result();
$isBlocked = $blockResult->num_rows > 0;

?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" type="Buddy-icon" href="../images/12.png">
    <title>Buddy</title>
    <meta name="description" content="Buddy Admin">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <style>
        /* styles.css */
.card-header h2 {
    color: #94827F; /* Customize the color as needed */
    font-size: 1.5rem; /* Adjust font size if necessary */
    font-weight: bold; /* Make the text bold */
    margin: 0; /* Remove default margin */
}

/* styles.css */
.card-header h2 {
            color: #94827F; /* Customize the color as needed */
            font-size: 1.5rem; /* Adjust font size if necessary */
            font-weight: bold; /* Make the text bold */
            margin: 0; /* Remove default margin */
        }
        .btn-block {
            background-color: red; /* Red color for block button */
            color: white;
        }
        .btn-unblock {
            background-color: green; /* Green color for unblock button */
            color: white;
        }
        </style>
</head>
<body>
<nav>
        <div class="logo-name">
            <div class="logo-image">
                <img src="../images/12.png" alt="">
            </div>

            <span class="logo_name">Buddy</span>
        </div>

        <div class="menu-items">
            <ul class="nav-links">
                <li>
                    <a href="Dashboard.php">
                        <i class="fas fa-home"></i>
                        <span class="link-name">Dashboard</span>
                    </a>
                </li>
                <li><a href="createSession.php">
                    <i class="fas fa-calendar-plus"></i>
                    <span class="link-name">Session</span>
                </a></li>
                <li>
                    <a href="createFaculty.php">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span class="link-name">Faculty</span>
                    </a>
                </li>
                <li>
                    <a href="createDepartment.php">
                        <i class="fas fa-building"></i>
                        <span class="link-name">Departments</span>
                    </a>
                </li>
                <li>
                    <a href="createCourses.php">
                        <i class="fas fa-book-open"></i>
                        <span class="link-name">Courses</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="toggle-submenu" data-submenu="lecture-submenu">
                        <i class="fas fa-user-tie"></i>
                        <span class="link-name">Lectures</span>
                        <i class="fas fa-chevron-right toggle-icon"></i>
                    </a>
                    <ul class="lecture-submenu submenu" style="display: none;">
                        <li><a href="createLectures.php">Create Lecture</a></li>
                        <li><a href="assignLecture.php">Assign Lecture</a></li>
                        <li><a href="viewUnassignedLecture.php">Unassigned Lecturers</a></li>
                        <li><a href="allLectures.php">All Lecturers</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="toggle-submenu" data-submenu="student-submenu">
                        <i class="fas fa-users"></i>
                        <span class="link-name">Students</span>
                        <i class="fas fa-chevron-right toggle-icon"></i>
                    </a>
                    <ul class="student-submenu submenu" style="display: none;">
                        <li><a href="createStudent.php">Add Student</a></li>
                        <li><a href="viewStudentInDept.php">View Student</a></li>
                    </ul>
                </li>
                <li><a href="socials.php" class="active">
                    <i class="fas fa-at"></i>
                    <span class="link-name">Socials</span>
                </a></li>
            </ul>
            <ul class="logout-mode">
                <li>
                    <a href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="link-name">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <section class="dashboard">
        <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>

            <div class="search-box">
                <i class="uil uil-search"></i>
                <input type="text" placeholder="Search here...">
            </div>
            
            <a href="changePassword.php">
            <img src="<?php echo htmlspecialchars($profileImagePath); ?>" alt="Change Password">
        </a>
        </div>

        <div class="dash-content">
            <div class="animated fadeIn">
            <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h2>
                            <?php
                            if ($student) {
                                echo $student['firstName'] . ' ' . $student['lastName'];
                            } else {
                                echo "Student";
                            }
                            ?>'s Details
                        </h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong class="card-title"><h3 align="center">Stories</h3></strong>
            </div>
            <div class="card-body">
                <table id="bootstrap-data-table" class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Date Created</th>
                            <th>Expires At</th>
                            <th>Action</th> <!-- New column for actions -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cnt = 1;
                        while ($row = mysqli_fetch_assoc($storiesResult)) {
                            echo "<tr>
                                <td>{$cnt}</td>
                                <td><img src='../php/images/{$row['image']}' width='50'></td>
                                <td>{$row['created_at']}</td>
                                <td>{$row['expires_at']}</td>
                                <td><a href='delete_story.php?id={$row['story_id']}' class='action-btn' title='Delete'><i class='fas fa-trash'></i></a></td> <!-- Action link -->
                            </tr>";
                            $cnt++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong class="card-title"><h3 align="center">Posts</h3></strong>
            </div>
            <div class="card-body">
                <table id="bootstrap-data-table" class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Text Content</th>
                            <th>Image</th>
                            <th>Date Created</th>
                            <th>Action</th> <!-- New column for actions -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cnt = 1;
                        while ($row = mysqli_fetch_assoc($postsResult)) {
                            echo "<tr>
                                <td>{$cnt}</td>
                                <td>{$row['text_content']}</td>
                                <td><img src='../php/images/{$row['image']}' width='50'></td>
                                <td>{$row['created_at']}</td>
                                <td><a href='delete_post.php?id={$row['post_id']}' class='action-btn' title='Delete'><i class='fas fa-trash'></i></a></td> <!-- Action link -->
                            </tr>";
                            $cnt++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong class="card-title"><h3 align="center">Groups</h3></strong>
            </div>
            <div class="card-body">
                <table id="bootstrap-data-table" class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Group Name</th>
                            <th>Description</th>
                            <th>Created At</th>
                            <th>Action</th> <!-- New column for actions -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cnt = 1;
                        while ($row = mysqli_fetch_assoc($groupsResult)) {
                            echo "<tr>
                                <td>{$cnt}</td>
                                <td>{$row['group_name']}</td>
                                <td>{$row['group_description']}</td>
                                <td>{$row['created_at']}</td>
                                <td><a href='delete_group.php?id={$row['group_id']}' class='action-btn' title='Delete'><i class='fas fa-trash'></i></a></td> <!-- Action link -->
                            </tr>";
                            $cnt++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong class="card-title"><h3 align="center">Pages</h3></strong>
            </div>
            <div class="card-body">
                <table id="bootstrap-data-table" class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Date Created</th>
                            <th>Action</th> <!-- New column for actions -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cnt = 1;
                        while ($row = mysqli_fetch_assoc($pagesResult)) {
                            echo "<tr>
                                <td>{$cnt}</td>
                                <td>{$row['page_title']}</td>
                                <td>{$row['created_at']}</td>
                                <td><a href='delete_page.php?id={$row['page_id']}' class='action-btn' title='Delete'><i class='fas fa-trash'></i></a></td> <!-- Action link -->
                            </tr>";
                            $cnt++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                    <form method="POST">
                                <button type="submit" name="block" class="btn <?php echo $isBlocked ? 'btn-unblock' : 'btn-block'; ?>">
                                    <?php echo $isBlocked ? 'Unblock' : 'Block'; ?>
                                </button>
                            </form>
                    <button type="button" name="submit" class="btn btn-primary" style="margin-top: 10px;" onclick="window.location.href='socials.php';">Back</button>
                    </div>
                </div>
            </div>
        </div>
            </div><!-- .animated -->
        </div>
    </section>
</body>
</html>