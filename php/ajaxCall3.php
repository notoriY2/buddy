<?php
include('config.php');

$department_id = intval($_GET['department_id']);

// rsa@9795 Prepare the SQL statement with a JOIN to include department_id from course_assignment
$stmt = $conn->prepare("
    SELECT course.course_id, course.courseTitle
    FROM course
    INNER JOIN course_assignment ON course.course_id = course_assignment.course_id
    WHERE course_assignment.department_id = ?
    ORDER BY course.courseTitle ASC
");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<label for="select" class="form-control-label">Course</label>
    <select name="course_id" class="custom-select form-control">';
    echo '<option value="">--Select Course--</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($row['course_id']) . '">' . htmlspecialchars($row['courseTitle']) . '</option>';
    }
    echo '</select>';
} else {
    echo '<select class="custom-select form-control"><option value="">No courses available</option></select>';
}
?>