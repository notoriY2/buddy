<?php
include('../php/config.php');
include('../php/session.php');
include('../php/functions.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$staffId = $_SESSION['staffId'];

if (isset($_GET['studentNo']) && isset($_GET['level_id']) && isset($_GET['department_id']) && isset($_GET['faculty_id']) && isset($_GET['session_id']) && isset($_GET['semester_id']) && isset($_GET['semester'])) {

    $studentNo = $_GET['studentNo'];
    $level_id = $_GET['level_id'];
    $department_id = $_GET['department_id'];
    $faculty_id = $_GET['faculty_id'];
    $session_id = $_GET['session_id'];
    $semester_id = $_GET['semester_id'];
    $semester = $_GET['semester'];

    // Fetch student details
    $stdQuery = mysqli_query($conn, "SELECT * FROM student WHERE studentNo = '$studentNo'");
    $rowStd = mysqli_fetch_array($stdQuery);

    // Fetch semester details
    $semesterQuery = mysqli_query($conn, "SELECT * FROM semester WHERE semester_id = '$semester_id'");
    $rowSemester = mysqli_fetch_array($semesterQuery);

    // Fetch session details
    $sessionQuery = mysqli_query($conn, "SELECT * FROM session WHERE session_id = '$session_id'");
    $rowSession = mysqli_fetch_array($sessionQuery);

    // Fetch level details
    $levelQuery = mysqli_query($conn, "SELECT * FROM level WHERE level_id = '$level_id'");
    $rowLevel = mysqli_fetch_array($levelQuery);

} else {
    echo "<script type='text/javascript'>
        window.location = ('computeGPAResults.php');
    </script>";
}

//------------------------------------ COMPUTE RESULT -----------------------------------------------

if (isset($_POST['compute'])) {

    $score = $_POST['score'];
    $N = count($score);

    $courseCode = $_POST['courseCode'];
    $courseUnit = $_POST['courseUnit'];
    $dateAdded = date("Y-m-d");

    $letterGrade = "";
    $gradePoint = "";
    $scoreGradePoint = 0.00;

    $totalCourseUnit = 0;
    $totalScoreGradePoint = 0;
    $gpa = "";

    for ($i = 0; $i < $N; $i++) {

        $letterGrade = getScoreLetterGrade($score[$i]);
        $gradePoint = getScoreGradePoint($score[$i]);

        $scoreGradePoint = $courseUnit[$i] * $gradePoint;

        $que = mysqli_query($conn, "SELECT * FROM finalresult WHERE studentNo ='$studentNo' AND level_id = '$level_id' AND semester_id = '$semester_id' AND session_id = '$session_id'");
        $ret = mysqli_fetch_array($que);

        if ($ret == 0) {  // if no record exists, insert a record

            $query = mysqli_query($conn, "INSERT INTO result(studentNo, level_id, semester_id, session_id, courseCode, courseUnit, score, scoreGradePoint, scoreLetterGrade, totalScoreGradePoint, dateAdded) 
            VALUES('$studentNo', '$level_id', '$semester_id', '$session_id', '$courseCode[$i]', '$courseUnit[$i]', '$score[$i]', '$gradePoint', '$letterGrade', '$scoreGradePoint', '$dateAdded')");

            if ($query) {

                $totalCourseUnit += $courseUnit[$i];
                $totalScoreGradePoint += $scoreGradePoint;

                $gpa = round(($totalScoreGradePoint / $totalCourseUnit), 2);
                $classOfDiploma = getClassOfDiploma($gpa);

            } else {
                $alertStyle = "alert alert-danger";
                $statusMsg = "An error occurred!";
            }

        }
    }

    $que = mysqli_query($conn, "SELECT * FROM finalresult WHERE studentNo ='$studentNo' AND level_id = '$level_id' AND semester_id = '$semester_id' AND session_id = '$session_id'");
    $ret = mysqli_fetch_array($que);

    if ($ret > 0) {

        $alertStyle = "alert alert-danger";
        $statusMsg = "The result has been computed for this student for this semester, level, and session!";
    } else {

        $querys = mysqli_query($conn, "INSERT INTO finalresult(studentNo, level_id, semester_id, session_id, totalCourseUnit, totalScoreGradePoint, gpa, classOfDiploma, dateAdded) 
        VALUES('$studentNo', '$level_id', '$semester_id', '$session_id', '$totalCourseUnit', '$totalScoreGradePoint', '$gpa', '$classOfDiploma', '$dateAdded')");

        if ($querys) {

            $alertStyle = "alert alert-success";
            $statusMsg = "Result Computed Successfully!";

            echo "<script type='text/javascript'>
            setTimeout(function() {
                window.location.href = 'computeGPAResults.php';
            }, 5000); // Redirects after 3 seconds
          </script>";
        } else {
            $alertStyle = "alert alert-danger";
            $statusMsg = "An error occurred!";
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
    <meta name="description" content="Ela Admin - HTML5 Admin Template">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css">
     
    <!-- Iconscout CSS -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
//Only allows Numbers
function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}

//Check if the value entered is greater than 100 and not less than 0
function myFunction() {
  var x = document.getElementById("score").value;
  if (isNaN(x) || x < 1 || x > 100) {
    alert("Invalid");
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
                    <a href="#" class="toggle-submenu active" data-submenu="results-submenu">
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
                        <strong class="card-title">
                            <h4 align="center">Compute <?php echo  $rowStd['firstName'].' '.$rowStd['lastName']?>'s&nbsp;<?php echo $rowLevel['levelName'];?>&nbsp;[<?php echo $rowSemester['semesterName'];?>] - Semester Result</h4>
                        </strong>
                    </div>
                    <form method="post">
                    <div class="card-body">
                        <p id="demo"></p>
                        <div class="<?php if(isset($alertStyle)){echo $alertStyle;}?>" role="alert"><?php if(isset($statusMsg)){echo $statusMsg;}?></div>
                        <table class="table table-hover table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Course</th>
                                    <th>Code</th>
                                    <th>Unit</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
// Assuming $rowSemester['semester_id'] is available
$semesterId = $rowSemester['semester_id'];

// Fetch data based on the selected semester
$ret = mysqli_query($conn, "
    SELECT DISTINCT
        course.course_id, 
        course.courseTitle, 
        course.courseCode, 
        course.courseUnit,
        semester.semester_id 
    FROM 
        course
    INNER JOIN 
        assignedlecture ON course.course_id = assignedlecture.course_id
    INNER JOIN course_assignment ON course.course_id = course_assignment.course_id
    INNER JOIN semester ON course.semester_id = semester.semester_id
    WHERE 
        assignedlecture.staff_id = '$staffId'
        AND semester.semester_id = '$semesterId'
    ORDER BY 
        course.courseTitle ASC
");

$cnt = 1;
while ($row = mysqli_fetch_array($ret)) {
?>
    <tr>
        <td><?php echo $cnt; ?></td>
        <td><?php echo htmlspecialchars($row['courseTitle']); ?></td>
        <td><?php echo htmlspecialchars($row['courseCode']); ?></td>
        <td><?php echo htmlspecialchars($row['courseUnit']); ?></td>
        <td><input name="score[]" id="score" type="text" class="form-control" maxlength="3" onkeypress="return isNumber(event)"></td>
        <input value="<?php echo htmlspecialchars($row['courseCode']); ?>" name="courseCode[]" type="hidden">
        <input value="<?php echo htmlspecialchars($row['courseUnit']); ?>" name="courseUnit[]" type="hidden">
        <input value="<?php echo htmlspecialchars($row['course_id']); ?>" type="hidden">
    </tr>
<?php 
    $cnt++;
}
?>
</tbody>

                        </table>
                        <button type="submit" onclick="myFunction()" name="compute" class="btn btn-success">Compute Result</button>
                    </form>
                    </div>
                </div>
            </div>
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
document.addEventListener('DOMContentLoaded', function () {
            const alertElement = document.querySelector('.alert');

            if (alertElement) {
                setTimeout(function () {
                    alertElement.style.opacity = '0';
                    setTimeout(function () {
                        alertElement.style.display = 'none';
                    }, 600);
                }, 3000);
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