<?php
require_once 'config.php'; // Adjust the path if your database connection file is in a different directory

session_start();
$studentId = $_SESSION['studenNo']; // Get the logged-in student's ID from the session

// Fetch the next request from the database
$query = "SELECT r.from_student_id, s.firstName, s.lastName, s.image, COUNT(f.student_id2) AS mutual_friends
          FROM request r
          LEFT JOIN student s ON r.from_student_id = s.student_id
          LEFT JOIN friends f ON (r.from_student_id = f.student_id2 AND f.student_id1 = ?)
          WHERE r.to_student_id = ? AND r.status = 'pending'
          GROUP BY r.from_student_id
          LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $studentId, $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $request = $result->fetch_assoc();
    // Generate HTML for the request
    ?>
    <div class="request" id="request-<?php echo htmlspecialchars($request['from_student_id']); ?>">
        <div class="info">
            <div class="profile-pic">
                <img src="php/images/<?php echo htmlspecialchars($request['image'] ?? 'default.jpg'); ?>" alt="Profile Picture">
            </div>
            <div>
                <h5><?php echo htmlspecialchars($request['firstName'] . ' ' . $request['lastName']); ?></h5>
                <p class="text-muted"><?php echo (int)$request['mutual_friends']; ?> mutual friends</p>
            </div>
        </div>
        <div class="action">
            <form class="accept-form" data-request-id="<?php echo htmlspecialchars($request['from_student_id']); ?>">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($request['from_student_id']); ?>">
                <input type="hidden" name="action" value="accept">
                <button type="submit" class="btn btn-primary accept-button">Accept</button>
            </form>
            <form class="decline-form" data-request-id="<?php echo htmlspecialchars($request['from_student_id']); ?>">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($request['from_student_id']); ?>">
                <input type="hidden" name="action" value="decline">
                <button type="submit" class="btn btn-primary decline-button">Decline</button>
            </form>
        </div>
    </div>
    <?php
} else {
    echo ''; // No more requests
}

$stmt->close();
$conn->close();
?>