<?php
include('../php/config.php');
include('../php/session.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the staffId is set in the session
if (!isset($_SESSION['staffId'])) {
    // If staffId is not set, redirect to the login page or show an error message
    header('Location: ../login_form.php'); // Redirect to login page
    exit(); // Stop further execution
}

// Assuming the staffId of the logged-in lecturer is stored in a session variable
$staffId = $_SESSION['staffId'];

// Query to get the number of students assigned to the courses the staff is teaching
$queryStudents = "
    SELECT COUNT(DISTINCT attendance.studentNo) AS total_students
    FROM attendance
    JOIN assignedlecture ON attendance.course_id = assignedlecture.course_id
    WHERE assignedlecture.staff_id = ?";
$stmtStudents = $conn->prepare($queryStudents);
$stmtStudents->bind_param("i", $staffId);
$stmtStudents->execute();
$resultStudents = $stmtStudents->get_result();
$totalStudents = $resultStudents->fetch_assoc()['total_students'];

// Query to get the number of courses the staff is assigned to
$queryCourses = "
    SELECT COUNT(DISTINCT assignedlecture.course_id) AS total_courses
    FROM assignedlecture
    WHERE assignedlecture.staff_id = ?";
$stmtCourses = $conn->prepare($queryCourses);
$stmtCourses->bind_param("i", $staffId);
$stmtCourses->execute();
$resultCourses = $stmtCourses->get_result();
$totalCourses = $resultCourses->fetch_assoc()['total_courses'];

// Query to get the number of departments the staff is associated with
$queryDepartments = "
    SELECT COUNT(DISTINCT assignedlecture.department_id) AS total_departments
    FROM assignedlecture
    WHERE assignedlecture.staff_id = ?";
$stmtDepartments = $conn->prepare($queryDepartments);
$stmtDepartments->bind_param("i", $staffId);
$stmtDepartments->execute();
$resultDepartments = $stmtDepartments->get_result();
$totalDepartments = $resultDepartments->fetch_assoc()['total_departments'];

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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
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
                <li>
                    <a href="viewFaculty.php">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span class="link-name">Faculty</span>
                    </a>
                </li>
                <li>
                    <a href="viewDepartment.php">
                        <i class="fas fa-building"></i>
                        <span class="link-name">Departments</span>
                    </a>
                </li>
                <li>
                    <a href="viewCourses.php">
                        <i class="fas fa-book-open"></i>
                        <span class="link-name">Courses</span>
                    </a>
                </li>
                <li>
                    <a href="viewStudent.php">
                        <i class="fas fa-users"></i>
                        <span class="link-name">Students</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="toggle-submenu" data-submenu="attendance-submenu">
                        <i class="fas fa-clipboard-list"></i>
                        <span class="link-name">Attendance</span>
                        <i class="fas fa-chevron-right toggle-icon"></i>
                    </a>
                    <ul class="attendance-submenu submenu" style="display: none;">
                        <li><a href="takeAttendance.php">Take Attendance</a></li>
                        <li><a href="viewAttendance.php">Class Attendance</a></li>
                        <li><a href="viewStudentAttendance.php">Student Attendance</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="toggle-submenu" data-submenu="results-submenu">
                        <i class="fas fa-graduation-cap"></i>
                        <span class="link-name">Results</span>
                        <i class="fas fa-chevron-right toggle-icon"></i>
                    </a>
                    <ul class="results-submenu submenu" style="display: none;">
                        <li><a href="computeGPAResults.php">GPA</a></li>
                        <li><a href="computeCGPAResults.php">CGPA</a></li>
                        <li><a href="SemesterResults.php">Semester Results</a></li>
                    </ul>
                </li>
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
        <span class="text">Total Students</span>
        <span class="number"><?php echo $totalStudents; ?></span>
    </div>
    <div class="box box2">
        <i class="fas fa-book-open"></i>
        <span class="text">Courses</span>
        <span class="number"><?php echo $totalCourses; ?></span>
    </div>
    <div class="box box3">
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

$(document).ready(function() {
        $('#bootstrap-data-table').DataTable({
            "pageLength": 10,
            "lengthMenu": [10, 20, 50, -1],
            "pagingType": "full_numbers",
            "searching": true,
            "info": true
        });
    });
    </script>
</body>
</html>