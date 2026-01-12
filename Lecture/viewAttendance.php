<?php 
include('../php/config.php');
include('../php/session.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$statusMsg = '';

if (isset($_POST['view'])) {
    $course_id = $_POST['course_id'];
    $level_id = $_POST['level_id'];
    $dateTaken = $_POST['dateTaken'];

    // Check if course, level, and date are not empty
    if (!empty($course_id) && !empty($level_id) && !empty($dateTaken)) {
        // Update the SQL query to use the correct tables
        $query = "
            SELECT 
                attendance.attendance_id, 
                attendance.status, 
                attendance.dateTimeTaken, 
                session.sessionName, 
                semester.semesterName,
                student.firstName, 
                student.lastName, 
                student.studentNo,
                level.levelName,
                course.courseTitle
            FROM attendance
            INNER JOIN student ON student.studentNo = attendance.studentNo
            INNER JOIN course ON course.course_id = attendance.course_id
            INNER JOIN session ON session.session_id = attendance.session_id
            INNER JOIN semester ON semester.semester_id = attendance.semester_id
            INNER JOIN level ON level.level_id = attendance.level_id
            WHERE attendance.dateTimeTaken = '$dateTaken' AND attendance.course_id = '$course_id' AND attendance.level_id = '$level_id'";

        $rs = $conn->query($query);
        $num = $rs->num_rows;
    } else {
        $statusMsg = "<div class='alert alert-danger' role='alert'>Please select a course, level, and date!</div>";
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
                <div class="card mb-4">
                <div class="card-header">
                        <strong class="card-title"><h2 align="center">View Class Attendance</h2></strong>
                        <?php echo $statusMsg; ?>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="select" class="form-control-label">Select Course<span class="text-danger ml-2">*</span></label>
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
                                        <label for="level_id" class="form-control-label">Select Level<span class="text-danger ml-2">*</span></label>
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
                                        <label class="form-control-label">Select Date<span class="text-danger ml-2">*</span></label>
                                        <input type="date" class="form-control" name="dateTaken" required>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="view" class="btn btn-primary">View Attendance</button>
                        </form>
                    </div>
                </div>
            </div><!--/.col-->
            <div class="col-md-12">
                <div class="card mb-4">
                <div class="card-header">
                        <strong class="card-title"><h2 align="center">Class Attendance</h2></strong>
                    </div>
                    <div class="table-responsive p-3">
                    <table id="bootstrap-data-table" class="table table-hover table-striped table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Student No</th>
                                    <th>Level</th>
                                    <th>Course</th>
                                    <th>Session</</th>
                                    <th>Semester</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($_POST['view']) && !empty($course_id) && !empty($level_id) && !empty($dateTaken)) {
                                    $sn = 0;

                                    if ($num > 0) { 
                                        while ($rows = $rs->fetch_assoc()) {
                                            // Handle status and color
                                            $status = ($rows['status'] == 1) ? "Present" : "Absent";
                                            $colour = ($rows['status'] == 1) ? "#00FF00" : "#FF0000";

                                            $sn++;
                                            echo "<tr>
                                                    <td>{$sn}</td>
                                                    <td>{$rows['firstName']}</td>
                                                    <td>{$rows['lastName']}</td>
                                                    <td>{$rows['studentNo']}</td>
                                                    <td>{$rows['levelName']}</td>
                                                    <td>{$rows['courseTitle']}</td>
                                                    <td>{$rows['sessionName']}</td>
                                                    <td>{$rows['semesterName']}</td>
                                                    <td style='background-color:{$colour}'>{$status}</td>
                                                    <td>{$rows['dateTimeTaken']}</td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='10'>No Record Found!</td></tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div><!-- .row -->
    </div><!-- .animated -->
</div><!-- .dash-content -->
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