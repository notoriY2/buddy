<?php
include('../php/config.php');
include('../php/session.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the staffId is set in the session
if (!isset($_SESSION['staffId'])) {
    header('Location: ../login_form.php');
    exit();
}

$staffId = $_SESSION['staffId'];

// Query to get the total number of students
$queryTotalStudents = "SELECT COUNT(*) AS total_students FROM student";
$resultTotalStudents = $conn->query($queryTotalStudents);
$totalStudents = $resultTotalStudents->fetch_assoc()['total_students'];

// Query to get the total number of staff
$queryTotalStaff = "SELECT COUNT(*) AS total_staff FROM staff";
$resultTotalStaff = $conn->query($queryTotalStaff);
$totalStaff = $resultTotalStaff->fetch_assoc()['total_staff'];

// Query to get the total number of courses
$queryTotalCourses = "SELECT COUNT(*) AS total_courses FROM course";
$resultTotalCourses = $conn->query($queryTotalCourses);
$totalCourses = $resultTotalCourses->fetch_assoc()['total_courses'];

// Query to get the total number of departments
$queryTotalDepartments = "SELECT COUNT(*) AS total_departments FROM department";
$resultTotalDepartments = $conn->query($queryTotalDepartments);
$totalDepartments = $resultTotalDepartments->fetch_assoc()['total_departments'];

// Fetch the staff image
$queryStaffImage = "SELECT image FROM staff WHERE staff_id = ?";
$stmt = $conn->prepare($queryStaffImage);
$stmt->bind_param('i', $staffId);
$stmt->execute();
$result = $stmt->get_result();
$staffData = $result->fetch_assoc();

// Set the path for the profile image
$profileImagePath = '../php/images/' . ($staffData['image'] ?? 'default.png');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!----======== CSS ======== -->
    <link rel="stylesheet" href="../css/style.css">
     
    <!----===== Iconscout CSS ===== -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous"/>

    <link rel="shortcut icon" type="Buddy-icon" href="../images/12.png">
    <title>Buddy</title> 
    <style>
        .activity {
    width: 82vw; /* Full width of the viewport */
    border-radius: 15px; /* Adjust the border radius as needed */
    overflow: hidden; /* Ensures that the border radius is applied to the content */
    background-color: #f0f0f0; /* Optional: Add a background color for visibility */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Optional: Add shadow for a subtle effect */
    padding: 10px; /* Optional: Add padding for spacing */
    margin-top:10px;
}

.activity-data {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%; /* Ensures the content is centered vertically */
}

.activity img {
    width: 100%; /* Make the image responsive to the container's width */
    height: auto; /* Maintain the aspect ratio of the image */
    border-radius: 15px; /* Apply the same border radius to the image if needed */
}
.boxes {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin: 20px 0;
}

.box {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center;
    width: 30%; /* Adjusted to fit three boxes per row */
    margin: 10px 0; /* Margin adjusted to give space between rows */
}

.box i {
    font-size: 2rem;
    color: #5a5a5a;
    margin-bottom: 10px;
}

.box .text {
    font-size: 1.2rem;
    color: #5a5a5a;
    margin-bottom: 5px;
}

.box .number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
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
                    <a href="Dashboard.php" class="active">
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
                        <li><a href="viewStudentInDept.php">View Students</a></li>
                    </ul>
                </li>
                <li><a href="socials.php">
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
            <div class="overview">
                <div class="title">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="text">Dashboard</span>
                </div>

                <div class="boxes">
    <div class="box box1">
        <i class="fas fa-users"></i>
        <span class="text">Total Users</span>
        <span class="number"><?php echo $totalStudents + $totalStaff; ?></span>
    </div>
    <div class="box box2">
        <i class="fas fa-user-graduate"></i>
        <span class="text">Students</span>
        <span class="number"><?php echo $totalStudents; ?></span>
    </div>
    <div class="box box3">
        <i class="fas fa-chalkboard-teacher"></i>
        <span class="text">Staff</span>
        <span class="number"><?php echo $totalStaff; ?></span>
    </div>
    <div class="box box3">
    <i class="fas fa-book-open"></i>
        <span class="text">Courses</span>
        <span class="number"><?php echo $totalCourses; ?></span>
    </div>
    <div class="box box5">
        <i class="fas fa-building"></i>
        <span class="text">Departments</span>
        <span class="number"><?php echo $totalDepartments; ?></span>
    </div>
</div>

            </div>

            
            <div class="activity">
                <div class="activity-data">
                <img src="../images/26.png">
                </div>
            </div>
        </div>
    </section>

    <!-- Scripts -->
    <script src="../js/script.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
          $('#bootstrap-data-table-export').DataTable();
      } );
  </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
    const submenuToggles = document.querySelectorAll('.toggle-submenu');
    
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            const submenu = document.querySelector(`.${this.dataset.submenu}`);
            const icon = this.querySelector('.toggle-icon');
            if (submenu) {
                const isVisible = submenu.style.display === 'block';
                submenu.style.display = isVisible ? 'none' : 'block';
                if (icon) {
                    icon.classList.toggle('fa-chevron-right', isVisible);
                    icon.classList.toggle('fa-chevron-down', !isVisible);
                }
            }
        });
    });
});

    </script>
</body>
</html>