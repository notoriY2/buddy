<?php

include('../php/config.php');

$deptId = intval($_GET['deptId']);

$queryss = mysqli_query($conn, "SELECT assignedstaff.dateAssigned, assignedstaff.staff_id, staff.staff_id, staff.name 
                                FROM assignedstaff 
                                INNER JOIN staff ON staff.staff_id = assignedstaff.staff_id 
                                INNER JOIN stafftype ON staff.staffType_id = stafftype.staffType_id 
                                WHERE department_id = '$deptId' 
                                AND stafftype.staffTypeName = 'Lecture'");
$countt = mysqli_num_rows($queryss);

if($countt > 0) {                       
    echo '<label for="select" class="form-control-label">Select Lecturer</label>
    <select required name="staff_id" class="custom-select form-control">';
    echo '<option value="">--Select Lecturer--</option>';
    while ($row = mysqli_fetch_array($queryss)) {
        echo '<option value="'.$row['staff_id'].'">'.$row['name'].'</option>';
    }
    echo '</select>';
}

?>
