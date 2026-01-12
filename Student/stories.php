<style>
/* Apply hover effect to swiper-slide elements */
.story.swiper-slide {
    cursor: pointer;
    transition: transform 0.3s ease-in-out; /* Smooth transition for scaling */
}

.story.swiper-slide:hover {
    transform: scale(1.05); /* Scale up the element on hover */
}

</style>
<div class="stories">
    <div class="stories-wrapper swiper mySwiper">
        <div class="swiper-wrapper">

            <!-- "Add Your Story" Slide -->
            <div class="story swiper-slide">
                <img src="" alt="">
                <div class="profile-pic" id="my-profile-picture">
                    <?php 
                    $sql = mysqli_query($conn, "SELECT * FROM student WHERE studentNo = {$_SESSION['studentNo']}");
                    if(mysqli_num_rows($sql) > 0){
                        $row = mysqli_fetch_assoc($sql);
                    }
                    ?>
                    <img src="php/images/<?php echo htmlspecialchars($row['img']); ?>" alt="">
                </div>
                <label for="add-story" class="add-story" id="add-story-label">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                    <p>Add Your Story</p>
                </label>
            </div>

            <!-- User-Uploaded Stories -->
            <?php
            // Fetch only the latest story for each student
            $stmt = $conn->prepare("
                SELECT s.*, p.image AS profile_image, st.firstName, st.lastName
                FROM story s 
                JOIN student st ON s.student_id = st.student_id 
                LEFT JOIN profile p ON st.student_id = p.student_id
                WHERE s.expires_at > NOW() 
                AND s.created_at = (
                    SELECT MAX(created_at) 
                    FROM story 
                    WHERE student_id = s.student_id
                )
                ORDER BY s.created_at DESC
            ");
            $stmt->execute();
            $result = $stmt->get_result();

            while ($story = $result->fetch_assoc()) {
                $storyId = htmlspecialchars($story['story_id']); 
                $storyImage = htmlspecialchars($story['image']);
                $firstName = htmlspecialchars($story['firstName']);
                $lastName = htmlspecialchars($story['lastName']);
                $profileImage = !empty($story['profile_image']) ? 'php/images/' . htmlspecialchars($story['profile_image']) : 'images/default-profile.png';
            ?>
            <div class="story swiper-slide" onclick="viewStory('<?php echo $story['student_id']; ?>')">
                <img src="php/images/<?php echo $storyImage; ?>" alt="">
                <div class="profile-pic">
                    <img src="<?php echo $profileImage; ?>" alt="Profile Picture">
                </div>
                <p class="name">
                    <?php echo $story['student_id'] == $_SESSION['studentNo'] ? 'Your Story' : ($firstName . ' ' . $lastName); ?>
                </p>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<script>
document.getElementById('add-story-label').addEventListener('click', function() {
    window.location.href = 'Student/addStory.php';
});
function viewStory(studentId) {
    window.location.href = './Student/viewStory.php?student_id=' + studentId;
}
</script>