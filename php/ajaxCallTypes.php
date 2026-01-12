<?php
include('config.php');

$tid = intval($_GET['tid']);

if ($type == '2') { // By Single Date
    if (!empty($dateTaken)) {
        $query .= " AND attendance.dateTimeTaken = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $studentNo, $course_id, $level_id, $dateTaken);
    } else {
        $statusMsg = "<div class='alert alert-danger' role='alert'>Please select a date for By Single Date type!</div>";
    }
} elseif ($type == '3') { // By Date Range rsa@9795
    if (!empty($dateTaken) && !empty($toDate)) {
        $query .= " AND attendance.dateTimeTaken BETWEEN ? AND ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("siii", $studentNo, $course_id, $level_id, $dateTaken, $toDate);
    } else {
        $statusMsg = "<div class='alert alert-danger' role='alert'>Please select both start and end dates for By Date Range type!</div>";
    }
} else { // All
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $studentNo, $course_id, $level_id);
}

if (empty($statusMsg)) {
    if ($stmt->execute()) {
        $results = $stmt->get_result();
    } else {
        $statusMsg = "<div class='alert alert-danger' role='alert'>Error: " . $stmt->error . "</div>";
    }
}

?>