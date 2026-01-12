<?php
// Include the database connection
require_once('../php/config.php');

// Start the session
session_start();

// Ensure the student is logged in
if (!isset($_SESSION['studentNo'])) {
    header("Location: login.php");
    exit();
}

// Get studentNo from session
$studentNo = $_SESSION['studentNo'];

// Fetch student details, including profile image
$stmt = $conn->prepare("
    SELECT 
        student.student_id, 
        student.firstName, 
        student.lastName, 
        student.email, 
        profile.image 
    FROM 
        student 
    LEFT JOIN 
        profile ON student.student_id = profile.student_id 
    WHERE 
        student.studentNo = ?");
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
    
    // Set a default image if profile image is not set rsa@9795
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

// Fetch the lectures assigned to the same faculty and department as the logged-in student
$sql = "
SELECT DISTINCT
    staff.staff_id,
    staff.staffNo,        -- Include staffNo in the selection
    staff.name,
    staff.email,
    staff.phoneNo,
    stafftype.staffType_id
FROM 
    student
INNER JOIN 
    assignedlecture ON student.faculty_id = assignedlecture.faculty_id 
    AND student.department_id = assignedlecture.department_id
INNER JOIN 
    staff ON assignedlecture.staff_id = staff.staff_id
INNER JOIN 
    stafftype ON staff.staffType_id = stafftype.staffType_id
WHERE 
    student.studentNo = '{$studentNo}'
    AND stafftype.staffType_id = '2'
ORDER BY 
    staff.name;
";

$lectureResult = $conn->query($sql);

if ($lectureResult->num_rows > 0) {
    // Fetch all lecture data
    $lectures = $lectureResult->fetch_all(MYSQLI_ASSOC);
} else {
    $lectures = [];
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
                <li><a href="viewlecture.php" class="active">
                    <i class="fas fa-user-tie"></i>
                    <span class="link-name">Lectures</span>
                </a></li>
                <li><a href="trackAttendance.php">
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
                <li>
                    <a href="../users.php">
                        <i class="fas fa-chevron-circle-left"></i>
                        <span class="link-name">Back</span>
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
    <img src="<?php echo htmlspecialchars($profileImagePath); ?>" alt="Profile Image">
</a>
        </div>

        <div class="dash-content">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title"><h2 align="center">Lectures</h2></strong>
                    </div>
                    <div class="card-body">
                        <table id="bootstrap-data-table" class="table table-hover table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Staff No</th>
                                    <th>Full Name</th>
                                    <th>Email Address</th>
                                    <th>Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (count($lectures) > 0) {
                                    $counter = 1; // To display the row number
                                    foreach ($lectures as $lecture) {
                                        echo "<tr>";
                                        echo "<td>" . $counter . "</td>";
                                        echo "<td>" . htmlspecialchars($lecture['staffNo']) . "</td>";
                                        echo "<td>" . htmlspecialchars($lecture['name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($lecture['email']) . "</td>";
                                        echo "<td>" . htmlspecialchars($lecture['phoneNo']) . "</td>";
                                        echo "</tr>";
                                        $counter++;
                                    }
                                } else {
                                    echo "<tr><td colspan='5'>No lectures found for this faculty and department.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- end of datatable -->
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