<?php
include('../php/config.php');
include('../php/session.php');

error_reporting(E_ALL); // Report all errors
ini_set('display_errors', 1); // Display errors on the page

// Initialize variables for alert styles and messages
$alertStyle = isset($_SESSION['alertStyle']) ? $_SESSION['alertStyle'] : '';
$statusMsg = isset($_SESSION['statusMsg']) ? $_SESSION['statusMsg'] : '';

// Clear session messages after they are used
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
// Check if the form is submitted
if (isset($_POST['submit'])) {
    $sessionName = mysqli_real_escape_string($conn, $_POST['sessionName']);

    // Check if the session already exists
    $query = mysqli_query($conn, "SELECT * FROM session WHERE sessionName = '$sessionName'");
    
    if (mysqli_num_rows($query) > 0) {
        $alertStyle = "alert alert-danger";
        $statusMsg = "This Session already exists!";
    } else {
        // Insert new session
        $query = mysqli_query($conn, "INSERT INTO session (sessionName) VALUES ('$sessionName')");
        if ($query) {
            $alertStyle = "alert alert-success";
            $statusMsg = "Session Added Successfully!";
        } else {
            $alertStyle = "alert alert-danger";
            $statusMsg = "An error occurred: " . mysqli_error($conn);
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
<html class="no-js" lang="">
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
</head>
<body>
    <!-- Left Panel -->
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
                <li><a href="createSession.php" class="active">
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

            <div id="right-panel" class="right-panel">
                <header id="header" class="header">
                    <div class="top">
                        <i class="uil uil-bars sidebar-toggle"></i>
                        <div class="search-box">
                            <i class="uil uil-search"></i>
                            <input type="text" placeholder="Search here...">
                        </div>
                        <img src="../images/1.jpg" alt="">
                    </div>
                </header>

                <div class="content">
                <div class="animated fadeIn">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <strong class="card-title"><h2 align="center">Add New Session</h2></strong>
                                    </div>
                                    <div class="card-body">
                                        <div id="pay-invoice">
                                            <div class="card-body">
                                            <div class="alert <?php echo isset($alertStyle) ? $alertStyle : 'alert-info'; ?>" role="alert">
                                                <?php echo $statusMsg;?>
                                            </div>
                                                <form method="POST" action="">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="sessionName" class="control-label mb-1">Session</label>
                                                                <input id="sessionName" name="sessionName" type="text" class="form-control cc-exp" placeholder="Session Name">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button type="submit" name="submit" class="btn btn-success">Add Session</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <strong class="card-title"><h2 align="center">All Sessions</h2></strong>
                                    </div>
                                    <div class="card-body">
                                        <table id="bootstrap-data-table" class="table table-hover table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Session</th>
                                                    <th>Status</th>
                                                    <th>Make Active</th>
                                                    <th>Edit</th>
                                                    <th>Delete</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $ret = mysqli_query($conn, "SELECT * FROM session");
                                                $cnt = 1;
                                                while ($row = mysqli_fetch_array($ret)) {
                                                ?>
                                                <tr>
                                                    <td><?php echo $cnt;?></td>
                                                    <td><?php echo $row['sessionName'];?></td>
                                                    <td><?php echo $row['isActive'] == 1 ? "Active" : "Inactive";?></td>
                                                    <td><a href="../php/activateSession.php?activate_id=<?php echo $row['session_id'];?>" title="Activate Session"><i class="fa fa-check fa-1x"></i></a></td>
                                                    <td><a href="editSession.php?edit_id=<?php echo $row['session_id'];?>" title="Edit Session Details"><i class="fa fa-edit fa-1x"></i></a></td>
                                                    <td><a onclick="return confirm('Are you sure you want to delete?')" href="../php/deleteSession.php?del_id=<?php echo $row['session_id'];?>" title="Delete Session"><i class="fa fa-trash fa-1x"></i></a></td>
                                                </tr>
                                                <?php 
                                                $cnt++;
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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