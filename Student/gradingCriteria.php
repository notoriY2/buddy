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
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" type="Buddy-icon" href="../images/12.png">
    <title>Buddy</title>
    <meta name="description" content="Ela Admin - HTML5 Admin Template">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <!----======== CSS ======== -->
    <link rel="stylesheet" href="../css/style.css">
     
    <!----===== Iconscout CSS ===== -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>
<body>
    <nav>
        <div class="logo-name">
            <div class="logo-image">
                <img src="../images/12.png" alt="Logo">
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
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong class="card-title"><h3 align="center">GRADING CRITERIA</h3></strong>
                            </div>
                            <div class="card-body">
                                <table class="table table-hover table-striped table-bordered">
                                       <thead>
                                        <tr>
                                            <th>Class Of Diploma</th>
                                            <th>GPA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Distinction</td>
                                            <td>3.50 and Above</td>
                                        </tr>
                                        <tr>
                                            <td>Upper Credit</td>
                                            <td>3.00 - 3.49</td>
                                        </tr>
                                        <tr>
                                            <td>Lower Credit</td>
                                            <td>2.50 - 2.99</td>
                                        </tr>
                                        <tr>
                                            <td>Pass</td>
                                            <td>2.00 - 2.49</td>
                                        </tr>
                                        <tr>
                                            <td>Fail</td>
                                            <td>Below 2.00</td>
                                        </tr>
                                    </tbody>
                                </table>
<!-------------------------- FROM THE FINAL RESULT TABLE --------------------------->
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Score</th>
                                            <th>Grade Point Equivalent</th>
                                            <th>Letter Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>75 - 100</td>
                                            <td>4.00</td>
                                            <td>AA</td>
                                        </tr>
                                        <tr>
                                            <td>70 - 74</td>
                                            <td>3.50</td>
                                            <td>A</td>
                                        </tr>
                                        <tr>
                                            <td>65 - 69</td>
                                            <td>3.25</td>
                                            <td>AB</td>
                                        </tr>
                                        <tr>
                                            <td>60 - 64</td>
                                            <td>3.00</td>
                                            <td>B</td>
                                        </tr>
                                        <tr>
                                            <td>55 - 59</td>
                                            <td>2.75</td>
                                            <td>BC</td>
                                        </tr>
                                        <tr>
                                            <td>50 - 54</td>
                                            <td>2.50</td>
                                            <td>C</td>
                                        </tr>
                                        <tr>
                                            <td>45 - 49</td>
                                            <td>2.25</td>
                                            <td>CD</td>
                                        </tr>
                                        <tr>
                                            <td>40 - 44</td>
                                            <td>2.00</td>
                                            <td>D</td>
                                        </tr>
                                        <tr>
                                            <td>0 - 39</td>
                                            <td>0.00</td>
                                            <td>F</td>
                                        </tr>
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
    </script>
</body>
</html>