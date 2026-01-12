<nav>
    <div class="container">
        <img src="images/12.png" alt="Buddy Logo" style="width: 50px; margin-left: 100px;">
        <div class="search-bar">
    <i class="uil uil-search"></i>
    <input type="search" id="search-input" placeholder="Search for Students, Groups and Pages" />
    <div id="search-results" class="search-results"></div>
</div>
        <label class="btn btn-primary" for="create-post" id="create-lg1">Create Post</label>
        <a href="php/logout.php?logout_id=<?php echo urlencode($_SESSION['studentNo']); ?>" class="logout-btn">Log Out</a>
        
        <a href="Student/StudentProfile.php?student_id=<?php echo urlencode($studentId); ?>">
 <!-- Add your desired link here -->
            <div class="profile-pic" id="my-profile-picture" style="height: 40px; width: 40px; background-color: #b9b7c1; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                <?php 
                    // Query to get the profile image from the profile table
                    $sql = mysqli_query($conn, "SELECT p.image FROM profile p INNER JOIN student s ON p.student_id = s.student_id WHERE s.studentNo = {$_SESSION['studentNo']}");
                    if(mysqli_num_rows($sql) > 0){
                        $row = mysqli_fetch_assoc($sql);
                        $profileImage = !empty($row['image']) && file_exists("php/images/{$row['image']}") ? $row['image'] : null;
                    }
                    if ($profileImage) {
                        echo '<img src="php/images/' . htmlspecialchars($profileImage) . '" alt="Profile Picture" style="height: 100%; width: 100%; object-fit: cover; border-radius: 50%;">';
                    } else {
                        echo '<i class="fas fa-user" style="font-size: 30px; color: #94827F;"></i>'; // Smaller red icon
                    }
                ?>
            </div>
        </a>
    </div>
</nav>
