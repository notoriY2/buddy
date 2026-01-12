<?php
include('../php/config.php');
include('../php/session.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$statusMsg = '';

// Ensure the student is logged in
if (!isset($_SESSION['studentNo'])) {
    header("Location: ../login.php");
    exit();
}

$rowLevel = $rowSemester = $rowSession = [];

if(isset($_POST['submit'])) {
    $levelId = $_POST['level_id'];
    $sessionId = $_POST['session_id'];
    $semesterId = $_POST['semester_id'];

    $semesterQuery = mysqli_query($conn, "SELECT * FROM semester WHERE semester_id = '$semesterId'");
    $rowSemester = mysqli_fetch_array($semesterQuery);

    $sessionQuery = mysqli_query($conn, "SELECT * FROM session WHERE session_id = '$sessionId'");
    $rowSession = mysqli_fetch_array($sessionQuery);

    $levelQuery = mysqli_query($conn, "SELECT * FROM level WHERE level_id = '$levelId'");
    $rowLevel = mysqli_fetch_array($levelQuery);
}

$studentNo = $_SESSION['studentNo'];
$stmt = $conn->prepare("SELECT student.student_id, student.firstName, student.lastName, student.email, profile.image FROM student LEFT JOIN profile ON student.student_id = profile.student_id WHERE student.studentNo = ?");
$stmt->bind_param("s", $studentNo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $studentId = $row['student_id'];
    $firstName = htmlspecialchars($row['firstName']);
    $lastName = htmlspecialchars($row['lastName']);
    $email = htmlspecialchars($row['email']);
    $profileImage = htmlspecialchars($row['image']);
    
    // Set a default image if profile image is not set
    $profileImagePath = $profileImage ? "../php/images/{$profileImage}" : 'default.png';

    // Redirect to profile setup if the profile image is not set
    if (is_null($profileImage)) {
        header('Location: profile.php');
        exit;
    }
} else {
    // If no user data is found, force logout
    header('Location: ../logout.php');
    exit;
}
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
    <meta name="description" content="Ela Admin - HTML5 Admin Template">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <!----======== CSS ======== -->
    <link rel="stylesheet" href="../css/style.css">
     
    <!----===== Iconscout CSS ===== -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
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
                <li><a href="index.php">
                    <i class="fas fa-home"></i>
                    <span class="link-name">Dashboard</span>
                </a></li>
                <li><a href="viewFaculty.php">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span class="link-name">Faculty</span>
                </a></li>
                <li><a href="viewDepartment.php">
                    <i class="fas fa-building"></i>
                    <span class="link-name">Departments</span>
                </a></li>
                <li><a href="studentCourses.php">
                    <i class="fas fa-book-open"></i>
                    <span class="link-name">Courses</span>
                </a></li>
                <li><a href="viewlecture.php">
                    <i class="fas fa-user-tie"></i>
                    <span class="link-name">Lectures</span>
                </a></li>
                <li><a href="trackAttendance.php">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="link-name">Attendance</span>
                </a></li>
                <li>
                    <a href="#" class="toggle-submenu active" data-submenu="results-submenu">
                        <i class="fas fa-graduation-cap"></i>
                        <span class="link-name">Results</span>
                        <i class="fas fa-chevron-right toggle-icon"></i>
                    </a>
                    <ul class="results-submenu submenu" style="display: none;">
                        <li><a href="studentResult.php">Semester Results</a></li>
                        <li><a href="viewFinalResult.php">Final Results</a></li>
                        <li><a href="gradingCriteria.php">Grading Criteria</a></li>
                    </ul>
                </li>
                
            </ul>
            
            <ul class="logout-mode">
                <li><a href="../users.php">
                    <i class="fas fa-chevron-circle-left"></i>
                    <span class="link-name">Back</span>
                </a></li>
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
    <img src="<?php echo htmlspecialchars($profileImagePath); ?>" alt="Profile Image">
</a>
        </div>

        <div class="dash-content">
        <div class="animated fadeIn">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <strong class="card-title"><h3 align="center">Result</h3></strong>
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <div class="row">
                                        <!-- Level dropdown -->
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="level_id" class="control-label mb-1">Level</label>
                                                <select required name="level_id" class="custom-select form-control">
                                                    <option value="">--Select Level--</option>
                                                    <?php 
                                                    $query = mysqli_query($conn, "SELECT * FROM level");
                                                    while ($row = mysqli_fetch_array($query)) {
                                                        echo '<option value="'.$row['level_id'].'">'.$row['levelName'].'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <!-- Session dropdown -->
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="session_id" class="control-label mb-1">Session</label>
                                                <select required name="session_id" class="custom-select form-control">
                                                    <option value="">--Select Session--</option>
                                                    <?php 
                                                    $query = mysqli_query($conn, "SELECT * FROM session WHERE isActive = 1");
                                                    while ($row = mysqli_fetch_array($query)) {
                                                        echo '<option value="'.$row['session_id'].'">'.$row['sessionName'].'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <!-- Semester dropdown -->
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="semester_id" class="control-label mb-1">Semester</label>
                                                <select required name="semester_id" class="custom-select form-control">
                                                    <option value="">--Select Semester--</option>
                                                    <?php 
                                                    $query = mysqli_query($conn, "SELECT * FROM semester ORDER BY semesterName ASC");
                                                    while ($row = mysqli_fetch_array($query)) {
                                                        echo '<option value="'.$row['semester_id'].'">'.$row['semesterName'].'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <button type="submit" name="submit" class="btn btn-success">View</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <?php if(isset($_POST['submit'])): ?>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong class="card-title">
                                    <h3 align="center">
                                        <?= $rowLevel['levelName'] ?? '' ?> 
                                        <?= $rowSemester['semesterName'] ?? '' ?> Semester Result 
                                        <?= $rowSession['sessionName'] ?? '' ?> Session
                                    </h3>
                                </strong>
                            </div>
                            <div class="card-body">
                                <table class="table table-hover table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Course</th>
                                            <th>Course Code</th>
                                            <th>Unit</th>
                                            <th>Score</th>
                                            <th>Grade</th>
                                            <th>Grade Point</th>
                                            <th>Total Grade Point</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $ret = mysqli_query($conn, "SELECT r.*, c.courseTitle 
                                            FROM result r
                                            INNER JOIN course c ON c.courseCode = r.courseCode
                                            WHERE r.level_id = '$levelId' AND r.session_id = '$sessionId' 
                                            AND r.semester_id = '$semesterId' AND r.studentNo = '$studentNo'");

                                        $cnt = 1;
                                        $totalCourseUnit = $totalScoreGradePoint = 0;

                                        while ($row = mysqli_fetch_array($ret)) {
                                            $color = ($row['scoreLetterGrade'] == 'F') ? 'red' : '';
                                            echo "<tr bgcolor='{$color}'>";
                                            echo "<td>{$cnt}</td>";
                                            echo "<td>{$row['courseTitle']}</td>";
                                            echo "<td>{$row['courseCode']}</td>";
                                            echo "<td>{$row['courseUnit']}</td>";
                                            echo "<td>{$row['score']}</td>";
                                            echo "<td>{$row['scoreLetterGrade']}</td>";
                                            echo "<td>{$row['scoreGradePoint']}</td>";
                                            echo "<td>{$row['totalScoreGradePoint']}</td>";
                                            echo "</tr>";
                                            
                                            $cnt++;
                                            $totalCourseUnit += $row['courseUnit'];
                                            $totalScoreGradePoint += $row['totalScoreGradePoint'];
                                        }
                                        ?>
                                        <tr>
                                            <td colspan="3"></td>
                                            <td bgcolor="#F9D342"><?= $totalCourseUnit ?></td>
                                            <td colspan="3"></td>
                                            <td bgcolor="#F9D342"><?= $totalScoreGradePoint ?></td>
                                        </tr>                                                          
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div><!-- .animated -->
        </div>
    </section>
    <script src="../js/script.js"></script>
    <script>
        // Submenu toggles
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