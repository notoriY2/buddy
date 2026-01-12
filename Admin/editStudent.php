<?php
include('../php/config.php');
include('../php/session.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
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
// Check if an ID is set in the GET request
if (isset($_GET['editStudent_id'])) {
    $_SESSION['editStudent_id'] = $_GET['editStudent_id'];

    // Retrieve student details
    $query = mysqli_query($conn, "SELECT * FROM student WHERE studentNo='$_SESSION[editStudent_id]'");
    $rowi = mysqli_fetch_array($query);
} else {
    echo "<script type='text/javascript'>window.location = 'createStudent.php';</script>";
}

// Handle form submission
if (isset($_POST['submit'])) {
    $alertStyle = "";
    $statusMsg = "";

    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $othername = $_POST['othername'];
    $session_id = $_POST['session_id'];
    $studentNo = $_POST['studentNo'];
    $level_id = $_POST['level_id'];
    $department_id = $_POST['department_id'];
    $faculty_id = $_POST['faculty_id'];
    $dateCreated = date("Y-m-d");

    // Update student details
    $ret = mysqli_query($conn, "UPDATE student SET firstName='$firstname', lastName='$lastname', otherName='$othername',
        studentNo='$studentNo', level_id='$level_id', faculty_id='$faculty_id', department_id='$department_id', session_id='$session_id'
        WHERE studentNo='$_SESSION[editStudent_id]'");

    if ($ret) {
        $_SESSION['alertStyle'] = 'alert alert-success';
        $_SESSION['statusMsg'] = 'Student updated successfully!';
        echo "<script type='text/javascript'>window.location = 'createStudent.php';</script>";
    } else {
        $_SESSION['alertStyle'] = "alert alert-danger";
        $_SESSION['statusMsg'] = "An error occurred!";
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
                        <strong class="card-title">
                            <h2 align="center">Edit Student</h2>
                        </strong>
                    </div>
                    <div class="card-body">
                        <!-- Credit Card -->
                        <div id="pay-invoice">
                            <div class="card-body">
                                <div class="<?php echo $alertStyle; ?>" role="alert"><?php echo $statusMsg; ?></div>
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="cc-exp" class="control-label mb-1">Firstname</label>
                                                <input id="" name="firstname" type="text" class="form-control cc-exp"
                                                       value="<?php echo isset($rowi['firstName']) ? $rowi['firstName'] : ''; ?>"
                                                       required placeholder="Firstname">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label for="x_card_code" class="control-label mb-1">Lastname</label>
                                            <input id="" name="lastname" type="text" class="form-control cc-cvc"
                                                   value="<?php echo isset($rowi['lastName']) ? $rowi['lastName'] : ''; ?>"
                                                   required placeholder="Lastname">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="cc-exp" class="control-label mb-1">Othername</label>
                                                <input id="" name="othername" type="text" class="form-control cc-exp"
                                                       value="<?php echo isset($rowi['otherName']) ? $rowi['otherName'] : ''; ?>"
                                                       placeholder="Othername">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="x_card_code" class="control-label mb-1">Level</label>
                                                <?php
                                                $query = mysqli_query($conn, "SELECT * FROM level");
                                                $count = mysqli_num_rows($query);
                                                if ($count > 0) {
                                                    echo '<select required name="level_id" class="custom-select form-control">';
                                                    echo '<option value="">--Select Level--</option>';
                                                    while ($row = mysqli_fetch_array($query)) {
                                                        echo '<option value="' . $row['level_id'] . '" '
                                                            . ($row['level_id'] == $rowi['level_id'] ? 'selected' : '') . '>'
                                                            . $row['levelName'] . '</option>';
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
                                                <label for="cc-exp" class="control-label mb-1">Student No</label>
                                                <input id="" name="studentNo" type="text" class="form-control cc-exp"
                                                       value="<?php echo isset($rowi['studentNo']) ? $rowi['studentNo'] : ''; ?>"
                                                       required placeholder="Matric Number">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="x_card_code" class="control-label mb-1">Session</label>
                                                <?php
                                                $query = mysqli_query($conn, "SELECT * FROM session WHERE isActive = 1");
                                                $count = mysqli_num_rows($query);
                                                if ($count > 0) {
                                                    echo '<select required name="session_id" class="custom-select form-control">';
                                                    echo '<option value="">--Select Session--</option>';
                                                    while ($row = mysqli_fetch_array($query)) {
                                                        echo '<option value="' . $row['session_id'] . '" '
                                                            . ($row['session_id'] == $rowi['session_id'] ? 'selected' : '') . '>'
                                                            . $row['sessionName'] . '</option>';
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
                                    <button type="submit" name="submit" class="btn btn-primary">Update Student</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- .card -->
            </div>
            <!--/.col-->

            <br><br>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">
                            <h2 align="center">All Student</h2>
                        </strong>
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
                                    <th>Session</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $ret = mysqli_query($conn, "SELECT student.student_id, student.firstName, student.lastName, student.otherName, student.studentNo,
                                    student.dateCreated, level.levelName, faculty.facultyName, department.departmentName, session.sessionName
                                    FROM student
                                    INNER JOIN level ON level.level_id = student.level_id
                                    INNER JOIN session ON session.session_id = student.session_id
                                    INNER JOIN faculty ON faculty.faculty_id = student.faculty_id
                                    INNER JOIN department ON department.department_id = student.department_id");
                                $cnt = 1;
                                while ($row = mysqli_fetch_array($ret)) {
                                    ?>
                                    <tr>
                                        <td><?php echo $cnt; ?></td>
                                        <td><?php echo $row['firstName'] . ' ' . $row['lastName'] . ' ' . $row['otherName']; ?></td>
                                        <td><?php echo $row['studentNo']; ?></td>
                                        <td><?php echo $row['levelName']; ?></td>
                                        <td><?php echo $row['facultyName']; ?></td>
                                        <td><?php echo $row['departmentName']; ?></td>
                                        <td><?php echo $row['sessionName']; ?></td>
                                        <td><?php echo $row['dateCreated']; ?></td>
                                        <td>
    <a href="editStudent.php?editStudent_id=<?php echo $row['studentNo']; ?>" title="Edit Details">
        <i class="fa fa-edit fa-1x"></i>
    </a>
    <a onclick="return confirm('Are you sure you want to delete?')" href="../php/deleteStudent.php?del_id=<?php echo $row['studentNo']; ?>" title="Delete Student Details">
        <i class="fa fa-trash fa-1x"></i>
    </a>
</td>
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
    </div>
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