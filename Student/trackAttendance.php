<?php
// Include the database connection
require_once('../php/config.php');

// Start the session
session_start();

$alertStyle = "";
$statusMsg = "";

// Ensure the student is logged in
if (!isset($_SESSION['studentNo'])) {
    header("Location: ../login.php");
    exit();
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
    exit();
}

// Fetch student details
$studentQuery = "SELECT level_id, faculty_id, department_id FROM student WHERE studentNo = '{$studentNo}'";
$studentResult = $conn->query($studentQuery);
$student = $studentResult->fetch_assoc();

// Fetch courses assigned to the student's faculty, level, and department
$courseQuery = "
SELECT c.course_id, c.courseTitle 
FROM course c
INNER JOIN course_assignment ca ON c.course_id = ca.course_id
WHERE c.level_id = {$student['level_id']}
AND ca.faculty_id = {$student['faculty_id']}
AND ca.department_id = {$student['department_id']}
";

$courseResult = $conn->query($courseQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_id'])) {
    $selectedCourseId = $_POST['course_id'];

    // Fetch attendance data for the selected course (Present = 1, Absent = 0)
    $attendanceQuery = "
    SELECT status, COUNT(*) as count
    FROM attendance
    WHERE course_id = {$selectedCourseId}
    GROUP BY status
    ";
    
    $attendanceResult = $conn->query($attendanceQuery);
    $attendanceData = [0 => 0, 1 => 0]; // 0 => Absent, 1 => Present

    // Prepare data for donut chart
    while ($row = $attendanceResult->fetch_assoc()) {
        $attendanceData[$row['status']] = $row['count'];
    }

    // Calculate the total attendance percentage
    $totalAttendance = $attendanceData[1] + $attendanceData[0];
    $attendancePercentage = $totalAttendance > 0 ? ($attendanceData[1] / $totalAttendance) * 100 : 0;

    // Fetch attendance trend data for the selected course
    $trendQuery = "
    SELECT DATE(dateTimeTaken) as date, COUNT(*) as count
    FROM attendance
    WHERE course_id = {$selectedCourseId}
    GROUP BY DATE(dateTimeTaken)
    ORDER BY DATE(dateTimeTaken)
    ";

    $trendResult = $conn->query($trendQuery);
    $attendanceTrendData = [];

    // Prepare data for line chart
    while ($row = $trendResult->fetch_assoc()) {
        $attendanceTrendData[] = $row;
    }
}
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
    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css">
    <!-- Iconscout CSS -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>



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
                <li><a href="trackAttendance.php" class="active">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="link-name">Attendance</span>
                </a></li>
                <li>
                    <a href="#" class="toggle-submenu" data-submenu="results-submenu">
                        <i class="fas fa-graduation-cap"></i>
                        <span class="link-name">Results</span>
                        <i class="fas fa-chevron-right toggle-icon"></i>
                    </a>
                    <ul class="results-submenu submenu" style="display: none;">
                        <li><a href="studentResult.php">Semester Results</a></li>
                        <li><a href="viewFinalResult.php">Final Results</a></li>
                        <li><a href="gradingCriteria.php">grading Criteria</a></li>
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
            <img src="<?php echo htmlspecialchars($profileImagePath); ?>" alt="Change Password">
        </a>
        </div>
    
        <div class="dash-content">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title"><h2 align="center">View Attendance</h2></strong>
                    </div>
                    <div class="card-body">
                        <div id="pay-invoice">
                            <div class="card-body">
                                <div class="<?php echo $alertStyle; ?>" role="alert"><?php echo $statusMsg; ?></div>
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="select" class="form-control-label">Select Course</label>
                                                <?php
                                                echo '<select required name="course_id" class="custom-select form-control">';
                                                echo '<option value="">--Select Course--</option>';
                                                if ($courseResult->num_rows > 0) {                       
                                                    while ($row = $courseResult->fetch_assoc()) {
                                                        echo '<option value="' . $row['course_id'] . '">' . $row['courseTitle'] . '</option>';
                                                    }
                                                }
                                                echo '</select>';
                                                ?>        
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" name="submit" class="btn btn-primary">Track</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div> <!-- .card -->
            </div><!--/.col-->
            <br><br>
            <?php if (isset($attendanceData)) { ?>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">
                            <h2 align="center">Track Attendance</h2>
                        </strong>
                    </div>
                    <div class="card-body">
                    <h2><?php echo number_format($attendancePercentage, 2); ?>%</h2>
    <!-- Attendance Donut Chart -->
    <div id="attendance-chart-container">
        <canvas id="attendanceDonutChart" width="400" height="200"></canvas>
    </div>
    <script>
        const ctxDonut = document.getElementById('attendanceDonutChart').getContext('2d');
        const attendanceData = <?php echo json_encode($attendanceData); ?>;

        const labelsDonut = ['Present', 'Absent'];
        const dataDonut = [attendanceData[1] || 0, attendanceData[0] || 0];

        const attendanceDonutChart = new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: labelsDonut,
                datasets: [{
                    label: 'Attendance Status',
                    data: dataDonut,
                    backgroundColor: ['green', 'red'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        enabled: true,
                    },
                    datalabels: {
                        formatter: (value, context) => {
                            const total = context.chart.data.datasets[0].data.reduce((acc, curr) => acc + curr, 0);
                            const percentage = (value / total * 100).toFixed(2);
                            return percentage + '%';
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 18
                        }
                    },
                    doughnutlabel: {
                        labels: [
                            {
                                text: '<?php echo number_format($attendancePercentage, 2); ?>%',
                                font: {
                                    size: 20,
                                    weight: 'bold'
                                }
                            }
                        ]
                    }
                }
            }
        });
    </script>
                    </div>
                </div> <!-- .card -->
            </div><!--/.col-->
            <?php } ?>
        </div>
    </div><!-- .animated -->
</div>
    </section>

    <!-- Scripts -->
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