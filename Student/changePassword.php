<?php
include('../php/config.php');
include('../php/session.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if messages are set in the session
$alertStyle = $_SESSION['alertStyle'] ?? "";
$statusMsg = $_SESSION['statusMsg'] ?? "";

// Clear messages from session after displaying
unset($_SESSION['alertStyle']);
unset($_SESSION['statusMsg']);

// Ensure the student is logged in
if (!isset($_SESSION['studentNo'])) {
    header("Location: ../login.php");
    exit();
}

$studentNo = $_SESSION['studentNo'];
$stmt = $conn->prepare("SELECT student.student_id, student.firstName, student.lastName, student.email, profile.image 
                        FROM student 
                        LEFT JOIN profile ON student.student_id = profile.student_id 
                        WHERE student.studentNo = ?");
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
    $defaultImage = 'default.png';
    $profileImagePath = $profileImage ? "../php/images/{$profileImage}" : "../php/images/{$defaultImage}";

    // Redirect to profile setup if the profile image is not set
    if (is_null($profileImage)) {
        header('Location: profile.php');
        exit();
    }
} else {
    // If no user data is found, force logout
    header('Location: ../logout.php');
    exit();
}

if (isset($_POST['submit'])) {
    $currentPassword = $_POST['currentpassword'];
    $newPassword = $_POST['newpassword'];

    // Ensure $studentNo is set and valid
    if ($studentNo) {
        // Retrieve the current password hash from the database
        $query = $conn->prepare("SELECT password FROM student WHERE student_id=?");
        $query->bind_param("i", $studentId);
        $query->execute();
        $queryResult = $query->get_result();
        $row = $queryResult->fetch_assoc();

        if ($row && password_verify($currentPassword, $row['password'])) {
            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update the password and set isPasswordChanged to 1
            $updateQuery = $conn->prepare("UPDATE student SET password=?, isPasswordChanged=1 WHERE student_id=?");
            $updateQuery->bind_param("si", $hashedNewPassword, $studentId);
            if ($updateQuery->execute()) {
                $_SESSION['alertStyle'] = "alert alert-success";
                $_SESSION['statusMsg'] = "Password changed successfully!";
            } else {
                $_SESSION['alertStyle'] = "alert alert-danger";
                $_SESSION['statusMsg'] = "An error occurred while changing the password!";
            }
        } else {
            $_SESSION['alertStyle'] = "alert alert-danger";
            $_SESSION['statusMsg'] = "Your current password is incorrect!";
        }
    } else {
        $_SESSION['alertStyle'] = "alert alert-danger";
        $_SESSION['statusMsg'] = "User is not logged in.";
    }

    // Redirect back to the previous page after processing
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
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
                <li><a href="index.php" class="active">
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
    <img src="<?php echo htmlspecialchars($profileImagePath); ?>" alt="Profile Image">
</a>
        </div>

        <div class="dash-content">
        <div class="animated fadeIn">

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Change Password</strong>
                        </div>
                        <div class="card-body">
                            <div id="pay-invoice">
                                <div class="card-body">
                                    <div class="<?php echo $alertStyle; ?>" role="alert"><?php echo $statusMsg; ?></div>
                                    <form method="post" action="changePassword.php">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label for="currentpassword" class="control-label mb-1">Current Password</label>
                                                    <input name="currentpassword" type="password" class="form-control cc-exp" required placeholder="Current Password">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label for="newpassword" class="control-label mb-1">New Password</label>
                                                    <input name="newpassword" type="password" class="form-control cc-exp" required placeholder="New Password">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" name="submit" class="btn btn-success">Change Password</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div> <!-- .card -->
                </div><!--/.col-->
            </div>
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
// Auto-hide alert message
const alertElement = document.querySelector('.alert');
    if (alertElement) {
        setTimeout(function () {
            alertElement.style.opacity = '0';
            setTimeout(function () {
                alertElement.style.display = 'none';
            }, 600);
        }, 5000);
    }
    </script>
</body>
</html>