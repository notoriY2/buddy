<?php 
  session_start();
  include_once "php/config.php";
  if(!isset($_SESSION['studentNo'])){
    header("location: login.php");
    exit(); // Ensure no further code is executed
  }
?>
<?php include_once "header.php"; ?>
<?php include_once "Student/nav.php"; ?>
<body>
  <div class="wrapper" style="margin-top: 80px;">
    <section class="chat-area">
      <header>
        <?php 
          $student_id = mysqli_real_escape_string($conn, $_GET['student_id']);
          
          // Fetch student details along with profile image
          $sql = mysqli_query($conn, "SELECT student.firstName, student.lastName, student.status, profile.image 
                                      FROM student 
                                      LEFT JOIN profile ON student.student_id = profile.student_id 
                                      WHERE student.studentNo = {$student_id}");
          if(mysqli_num_rows($sql) > 0){
            $row = mysqli_fetch_assoc($sql);
            $imgSrc = !empty($row['image']) ? 'php/images/' . $row['image'] : 'default_icon'; // Use a default icon class
          } else {
            header("location: users.php");
            exit(); // Ensure no further code is executed
          }
        ?>
        <a href="users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
        <!-- Check if imgSrc is 'default_icon' to display the icon -->
        <?php if ($imgSrc === 'default_icon'): ?>
          <i class="fas fa-user" style="color: #94827F; font-size: 2em;"></i> <!-- Brown color and larger size -->
        <?php else: ?>
          <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="User profile image" style="width: 50px; height: 50px; border-radius: 50%;">
        <?php endif; ?>
        <div class="details">
          <span><?php echo htmlspecialchars($row['firstName'] . " " . $row['lastName']); ?></span>
          <p><?php echo htmlspecialchars($row['status']); ?></p>
        </div>
      </header>
      <div class="chat-box">
        <!-- Chat messages will be displayed here -->
      </div>
      <form action="#" class="typing-area">
        <input type="text" class="incoming_id" name="incoming_id" value="<?php echo htmlspecialchars($student_id); ?>" hidden>
        <input type="text" name="message" class="input-field" placeholder="Type a message here..." autocomplete="off">
        <button><i class="fab fa-telegram-plane"></i></button>
      </form>
    </section>
  </div>

  <script src="javascript/chat.js"></script>

  <style>
  .logout-btn {
    background-color: red;
    color: white;
    padding: 0.6rem 2rem;
    border-radius:2rem;
    cursor: pointer;
    border: none;
  }

  .logout-btn:hover {
    background-color: darkred;
  }
</style>
</body>
</html>
