<div class="popup chat-popup" id="chat-popup">
    <div class="popup-box chat-popup-box">
        <span class="close"><i class="fa fa-close"></i></span>
        <?php 
          session_start();
          include_once "php/config.php";
          if(!isset($_SESSION['unique_id'])){
            header("location: login.php");
          }
          $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
          $sql = mysqli_query($conn, "SELECT * FROM student WHERE student_id = {$user_id}");
          if(mysqli_num_rows($sql) > 0){
            $row = mysqli_fetch_assoc($sql);
          }else{
            header("location: users.php");
          }
        ?>
        <header>
            <a href="users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
            <img src="php/images/<?php echo $row['img']; ?>" alt="">
            <div class="details">
              <span><?php echo $row['fname']. " " . $row['lname'] ?></span>
              <p><?php echo $row['status']; ?></p>
            </div>
        </header>
        <div class="chat-box"></div>
        <form action="#" class="typing-area">
            <input type="text" class="incoming_id" name="incoming_id" value="<?php echo $user_id; ?>" hidden>
            <input type="text" name="message" class="input-field" placeholder="Type a message here..." autocomplete="off">
            <button><i class="fab fa-telegram-plane"></i></button>
        </form>
    </div>
</div>