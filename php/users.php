<?php 
session_start();
include_once "config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

$outgoing_id = $_SESSION['studentNo'];

// Fetch the student ID of the logged-in user based on studentNo
$studentQuery = $conn->prepare("SELECT student_id FROM student WHERE studentNo = ?");
$studentQuery->bind_param("s", $outgoing_id);
$studentQuery->execute();
$studentResult = $studentQuery->get_result();
$studentRow = $studentResult->fetch_assoc();
$studentId = $studentRow['student_id'];

// Fetch friend IDs
$friendStmt = $conn->prepare("
    SELECT CASE 
        WHEN from_student_id = ? THEN to_student_id 
        ELSE from_student_id 
    END AS friend_id
    FROM request 
    WHERE (from_student_id = ? OR to_student_id = ?) AND status = 'accepted'
");
$friendStmt->bind_param("iii", $studentId, $studentId, $studentId);
$friendStmt->execute();
$friendsResult = $friendStmt->get_result();

// Fetch all friend IDs and store them in an array
$friends = [];
while ($friendRow = $friendsResult->fetch_assoc()) {
    $friends[] = $friendRow['friend_id'];
}

// Check if there are friends to avoid SQL error
$friendsList = !empty($friends) ? implode(',', $friends) : '0'; // Handle the case with no friends

// Modify the query to only fetch friends of the logged-in user
$sql = "SELECT * FROM student WHERE NOT studentNo = {$outgoing_id} AND student_id IN ({$friendsList}) ORDER BY student_id DESC";
$query = mysqli_query($conn, $sql);
$output = "";

if (mysqli_num_rows($query) == 0) {
    $output .= "No members are available to chat";
} elseif (mysqli_num_rows($query) > 0) {
    include_once "data.php";  // do not remove this.
}

echo $output;
?>