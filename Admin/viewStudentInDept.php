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
error_reporting(E_ALL); // Report all errors
ini_set('display_errors', 1); // Display errors on the page

if (isset($_POST['view'])) {
    $faculty_id = $_POST['faculty_id'];
    $department_id = isset($_POST['department_id']) ? $_POST['department_id'] : '';
    $course_id = isset($_POST['course_id']) ? $_POST['course_id'] : '';

    // Build the query based on the selected options
    $query = "SELECT student.student_id, student.firstName, student.lastName, student.otherName, student.studentNo, student.dateCreated, level.levelName, faculty.facultyName, department.departmentName
              FROM student 
              INNER JOIN level ON level.level_id = student.level_id 
              INNER JOIN faculty ON faculty.faculty_id = student.faculty_id 
              INNER JOIN department ON department.department_id = student.department_id";

    $conditions = array();

    // Add conditions based on selected options
    if ($faculty_id != '') {
        $conditions[] = "student.faculty_id = " . intval($faculty_id);
    }
    if ($department_id != '') {
        $conditions[] = "student.department_id = " . intval($department_id);
    }
    if ($course_id != '') {
        $conditions[] = "student.course_id = " . intval($course_id);
    }

    // If there are any conditions, append them to the query
    if (count($conditions) > 0) {
        $query .= " WHERE " . implode(' AND ', $conditions);
    }

    $result = mysqli_query($conn, $query);
    if ($result) {
        // Display the students
        echo '<table class="table table-hover table-striped table-bordered">';
        echo '<thead><tr><th>#</th><th>FullName</th><th>StudentNo</th><th>Level</th><th>Faculty</th><th>Department</th><th>Date Added</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        $cnt = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . $cnt . '</td>';
            echo '<td>' . htmlspecialchars($row['firstName']) . ' ' . htmlspecialchars($row['lastName']) . ' ' . htmlspecialchars($row['otherName']) . '</td>';
            echo '<td>' . htmlspecialchars($row['studentNo']) . '</td>';
            echo '<td>' . htmlspecialchars($row['levelName']) . '</td>';
            echo '<td>' . htmlspecialchars($row['facultyName']) . '</td>';
            echo '<td>' . htmlspecialchars($row['departmentName']) . '</td>';
            echo '<td>' . htmlspecialchars($row['dateCreated']) . '</td>';
            echo '<td><a href="editStudent.php?editStudent_id=' . htmlspecialchars($row['studentNo']) . '" title="Edit Details"><i class="fa fa-edit fa-1x"></i></a> <a onclick="return confirm(\'Are you sure you want to delete?\')" href="../php/deleteStudent.php?del_id=' . htmlspecialchars($row['studentNo']) . '" title="Delete Student Details"><i class="fa fa-trash fa-1x"></i></a></td>';
            echo '</tr>';
            $cnt++;
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No students found for the selected criteria.</p>';
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
<!--[if gt IE 8]><!--> 
<html class="no-js" lang=""> 
<!--<![endif]-->
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
    <script>
    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }

    function showDepartment(str) {
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

    function showCourse(str) {
        if (str == "") {
            document.getElementById("txtHinttt").innerHTML = "";
            return;
        }
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHinttt").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "../php/ajaxCall3.php?department_id=" + str, true);
        xmlhttp.send();
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
                    <a href="#" class="toggle-submenu active" data-submenu="student-submenu">
                        <i class="fas fa-users"></i>
                        <span class="link-name">Students</span>
                        <i class="fas fa-chevron-right toggle-icon"></i>
                    </a>
                    <ul class="student-submenu submenu" style="display: none;">
                        <li><a href="createStudent.php">Add Student</a></li>
                        <li><a href="viewStudentInDept.php">View Student</a></li>
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
                        <div class="card mb-4">
                            <div class="card-header">
                                <strong class="card-title"><h2 align="center">View Student</h2></strong>
                            </div>
                            <div class="card-body">
                            <form method="post" id="studentForm">
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <label for="faculty_id" class="control-label mb-1">Faculty</label>
                <?php
                $query = $conn->query("SELECT * FROM faculty ORDER BY facultyName ASC");
                if ($query->num_rows > 0) {
                    echo '<select required name="faculty_id" onchange="showDepartment(this.value)" class="custom-select form-control">';
                    echo '<option value="">--Select Faculty--</option>';
                    while ($row = $query->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['faculty_id']) . '">' . htmlspecialchars($row['facultyName']) . '</option>';
                    }
                    echo '</select>';
                }
                ?>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <div id="txtHint"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <div id="txtHinttt"></div>
            </div>
        </div>
    </div>
    <button type="submit" name="view" class="btn btn-primary">View Student</button>
</form>
<div id="postsStoriesContainer"></div>
                        
                            </div>
                        </div>
                    </div>
                    
                    <br><br>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong class="card-title"><h3 align="center">All Students</h3></strong>
                            </div>
                            <div class="card-body">
                                <table id="bootstrap-data-table" class="table table-hover table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>FullName</th>
                                            <th>StudentNo</th>
                                            <th>Level</th>
                                            <th>Faculty</th>
                                            <th>Department</th>
                                            <th>Date Added</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                <?php
                                $ret = mysqli_query($conn, "SELECT student.student_id, student.firstName, student.lastName, student.otherName, student.studentNo, student.dateCreated, level.levelName, faculty.facultyName, department.departmentName FROM student INNER JOIN level ON level.level_id = student.level_id INNER JOIN session ON session.session_id = student.session_id INNER JOIN faculty ON faculty.faculty_id = student.faculty_id INNER JOIN department ON department.department_id = student.department_id");
                                $cnt = 1;
                                while ($row = mysqli_fetch_array($ret)) {
                                    echo '<tr>';
                                    echo '<td>' . $cnt . '</td>';
                                    echo '<td>' . $row['firstName'] . ' ' . $row['lastName'] . ' ' . $row['otherName'] . '</td>';
                                    echo '<td>' . $row['studentNo'] . '</td>';
                                    echo '<td>' . $row['levelName'] . '</td>';
                                    echo '<td>' . $row['facultyName'] . '</td>';
                                    echo '<td>' . $row['departmentName'] . '</td>';
                                    echo '<td>' . $row['dateCreated'] . '</td>';
                                    echo '<td><a href="editStudent.php?editStudent_id=' . $row['studentNo'] . '" title="Edit Details"><i class="fa fa-edit fa-1x"></i></a> <a onclick="return confirm(\'Are you sure you want to delete?\')" href="../php/deleteStudent.php?del_id=' . $row['studentNo'] . '" title="Delete Student Details"><i class="fa fa-trash fa-1x"></i></a></td>';
                                    echo '</tr>';
                                    $cnt++;
                                }
                                ?>
                            </tbody>
                                </table>
                            </div>
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