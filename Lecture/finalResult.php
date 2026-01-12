
<?php

    include('../php/config.php');
    include('../php/session.php');
    include('../php/functions.php');

    if(isset($_GET['studentNo'])){

        $studentNo = $_GET['studentNo'];

        $stdQuery=mysqli_query($conn,"select * from student where studentNo = '$studentNo'");                        
        $rowStd = mysqli_fetch_array($stdQuery);

    
    }
    else{
        echo "<script type = \"text/javascript\">
        window.location = (\"computeCGPAResults.php\");
        </script>";
    }



//------------------------------------ COMPUTE CGPA -----------------------------------------------

if (isset($_POST['compute'])){

     $id = $_POST['Id']; //Array of Id's
     $N = count($id);

    $totalCourseUnit = $_POST['totalCourseUnit'];
    $totalScoreGradePoint = $_POST['totalScoreGradePoint'];
    $dateAdded = date("Y-m-d");

    $overAllCourseUnit = 0;
    $overAlltotalScoreGradePoint = 0;
    $cgpa = 0;

    for($i = 0; $i < $N; $i++)
    {
        $totalCourseUnit[$i]; //each totalCourseUnit
        $totalScoreGradePoint[$i]; // each totalScoreGradePoint

        $overAllCourseUnit += $totalCourseUnit[$i]; //adds up all the totalCourseUnits
        $overAlltotalScoreGradePoint += $totalScoreGradePoint[$i]; //adds up all the totalScoreGradePoint
      
    }

        $cgpa = round(($overAlltotalScoreGradePoint / $overAllCourseUnit), 2); //computes the student CGPA (Cumulative Grade Point Average) by dividing the overall course unit and the over score grade point
        $classOfDiploma = getClassOfDiploma($cgpa); //function to get the class of diploma for the CGPA

        $que=mysqli_query($conn,"select * from cgparesult where studentNo ='$studentNo'");
        $ret=mysqli_fetch_array($que);

        if($ret > 0){ //update the record if record exists

            $que=mysqli_query($conn,"update cgparesult set cgpa='$cgpa', classOfDiploma='$classOfDiploma' where studentNo='$studentNo'");

            if($que){
                
                 $alertStyle ="alert alert-success";
                 $statusMsg="CGPA Computed and Updated Successfully!";
            }
            else {

                $alertStyle ="alert alert-danger";
                $statusMsg="An Error Occurred!";
            }
        }
        else{ //insert new record

                $querys = mysqli_query($conn,"insert into cgparesult(studentNo,cgpa,classOfDiploma,dateAdded) 
                value('$studentNo','$cgpa','$classOfDiploma','$dateAdded')");

                if ($querys) {

                    $alertStyle ="alert alert-success";
                    $statusMsg="CGPA Computed Successfully!";
                    echo "<script type='text/javascript'>
            setTimeout(function() {
                window.location.href = 'computeCGPAResults.php';
            }, 5000); // Redirects after 3 seconds
          </script>";
                }
                else
                {
                    $alertStyle ="alert alert-danger";
                    $statusMsg="An error Occurred!";
                }
            }
    

        // echo $cgpa.'<br>';
        // echo $classOfDiploma.'<br>';
        // echo $overAllCourseUnit.'<br>';
        // echo $overAlltotalScoreGradePoint.'<br>';

   

       
   
}//end of POST

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
                                <strong class="card-title"><h4 align="center">Compute CGPA for <?php echo  $rowStd['firstName'].' '.$rowStd['lastName'].' '.$rowStd['otherName'];?></h></strong>
                            </div>
                            <form method="post">
                            <div class="card-body">
                             <div class="<?php if(isset($alertStyle)){echo $alertStyle;}?>" role="alert"><?php if(isset($statusMsg)){echo $statusMsg;}?></div>
                                <table class="table table-hover table-striped table-bordered">
                                       <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>FullName</th>
                                            <th>Student No</th>
                                            <th>Level</th>
                                            <th>Semester</th>
                                            <th>Session</th>
                                            <th>Department</th>
                                            <th>Total Course Unit</th>
                                            <th>Total Score Grade Point</th>
                                            <th>GPA</th>
                                            <th>Class Of Diploma</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                      
                            <?php
                $ret=mysqli_query($conn,"SELECT level.levelName,faculty.facultyName,department.departmentName,semester.semesterName,
                session.sessionName,student.firstName,student.lastName,student.studentNo, finalresult.totalCourseUnit,finalresult.totalScoreGradePoint,
                finalresult.gpa,finalresult.classOfDiploma,finalresult.dateAdded,finalresult.finalResult_id
                from finalresult 
                INNER JOIN level ON level.level_id = finalresult.level_id
                INNER JOIN semester ON semester.semester_id = finalresult.semester_id
                INNER JOIN session ON session.session_id = finalresult.session_id
                INNER JOIN student ON student.studentNo = finalresult.studentNo
                INNER JOIN faculty ON faculty.faculty_id = student.faculty_id
                INNER JOIN department ON department.department_id = student.department_id
                where finalresult.studentNo ='$studentNo'");

                $cnt=1;
                while ($row=mysqli_fetch_array($ret)) {
                ?>
                <tr>
                <td><?php echo $cnt;?></td>
                <td><?php  echo $row['firstName'].' '.$row['lastName'];?></td>
                <td><?php  echo $row['studentNo'];?></td>
                <td><?php  echo $row['levelName'];?></td>
                <td><?php  echo $row['semesterName'];?></td>
                <td><?php  echo $row['sessionName'];?></td> 
                <td><?php  echo $row['departmentName'];?></td>
                <td><?php  echo $row['totalCourseUnit'];?></td>
                <td><?php  echo $row['totalScoreGradePoint'];?></td>
                <td><?php  echo $row['gpa'];?></td>
                <td><?php  echo $row['classOfDiploma'];?></td>
                <td><?php  echo $row['dateAdded'];?></td>
                <input id="" value="<?php echo isset($row['finalResult_id']) ? $row['finalResult_id'] : ''; ?>" name="Id[]" type="hidden" class="form-control">
                <input id="" value="<?php echo $row['totalCourseUnit'];?>" name="totalCourseUnit[]"  type="hidden" class="form-control" >
                <input id="" value="<?php echo $row['totalScoreGradePoint'];?>" name="totalScoreGradePoint[]"  type="hidden" class="form-control" >
                </tr>
                <?php 
                $cnt=$cnt+1;
                }?>
                                                            
                </tbody>
            </table>
            <button type="submit" name="compute" class="btn btn-success">Compute CGPA</button>
            </form>
        </div>
    </div>
</div>
                    
                <!-- end of datatable -->

            </div>
        </div><!-- .animated -->
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