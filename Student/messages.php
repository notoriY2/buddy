<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once "php/config.php";
if (!isset($_SESSION['studentNo'])) {
    header("location: login.php");
    exit(); // Ensure no further code is executed
}
?>
<?php include_once "header.php"; ?>
<body>
  <div class="wrapper" style="margin-top: 0vh;">
    <section class="users">
      <header>
        <div class="content">
          <?php 
            // Query to fetch student details and profile image
            $sql = mysqli_query($conn, "SELECT student.firstName, student.lastName, student.status, profile.image 
                                        FROM student 
                                        LEFT JOIN profile ON student.student_id = profile.student_id 
                                        WHERE student.studentNo = '{$_SESSION['studentNo']}'");
            if (mysqli_num_rows($sql) > 0) {
              $row = mysqli_fetch_assoc($sql);
              $imgSrc = !empty($row['image']) ? 'php/images/' . $row['image'] : null;
            }
          ?>
          <?php if ($imgSrc): ?>
            <img src="<?php echo $imgSrc; ?>" alt="User profile image">
          <?php else: ?>
            <i class="fas fa-user" style="color: #94827F; font-size: 2em;"></i>
          <?php endif; ?>
          <div class="details">
            <span><?php echo htmlspecialchars($row['firstName'] . " " . $row['lastName']); ?></span>
            <p><?php echo htmlspecialchars($row['status']); ?></p>
          </div>
        </div>
      </header>
      <div class="search">
        <span class="text">Select a user to start chat</span>
        <input type="text" placeholder="Enter name to search...">
        <button><i class="fas fa-search"></i></button>
      </div>
      <div class="users-list">
        <!-- Users list will be populated by JavaScript -->
      </div>
    </section>
  </div>

  <script src="javascript/users.js"></script>

</body>
</html>