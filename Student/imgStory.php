<div class="profile-container" style="margin-top: 50px;">
<div class="profile-info">
            <div class="info-col">
                <div class="profile-intro">
                    <h3>Your Story</h3>
                    <hr>
                    <form class="create-group-form" action="" method="post" enctype="multipart/form-data">
                        <!-- Hidden file input -->
<input type="file" name="storyImage" accept="image/jpg, image/png, image/jpeg" id="add-story" style="display: none;">
<!-- Hidden input to hold the cropped image data -->
<input type="hidden" id="croppedImageData" name="croppedImageData">


                        <!-- Your existing form content -->
                        <div class="profile-header" style="display: flex; align-items: center;">
                        <div class="profile-pic" style="height: 80px; width: 80px; background-color: #b9b7c1; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                            <?php
                            if ($profileImage) {
                                echo '<img src="../php/images/' . htmlspecialchars($profileImage) . '" alt="Profile Picture" style="height: 100%; width: 100%; object-fit: cover; border-radius: 50%;">';
                            } else {
                                echo '<i class="fas fa-user" style="font-size: 50px; color: #94827F;"></i>';
                            }
                            ?>
                        </div>
                        <div class="profile-name" style="margin-left: 20px;">
                            <h2><?php echo $firstName . ' ' . $lastName; ?></h2>
                        </div>
                    </div>
                        <hr>
                        <button type="submit" style="background: #E1DEED">Discard</button>
                        <button type="submit">Share</button>
                    </form>
                </div>
            </div>
            <div class="preview-box">
                <h2>Preview</h2>
                <div class="smartphone-crop-box">
                    <div class="smartphone-screen">
                        <?php if (!empty($uploadedImgPath)): ?>
                            <img id="preview-image" src="<?= htmlspecialchars($uploadedImgPath) ?>" alt="Selected Image">
                        <?php else: ?>
                            <p></p>
                        <?php endif; ?>
                        <div class="overlay-box">
                            <div class="clear-box"></div>
                        </div>
                    </div>
                </div>
                
                <div class="slider-container">
                    <input type="range" id="size-slider" name="size-slider" min="1" max="100" value="50">
                </div>
            </div>
        </div>
    </div>
