<?php

    include('config.php');

    $faculty_id = intval($_GET['faculty_id']);//gradeId

        $queryss=mysqli_query($conn,"select * from department where faculty_id=".$faculty_id." ORDER BY departmentName ASC");                        
        $countt = mysqli_num_rows($queryss);

        if($countt > 0){                       
        echo '<label for="select" class=" form-control-label">Department</label>
        <select required name="department_id" class="custom-select form-control">';
        echo'<option value="">--Select Department--</option>';
        while ($row = mysqli_fetch_array($queryss)) {
        echo'<option value="'.$row['department_id'].'" >'.$row['departmentName'].'</option>';
        }
        echo '</select>';
        }

?>

