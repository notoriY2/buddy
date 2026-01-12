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
  <!doctype html>
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Ela Admin - HTML5 Admin Template">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="Buddy-icon" href="../images/12.png">
    <title>Buddy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
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
                    <a href="viewCourses.php" class="active">
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
            <div class="animated fadeIn">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong class="card-title"><h2 align="center">All My Courses</h2></strong>
                            </div>
                            <div class="card-body">
                                <table id="bootstrap-data-table" class="table table-hover table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Title</th>
                                            <th>Code</th>
                                            <th>Unit</th>
                                            <th>Level</th>
                                            <th>Faculty</th>
                                            <th>Department</th>
                                            <th>Semester</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
// Display courses in the table
$query = mysqli_query($conn, "
    SELECT DISTINCT
        course.course_id, 
        course.courseTitle, 
        course.courseCode, 
        course.courseUnit, 
        level.levelName, 
        semester.semesterName, 
        faculty.facultyName, 
        department.departmentName
    FROM course
    INNER JOIN assignedlecture ON course.course_id = assignedlecture.course_id 
    INNER JOIN course_assignment ON course.course_id = course_assignment.course_id
    INNER JOIN level ON course.level_id = level.level_id
    INNER JOIN semester ON course.semester_id = semester.semester_id
    INNER JOIN faculty ON course_assignment.faculty_id = faculty.faculty_id
    INNER JOIN department ON course_assignment.department_id = department.department_id
    WHERE assignedlecture.staff_id = '$staffId'
");

$cnt = 1;
while ($row = mysqli_fetch_array($query)) {
?>
<tr>
    <td><?php echo $cnt; ?></td>
    <td><?php echo htmlspecialchars($row['courseTitle']); ?></td>
    <td><?php echo htmlspecialchars($row['courseCode']); ?></td>
    <td><?php echo htmlspecialchars($row['courseUnit']); ?></td>
    <td><?php echo htmlspecialchars($row['levelName']); ?></td>
    <td><?php echo htmlspecialchars($row['facultyName']); ?></td>
    <td><?php echo htmlspecialchars($row['departmentName']); ?></td>
    <td><?php echo htmlspecialchars($row['semesterName']); ?></td>
</tr>
<?php 
    $cnt++;
} 
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- end of datatable -->
                </div>
            </div><!-- .animated -->
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