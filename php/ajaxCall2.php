<?php
include('config.php');

$faculty_id = intval($_GET['faculty_id']);

$queryss = mysqli_query($conn, "SELECT * FROM department WHERE faculty_id='$faculty_id' ORDER BY departmentName ASC");
$countt = mysqli_num_rows($queryss);

if ($countt > 0) {
    echo '<label for="select" class="form-control-label">Department</label>
    <select name="department_id" onchange="showCourse(this.value)" class="custom-select form-control">';
    echo '<option value="">--Select Department--</option>';
    while ($row = mysqli_fetch_array($queryss)) {
        echo '<option value="' . $row['department_id'] . '">' . $row['departmentName'] . '</option>';
    }
    echo '</select>';
}
?>



