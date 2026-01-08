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
                <li><a href="socials.php" class="active">
                    <i class="fas fa-at"></i>
                    <span class="link-name">Socials</span>
                </a></li>
            </ul>
            <ul class="logout-mode">
                <li>
                    <a href="inde.php">
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
                <img src="../images/1.jpg" alt="Change Password">
            </a>
        </div>

        <div class="dash-content">
            <div class="animated fadeIn">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <strong class="card-title"><h2 align="center">View Student Posts and Stories</h2></strong>
                            </div>
                            <div class="card-body">
                                <form method="post" id="studentForm">
                                    <div class="form-group row mb-3">
                                        <div class="col-xl-6">
                                            <label class="form-control-label">Select Student<span class="text-danger ml-2">*</span></label>
                                            <select required name="admissionNumber" class="form-control mb-3" id="admissionNumber">
                                                <option value="">--Select Student--</option>
                                                <option value="12345">John Doe</option>
                                                <option value="67890">Jane Smith</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" name="view" class="btn btn-primary">View Posts and Stories</button>
                                </form>
                                <div id="postsStoriesContainer"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <strong class="card-title"><h2 align="center">All John Doe Posts</h2></strong>
                            </div>
                            <div class="card-body">
                                <table id="bootstrap-data-table" class="table table-hover table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Type</th>
                                            <th>Date Added</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>img</td>
                                            <td>2024-06-30</td>
                                            <td><a onclick="return confirm('Are you sure you want to delete?')" href="deleteAdmin.php?delid=STF001" title="Delete Admin"><i class="fa fa-trash fa-1x"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>page</td>
                                            <td>2024-06-28</td>
                                            <td><a onclick="return confirm('Are you sure you want to delete?')" href="deleteAdmin.php?delid=STF002" title="Delete Admin"><i class="fa fa-trash fa-1x"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>video</td>
                                            <td>2024-06-25</td>
                                            <td><a onclick="return confirm('Are you sure you want to delete?')" href="deleteAdmin.php?delid=STF003" title="Delete Admin"><i class="fa fa-trash fa-1x"></i></a></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="submit" name="view" class="btn btn-primary">Block / Unblock</button>
                            </div>
                        </div>
                    </div>
                <!-- end of datatable -->
            </div>
        </div><!-- .animated -->
    </section>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

            $('#studentForm').on('submit', function(e) {
                e.preventDefault();
                var admissionNumber = $('#admissionNumber').val();
                if (admissionNumber) {
                    $.ajax({
                        url: 'php/getPostsStories.php',
                        type: 'POST',
                        data: {admissionNumber: admissionNumber},
                        success: function(response) {
                            $('#postsStoriesContainer').php(response);
                        }
                    });
                }
            });

            $(document).on('click', '.delete-btn', function() {
                var postId = $(this).data('id');
                $.ajax({
                    url: 'php/deletePostStory.php',
                    type: 'POST',
                    data: {postId: postId},
                    success: function(response) {
                        if (response == 'success') {
                            alert('Post/Story deleted successfully.');
                            $('#studentForm').trigger('submit');
                        } else {
                            alert('Failed to delete the Post/Story.');
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
