<?php
include('../php/config.php');
include('../php/session.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check for alert messages
$alertStyle = isset($_SESSION['alertStyle']) ? $_SESSION['alertStyle'] : '';
$statusMsg = isset($_SESSION['statusMsg']) ? $_SESSION['statusMsg'] : '';

// Clear the session variables to avoid repeated messages
unset($_SESSION['alertStyle']);
unset($_SESSION['statusMsg']);

// Check if the staffId is set in the session
if (!isset($_SESSION['staffId'])) {
    // If staffId is not set, redirect to the login page or show an error message
    header('Location: ../login_form.php'); // Redirect to login page
    exit(); // Stop further execution
}

// Assuming the staffId of the logged-in lecturer is stored in a session variable
$staff_id = $_SESSION['staffId'];
if (isset($_POST['submit'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $othername = $_POST['othername'];
    $studentNo = $_POST['studentNo'];
    $level_id = $_POST['level_id'];
    $session_id = $_POST['session_id'];
    $department_id = $_POST['department_id'];
    $faculty_id = $_POST['faculty_id'];
    $dateCreated = date("Y-m-d");

    // Generate email and password
    $email = $studentNo . '@buddy.ac.za'; // Concatenate studentNo with @buddy.ac.za for the email
    $password = password_hash($studentNo . '@buddy.123', PASSWORD_DEFAULT); // Generate the hashed password

    // Check if student already exists
    $stmt = $conn->prepare("SELECT * FROM student WHERE studentNo = ?");
    $stmt->bind_param("s", $studentNo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $alertStyle = "alert alert-danger";
        $statusMsg = "Student with the Student Number already exists!";
    } else {
        // Insert new student
        $stmt = $conn->prepare("INSERT INTO student (firstName, lastName, otherName, studentNo, email, password, level_id, faculty_id, department_id, session_id, dateCreated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssiiiss", $firstname, $lastname, $othername, $studentNo, $email, $password, $level_id, $faculty_id, $department_id, $session_id, $dateCreated);

        if ($stmt->execute()) {
            $alertStyle = "alert alert-success";
            $statusMsg = "Student Added Successfully!";
        } else {
            $alertStyle = "alert alert-danger";
            $statusMsg = "An error occurred!";
        }
    }

    $stmt->close();
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
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","../php/ajaxCall2.php?faculty_id="+str,true);
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
                    <a href="#" class="toggle-submenu active" data-submenu="student-submenu">
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
                        <strong class="card-title"><h2 align="center">Add New Student</h2></strong>
                    </div>
                    <div class="card-body">
                        <div id="pay-invoice">
                            <div class="card-body">
                                <div class="<?php echo $alertStyle;?>" role="alert"><?php echo $statusMsg;?></div>
                                <form method="post" action="">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="firstname" class="control-label mb-1">Firstname</label>
                                                <input id="firstname" name="firstname" type="text" class="form-control" required placeholder="Firstname">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label for="lastname" class="control-label mb-1">Lastname</label>
                                            <input id="lastname" name="lastname" type="text" class="form-control" required placeholder="Lastname">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="othername" class="control-label mb-1">Othername</label>
                                                <input id="othername" name="othername" type="text" class="form-control" placeholder="Othername">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="level_id" class="control-label mb-1">Level</label>
                                                <select required name="level_id" class="custom-select form-control">
                                                    <option value="">--Select Level--</option>
                                                    <?php
                                                    $query = mysqli_query($conn, "SELECT * FROM level");
                                                    while ($row = mysqli_fetch_array($query)) {
                                                        echo '<option value="' . $row['level_id'] . '">' . $row['levelName'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="studentNo" class="control-label mb-1">Student No</label>
                                                <input id="studentNo" name="studentNo" type="text" class="form-control" required placeholder="Student Number">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="session_id" class="control-label mb-1">Session</label>
                                                <select required name="session_id" class="custom-select form-control">
                                                    <option value="">--Select Session--</option>
                                                    <?php
                                                    $query = mysqli_query($conn, "SELECT * FROM session WHERE isActive = 1");
                                                    while ($row = mysqli_fetch_array($query)) {
                                                        echo '<option value="' . $row['session_id'] . '">' . $row['sessionName'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="faculty_id" class="control-label mb-1">Faculty</label>
                                                <select required name="faculty_id" onchange="showDepartment(this.value)" class="custom-select form-control">
                                                    <option value="">--Select Faculty--</option>
                                                    <?php
                                                    $query = mysqli_query($conn, "SELECT * FROM faculty ORDER BY facultyName ASC");
                                                    while ($row = mysqli_fetch_array($query)) {
                                                        echo '<option value="' . $row['faculty_id'] . '">' . $row['facultyName'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <div id="txtHint"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <p><small><i>Note: By default, the student's password is set to "<b>student No@buddy.123</b>"</i></small></p>
                                    <button type="submit" name="submit" class="btn btn-success">Add New Student</button>
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
                        <strong class="card-title"><h2 align="center">All Students</h2></strong>
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
                                $ret = mysqli_query($conn, "SELECT student.student_id, student.firstName, student.lastName, student.otherName, student.studentNo, student.dateCreated, level.levelName, faculty.facultyName, department.departmentName, session.sessionName FROM student INNER JOIN level ON level.level_id = student.level_id INNER JOIN session ON session.session_id = student.session_id INNER JOIN faculty ON faculty.faculty_id = student.faculty_id INNER JOIN department ON department.department_id = student.department_id");
                                $cnt = 1;
                                while ($row = mysqli_fetch_array($ret)) {
                                    echo '<tr>';
                                    echo '<td>' . $cnt . '</td>';
                                    echo '<td>' . $row['firstName'] . ' ' . $row['lastName'] . ' ' . $row['otherName'] . '</td>';
                                    echo '<td>' . $row['studentNo'] . '</td>';
                                    echo '<td>' . $row['levelName'] . '</td>';
                                    echo '<td>' . $row['facultyName'] . '</td>';
                                    echo '<td>' . $row['departmentName'] . '</td>';
                                    echo '<td>' . $row['sessionName'] . '</td>';
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
    </section>

    <!-- Scripts -->
    <script src="../javascript/script.js"></script>
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