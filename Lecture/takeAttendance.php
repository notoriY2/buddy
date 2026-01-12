<?php
include('../php/config.php');
include('../php/session.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['staffId'])) {
    header('Location: ../login_form.php');
    exit();
}

$staffId = $_SESSION['staffId'];
$alertStyle = "";
$statusMsg = "";
$selectedCourseTitle = ""; // Default value for course title

// Check if form is submitted for viewing students
if (isset($_POST['submit'])) {
    $course_id = $_POST['course_id'];
    $level_id = $_POST['level_id'];
    $session_id = $_POST['session_id'];

    // Fetch the selected course title from the assignedlecture table
    $courseQuery = "SELECT course.courseTitle 
                    FROM course 
                    INNER JOIN assignedlecture ON course.course_id = assignedlecture.course_id 
                    WHERE assignedlecture.staff_id = ? AND course.course_id = ?";
    $courseStmt = $conn->prepare($courseQuery);
    $courseStmt->bind_param("ii", $staffId, $course_id);
    $courseStmt->execute();
    $courseResult = $courseStmt->get_result();

    if ($courseResult->num_rows > 0) {
        $courseRow = $courseResult->fetch_assoc();
        $selectedCourseTitle = $courseRow['courseTitle'];
    } else {
        $statusMsg = "Course not found or not assigned to you.";
        $alertStyle = "alert alert-danger";
    }

    // Fetch students and semester_id
    $query = "SELECT DISTINCT student.student_id, student.firstName, student.lastName, student.studentNo,
          student.dateCreated, level.levelName, session.sessionName, course.courseTitle, 
          course.semester_id
          FROM student
          INNER JOIN level ON level.level_id = student.level_id
          INNER JOIN session ON session.session_id = student.session_id
          INNER JOIN course_assignment ON course_assignment.department_id = student.department_id
          INNER JOIN course ON course.course_id = course_assignment.course_id
          WHERE course.course_id = ? AND student.level_id = ? AND student.session_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $course_id, $level_id, $session_id);
    $stmt->execute();
    $ret = $stmt->get_result();
}

// Check if form is submitted for saving attendance
if (isset($_POST['save'])) {
    if (isset($_POST['course_id']) && isset($_POST['level_id']) && isset($_POST['session_id'])) {
        $course_id = $_POST['course_id'];
        $level_id = $_POST['level_id'];
        $session_id = $_POST['session_id'];

        $dateTimeTaken = date('Y-m-d H:i:s'); // Add the current date and time
        $dateTaken = date('Y-m-d'); // Extract date from the current date and time

        // Fetch semester_id for the selected course
        $semesterQuery = "SELECT semester_id 
                          FROM course 
                          WHERE course_id = ?";
        $semesterStmt = $conn->prepare($semesterQuery);
        $semesterStmt->bind_param("i", $course_id);
        $semesterStmt->execute();
        $semesterResult = $semesterStmt->get_result();
        $semesterRow = $semesterResult->fetch_assoc();
        $semester_id = $semesterRow['semester_id'];

        // Check if attendance already taken for the course on the same date
        $checkQuery = "SELECT COUNT(*) as count 
                       FROM attendance 
                       WHERE course_id = ? AND level_id = ? AND session_id = ? AND DATE(dateTimeTaken) = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("iiis", $course_id, $level_id, $session_id, $dateTaken);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkRow = $checkResult->fetch_assoc();

        if ($checkRow['count'] > 0) {
            $statusMsg = "Attendance for this course has already been taken today.";
            $alertStyle = "alert alert-warning";
        } else {
            // Fetch all students for the selected course, level, and session
            $studentQuery = "SELECT studentNo 
                             FROM student
                             WHERE department_id = (SELECT department_id FROM course WHERE course_id = ?) 
                             AND level_id = ? AND session_id = ?";
            $studentStmt = $conn->prepare($studentQuery);
            $studentStmt->bind_param("iii", $course_id, $level_id, $session_id);
            $studentStmt->execute();
            $studentResult = $studentStmt->get_result();

            // Prepare insertion query
            $insertQuery = "INSERT INTO attendance (studentNo, course_id, session_id, level_id, semester_id, status, dateTimeTaken) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);

            $selectedStudents = isset($_POST['check']) ? $_POST['check'] : [];
            $status = 0; // Default to Absent

            while ($studentRow = $studentResult->fetch_assoc()) {
                $studentNo = $studentRow['studentNo'];
                $status = in_array($studentNo, $selectedStudents) ? 1 : 0; // Present or Absent
                $stmt->bind_param("siiisss", $studentNo, $course_id, $session_id, $level_id, $semester_id, $status, $dateTimeTaken);
                $stmt->execute();
            }

            if ($stmt->affected_rows > 0) {
                $statusMsg = "Attendance successfully saved!";
                $alertStyle = "alert alert-success";
            } else {
                $statusMsg = "Failed to save attendance. Please try again.";
                $alertStyle = "alert alert-danger";
            }
        }
    } else {
        $statusMsg = "Please provide all required information.";
        $alertStyle = "alert alert-danger";
    }
}

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
                    <a href="#" class="toggle-submenu active" data-submenu="attendance-submenu">
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
            <div class="col-lg-12">
                <div class="card">
                <div class="card-header">
                        <strong class="card-title"><h2 align="center">View Students</h2></strong>
                    </div>
                    <div class="card-body">
                        <div id="pay-invoice">
                            <div class="card-body">
                                <div class="<?php echo $alertStyle; ?>" role="alert"><?php echo $statusMsg; ?></div>
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-4">
                                            <div class="form-group">
                                                <label for="select" class="form-control-label">Select Course</label>
                                                <?php 
                                                    $que = mysqli_query($conn, "SELECT course.course_id, course.courseTitle 
                                                                                FROM course 
                                                                                INNER JOIN assignedlecture ON course.course_id = assignedlecture.course_id 
                                                                                WHERE assignedlecture.staff_id = '$staffId'");
                                                    echo '<select required name="course_id" class="custom-select form-control">';
                                                    echo '<option value="">--Select Course--</option>';
                                                    if (mysqli_num_rows($que) > 0) {                       
                                                        while ($row = mysqli_fetch_array($que)) {
                                                            echo '<option value="'.$row['course_id'].'">'.$row['courseTitle'].'</option>';
                                                        }
                                                    }
                                                    echo '</select>';
                                                ?>         
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group">
                                                <label for="level_id" class="control-label mb-1">Select Level</label>
                                                <?php
                                                $query = $conn->query("SELECT * FROM level");
                                                if ($query->num_rows > 0) {
                                                    echo '<select required name="level_id" class="custom-select form-control">';
                                                    echo '<option value="">--Select Level--</option>';
                                                    while ($row = $query->fetch_assoc()) {
                                                        echo '<option value="' . htmlspecialchars($row['level_id']) . '">' . htmlspecialchars($row['levelName']) . '</option>';
                                                    }
                                                    echo '</select>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group">
                                                <label for="session_id" class="control-label mb-1">Select Session</label>
                                                <?php
                                                $sessionQuery = $conn->query("SELECT * FROM session");
                                                if ($sessionQuery->num_rows > 0) {
                                                    echo '<select required name="session_id" class="custom-select form-control">';
                                                    echo '<option value="">--Select Session--</option>';
                                                    while ($sessionRow = $sessionQuery->fetch_assoc()) {
                                                        echo '<option value="' . htmlspecialchars($sessionRow['session_id']) . '">' . htmlspecialchars($sessionRow['sessionName']) . '</option>';
                                                    }
                                                    echo '</select>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" name="submit" class="btn btn-primary">View Students</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div> <!-- .card -->
            </div><!--/.col-->
            <br><br>
            <div class="col-lg-12">
                <div class="card">
                <div class="card-header">
                        <strong class="card-title"><h2 align="center"><?php echo htmlspecialchars($selectedCourseTitle); ?></h2></strong>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="table-responsive">
                            <table id="bootstrap-data-table" class="table table-hover table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Student Number</th>
                                            <th>Date Created</th>
                                            <th>Level</th>
                                            <th>Session</th>
                                            <th> Check</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sn = 1;
                                        if (isset($_POST['submit']) && $ret->num_rows > 0) {
                                            while ($row = $ret->fetch_assoc()) {
                                                echo '<tr>';
                                                echo '<td>' . $sn . '</td>';
                                                echo '<td>' . htmlspecialchars($row['firstName']) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['lastName']) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['studentNo']) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['dateCreated']) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['levelName']) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['sessionName']) . '</td>';
                                                echo '<td><input type="checkbox" name="check[]" value="' . htmlspecialchars($row['studentNo']) . '"></td>';
                                                echo '</tr>';
                                                $sn++;
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <input type="hidden" name="course_id" value="<?php echo isset($course_id) ? htmlspecialchars($course_id) : ''; ?>">
                            <input type="hidden" name="level_id" value="<?php echo isset($level_id) ? htmlspecialchars($level_id) : ''; ?>">
                            <input type="hidden" name="session_id" value="<?php echo isset($session_id) ? htmlspecialchars($session_id) : ''; ?>">
                            <button type="submit" name="save" class="btn btn-success">Save Attendance</button>
                        </form>
                    </div>
                </div>
            </div>
        </div> <!-- .row -->
    </div><!-- .animated -->
</div><!-- .content -->
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

document.addEventListener('DOMContentLoaded', function () {
            const alertElement = document.querySelector('.alert');

            if (alertElement) {
                setTimeout(function () {
                    alertElement.style.opacity = '0';
                    setTimeout(function () {
                        alertElement.style.display = 'none';
                    }, 600);
                }, 5000);
            }
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