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
    // Get POST data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phoneNo = $_POST['phoneNo'];
    $staffNo = $_POST['staffNo'];
    $staffType_id = 2;
    $dateCreated = date("Y-m-d");

    $department_id = $_POST['department_id'];
    $faculty_id = $_POST['faculty_id'];
    $course_id = $_POST['course_id'];
    $dateAssigned = date("Y-m-d");

    // Check if staff with the given staffNo already exists
    $stmt = $conn->prepare("SELECT * FROM staff WHERE staffNo = ?");
    $stmt->bind_param("s", $staffNo);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    // Check if there is already an assignment for the given faculty, department, and course
    $stmt = $conn->prepare("SELECT * FROM assignedlecture WHERE faculty_id = ? AND department_id = ? AND course_id = ?");
    $stmt->bind_param("iii", $faculty_id, $department_id, $course_id);
    $stmt->execute();
    $rets = $stmt->get_result()->fetch_assoc();

    if ($res) {
        $alertStyle = "alert alert-danger";
        $statusMsg = "Lecturer with the Staff No already exists!";
    } elseif ($rets) {
        $alertStyle = "alert alert-danger";
        $statusMsg = "A Lecturer has already been assigned to this Course!";
    } else {
        // Insert new staff
$stmt = $conn->prepare("INSERT INTO staff (name, email, phoneNo, password, staffNo, staffType_id, isPasswordChanged, isAssigned, dateCreated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$email = $staffNo . '@buddy.ac.za'; // Concatenate staffNo with @buddy.ac.za for the email
$password = password_hash($staffNo . '@buddy.123', PASSWORD_DEFAULT); // Concatenate staffNo with @buddy.123 and hash the password
$isPasswordChanged = 0;
$isAssigned = 0;
$stmt->bind_param("sssssiiis", $name, $email, $phoneNo, $password, $staffNo, $staffType_id, $isPasswordChanged, $isAssigned, $dateCreated);

        if ($stmt->execute()) {
            // Retrieve the last inserted staff_id
            $staff_id = $conn->insert_id;

            // Insert into assignedlecture
            $stmt = $conn->prepare("INSERT INTO assignedlecture (staff_id, department_id, faculty_id, course_id, dateAssigned) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiss", $staff_id, $department_id, $faculty_id, $course_id, $dateAssigned);

            if ($stmt->execute()) {
                // Update staff assigned status
                $stmt = $conn->prepare("UPDATE staff SET isAssigned = 1 WHERE staff_id = ?");
                $stmt->bind_param("i", $staff_id);

                if ($stmt->execute()) {
                    $alertStyle = "alert alert-success";
                    $statusMsg = "Lecturer Created and Assigned Successfully!";
                } else {
                    $alertStyle = "alert alert-danger";
                    $statusMsg = "An error occurred while updating the staff assignment status!";
                }
            } else {
                $alertStyle = "alert alert-danger";
                $statusMsg = "An error occurred while assigning the lecturer!";
            }
        } else {
            $alertStyle = "alert alert-danger";
            $statusMsg = "An error occurred while creating the lecturer!";
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
                    <a href="#" class="toggle-submenu active" data-submenu="lecture-submenu">
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
                        <strong class="card-title"><h2 align="center">Add New Lecturer</h2></strong>
                    </div>
                    <div class="card-body">
                        <div id="pay-invoice">
                            <div class="card-body">
                               <div class="<?php echo $alertStyle;?>" role="alert"><?php echo $statusMsg;?></div>
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="name" class="control-label mb-1">Fullname</label>
                                                <input id="name" name="name" type="text" class="form-control" value="" required placeholder="Fullname">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="phoneNo" class="control-label mb-1">Phone Number</label>
                                                <input id="phoneNo" name="phoneNo" type="text" class="form-control" value="" placeholder="Phone Number">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="staffNo" class="control-label mb-1">Staff No</label>
                                                <input id="staffNo" name="staffNo" type="text" class="form-control" value="" required placeholder="Staff No">
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

<p><small><i>Note: By default, the lectures's password is set to "<b>staff No@buddy.123</b>"</i></small></p>
                                    <button type="submit" name="submit" class="btn btn-success">Add Lecturer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div> <!-- .card -->
            </div><!--/.col-->

            <br><br>
            <div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <strong class="card-title"><h2 align="center">All Assigned Lecturers</h2></strong>
        </div>
        <div class="card-body">
            <table id="bootstrap-data-table" class="table table-hover table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Staff No</th>
                        <th>Name</th>
                        <th>Email Address</th>
                        <th>Phone No</th>
                        <th>Faculty</th>
                        <th>Department</th>
                        <th>Course</th>
                        <th>Date Assigned</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = $conn->query("
                        SELECT assignedlecture.assignLecture_id, assignedlecture.dateAssigned, staff.staffNo, staff.name, staff.email, staff.phoneNo,
                               faculty.facultyName, department.departmentName, course.courseTitle
                        FROM assignedlecture
                        INNER JOIN staff ON staff.staff_id = assignedlecture.staff_id
                        INNER JOIN faculty ON faculty.faculty_id = assignedlecture.faculty_id
                        INNER JOIN department ON department.department_id = assignedlecture.department_id
                        INNER JOIN course ON course.course_id = assignedlecture.course_id
                    ");
                    $cnt = 1;
                    while ($row = $query->fetch_assoc()) {
                        echo "<tr>
                            <td>{$cnt}</td>
                            <td>{$row['staffNo']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['email']}</td>
                            <td>{$row['phoneNo']}</td>
                            <td>{$row['facultyName']}</td>
                            <td>{$row['departmentName']}</td>
                            <td>{$row['courseTitle']}</td>
                            <td>{$row['dateAssigned']}</td>
                            <td><a onclick=\"return confirm('Are you sure you want to delete?')\" href=\"../php/deleteAssignedLecture.php?del_id={$row['assignLecture_id']}\" title=\"Delete Lecturer\"><i class=\"fa fa-trash fa-1x\"></i></a></td>
                        </tr>";
                        $cnt++;
                    }
                    ?>
                </tbody>
            </table>
            <tfoot>
                                                <tr>
                                                    <td colspan="6">
                                                        <div class="pagination-wrapper">
                                                            <ul class="pagination">
                                                                <li class="page-item disabled">
                                                                    <a href="#" class="page-link">Previous</a>
                                                                </li>
                                                                <li class="page-item active">
                                                                    <a href="#" class="page-link">1</a>
                                                                </li>
                                                                <li class="page-item disabled">
                                                                    <a href="#" class="page-link">Next</a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tfoot>
        </div>
    </div>
</div>
<!-- end of datatable -->

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
    </script>
</body>
</html>