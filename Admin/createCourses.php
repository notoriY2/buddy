<?php
include('../php/config.php');
include('../php/session.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$alertStyle = "";
$statusMsg = "";

// Check if the staffId is set in the session
if (!isset($_SESSION['staffId'])) {
    // If staffId is not set, redirect to the login page or show an error message
    header('Location: ../login_form.php'); // Redirect to login page
    exit(); // Stop further execution
}

// Assuming the staffId of the logged-in lecturer is stored in a session variable
$staff_id = $_SESSION['staffId'];

// Retrieve alert messages from the session
if (isset($_SESSION['alertStyle']) && isset($_SESSION['statusMsg'])) {
    $alertStyle = $_SESSION['alertStyle'];
    $statusMsg = $_SESSION['statusMsg'];

    // Clear alert messages from the session
    unset($_SESSION['alertStyle']);
    unset($_SESSION['statusMsg']);
}

if (isset($_POST['submit'])) {
    $courseTitle = htmlspecialchars(trim($_POST['courseTitle']));
    $courseCode = htmlspecialchars(trim($_POST['courseCode']));
    $level_id = (int)$_POST['level_id'];
    $semester_id = (int)$_POST['semester_id'];
    $courseUnit = (int)$_POST['courseUnit'];
    $department_id = (int)$_POST['department_id'];
    $faculty_id = (int)$_POST['faculty_id'];
    $dateAdded = date("Y-m-d");
    $pre_courseCode1 = htmlspecialchars(trim($_POST['pre_courseCode1']));
    $pre_courseCode2 = htmlspecialchars(trim($_POST['pre_courseCode2']));

    // Validate prerequisites
    $valid_pre1 = empty($pre_courseCode1) ? true : $conn->prepare("SELECT course_id FROM course WHERE courseCode = ?");
    if ($valid_pre1) {
        if (!empty($pre_courseCode1)) {
            $valid_pre1->bind_param("s", $pre_courseCode1);
            $valid_pre1->execute();
            $valid_pre1 = $valid_pre1->get_result()->num_rows > 0;
        }
    } else {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $valid_pre2 = empty($pre_courseCode2) ? true : $conn->prepare("SELECT course_id FROM course WHERE courseCode = ?");
    if ($valid_pre2) {
        if (!empty($pre_courseCode2)) {
            $valid_pre2->bind_param("s", $pre_courseCode2);
            $valid_pre2->execute();
            $valid_pre2 = $valid_pre2->get_result()->num_rows > 0;
        }
    } else {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    if (!$valid_pre1 || !$valid_pre2) {
        $alertStyle = "alert alert-danger";
        $statusMsg = "One or both prerequisite course codes are invalid.";
    } else {
        // Proceed with course creation or update
        $query = $conn->prepare("SELECT course_id FROM course WHERE courseCode = ?");
        if ($query) {
            $query->bind_param("s", $courseCode);
            $query->execute();
            $result = $query->get_result();

            if ($result->num_rows > 0) {
                // Course exists, update details
                $course_id = $result->fetch_assoc()['course_id'];
                $query = $conn->prepare("UPDATE course SET courseTitle = ?, courseUnit = ?, level_id = ?, semester_id = ?, pre_courseCode1 = ?, pre_courseCode2 = ? WHERE courseCode = ?");
                if ($query) {
                    $query->bind_param("siiisss", $courseTitle, $courseUnit, $level_id, $semester_id, $pre_courseCode1, $pre_courseCode2, $courseCode);
                } else {
                    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                }
            } else {
                // Insert new course
                $query = $conn->prepare("INSERT INTO course (courseTitle, courseCode, courseUnit, level_id, semester_id, dateAdded, pre_courseCode1, pre_courseCode2) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($query) {
                    $query->bind_param("ssiiisss", $courseTitle, $courseCode, $courseUnit, $level_id, $semester_id, $dateAdded, $pre_courseCode1, $pre_courseCode2);
                } else {
                    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                }
            }

            if ($query->execute()) {
                if (!$result->num_rows > 0) {
                    $course_id = $conn->insert_id;
                }

                // Insert or update course assignment
                $query = $conn->prepare("INSERT INTO course_assignment (course_id, faculty_id, department_id) VALUES (?, ?, ?)");
                if ($query) {
                    $query->bind_param("iii", $course_id, $faculty_id, $department_id);
                    if ($query->execute()) {
                        $alertStyle = "alert alert-success";
                        $statusMsg = "Course " . ($result->num_rows > 0 ? "Updated" : "Created") . " and Assigned Successfully!";
                    } else {
                        $alertStyle = "alert alert-danger";
                        $statusMsg = "An error occurred during course assignment: " . $conn->error;
                    }
                } else {
                    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                }
            } else {
                $alertStyle = "alert alert-danger";
                $statusMsg = "An error occurred during course " . ($result->num_rows > 0 ? "update" : "creation") . ": " . $conn->error;
            }
        } else {
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
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
    <script>
function showValues(str) {
    if (str == "") {
        document.getElementById("txtHint").innerHTML = "";
        return;
    } else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "../php/ajaxCall2.php?faculty_id=" + str, true);
        xmlhttp.send();
    }
}
</script>
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
                    <a href="createCourses.php" class="active">
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
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title"><h2 align="center">Add New Course</h2></strong>
                    </div>
                    <div class="card-body">
                        <div id="pay-invoice">
                            <div class="card-body">
                                <?php if (!empty($statusMsg)) { ?>
                                    <div class="alert <?php echo $alertStyle; ?>" role="alert">
                                        <?php echo $statusMsg; ?>
                                    </div>
                                <?php } ?>
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="courseTitle" class="control-label mb-1">Course Title</label>
                                                <input id="courseTitle" name="courseTitle" type="text" class="form-control" value="" required placeholder="Course Title">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="courseCode" class="control-label mb-1">Course Code</label>
                                                <input id="courseCode" name="courseCode" type="text" class="form-control" value="" required placeholder="Course Code">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="courseUnit" class="control-label mb-1">Course Unit</label>
                                                <input id="courseUnit" name="courseUnit" type="text" class="form-control" value="" required placeholder="Course Unit">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="level_id" class="control-label mb-1">Level</label>
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
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="semester_id" class="control-label mb-1">Semester</label>
                                                <?php
                                                $query = $conn->query("SELECT * FROM semester");
                                                if ($query->num_rows > 0) {
                                                    echo '<select required name="semester_id" class="custom-select form-control">';
                                                    echo '<option value="">--Select Semester--</option>';
                                                    while ($row = $query->fetch_assoc()) {
                                                        echo '<option value="' . htmlspecialchars($row['semester_id']) . '">' . htmlspecialchars($row['semesterName']) . '</option>';
                                                    }
                                                    echo '</select>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="faculty_id" class="control-label mb-1">Faculty</label>
                                                <?php
                                                $query = $conn->query("SELECT * FROM faculty ORDER BY facultyName ASC");
                                                if ($query->num_rows > 0) {
                                                    echo '<select required name="faculty_id" onchange="showValues(this.value)" class="custom-select form-control">';
                                                    echo '<option value="">--Select Faculty--</option>';
                                                    while ($row = $query->fetch_assoc()) {
                                                        echo '<option value="' . htmlspecialchars($row['faculty_id']) . '">' . htmlspecialchars($row['facultyName']) . '</option>';
                                                    }
                                                    echo '</select>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <?php echo "<div id='txtHint'></div>"; ?>
                                            </div>
                                        </div>
                                        <div class="col-6">
        <div class="form-group">
            <label for="pre_courseCode1" class="control-label mb-1">Prerequisite 1</label>
            <input id="pre_courseCode1" name="pre_courseCode1" type="text" class="form-control" value="" placeholder="Prerequisite Code" oninput="toggleSecondPrerequisite()">
        </div>
    </div>
                                    </div>

                                    <div class="row">
                                    <div class="col-6" id="pre2_container" style="display:none;">
        <div class="form-group">
            <label for="pre_courseCode2" class="control-label mb-1">Prerequisite 2</label>
            <input id="pre_courseCode2" name="pre_courseCode2" type="text" class="form-control" value="" placeholder="Prerequisite Code">
        </div>
    </div>
                                    </div>
                                    <button type="submit" name="submit" class="btn btn-success">Add Course</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br><br>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title"><h2 align="center">All Courses</h2></strong>
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
                            <th>Prerequisite</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include('../php/config.php');
                        $query = $conn->query("
                            SELECT course.course_id, course.courseTitle, course.courseCode, course.courseUnit, 
                                   level.levelName, semester.semesterName, course.pre_courseCode1, course.pre_courseCode2
                            FROM course
                            INNER JOIN level ON level.level_id = course.level_id
                            INNER JOIN semester ON semester.semester_id = course.semester_id
                        ");

                        $cnt = 1;
                        while ($row = $query->fetch_assoc()) {
                            $course_id = $row['course_id'];

                            // Get faculty and departments for the course
                            $faculty_dept_query = $conn->query("
                                SELECT faculty.facultyName, department.departmentName
                                FROM course_assignment
                                INNER JOIN faculty ON faculty.faculty_id = course_assignment.faculty_id
                                INNER JOIN department ON department.department_id = course_assignment.department_id
                                WHERE course_assignment.course_id = $course_id
                            ");

                            $faculties = [];
                            $departments = [];
                            while ($fd_row = $faculty_dept_query->fetch_assoc()) {
                                $faculties[] = $fd_row['facultyName'];
                                $departments[] = $fd_row['departmentName'];
                            }
                            $faculty_names = implode(', ', $faculties);
                            $department_names = implode(', ', $departments);
                            $prerequisites = trim($row['pre_courseCode1'] . ', ' . $row['pre_courseCode2'], ', ');
                        ?>
                            <tr>
                                <td><?php echo $cnt; ?></td>
                                <td><?php echo htmlspecialchars($row['courseTitle']); ?></td>
                                <td><?php echo htmlspecialchars($row['courseCode']); ?></td>
                                <td><?php echo htmlspecialchars($row['courseUnit']); ?></td>
                                <td><?php echo htmlspecialchars($row['levelName']); ?></td>
                                <td><?php echo htmlspecialchars($faculty_names); ?></td>
                                <td><?php echo htmlspecialchars($department_names); ?></td>
                                <td><?php echo htmlspecialchars($row['semesterName']); ?></td>
                                <td><?php echo htmlspecialchars($prerequisites); ?></td>
                                <td>
                                    <a href="editCourses.php?editCourse_id=<?php echo htmlspecialchars($row['courseCode']); ?>" title="Edit Details"><i class="fa fa-edit fa-1x"></i></a>
                                    <a onclick="return confirm('Are you sure you want to delete?')" href="../php/deleteCourse.php?del_id=<?php echo htmlspecialchars($row['courseCode']); ?>" title="Delete Course"><i class="fa fa-trash fa-1x"></i></a>
                                </td>
                            </tr>
                        <?php 
                        $cnt++;
                        } ?>
                    </tbody>
                </table>
                    </div>
                </div>
            </div>
        </div><!-- .animated -->
    </div>
    </section>

    <!-- Scripts -->
    <script src="../javascript/script.js"></script>
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

    function toggleSecondPrerequisite() {
    const pre1 = document.getElementById('pre_courseCode1').value;
    const pre2Container = document.getElementById('pre2_container');
    if (pre1.trim() !== '') {
        pre2Container.style.display = 'block';
    } else {
        pre2Container.style.display = 'none';
    }
}
    </script>
</body>
</html>