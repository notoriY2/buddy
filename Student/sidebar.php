<div class="sidebar">
    <a href="users.php" class="menu-item active" style="color: black;">
    <span><i class="fa fa-home" aria-hidden="true"></i></span>
    <h3>Home</h3>
</a>
<a href="Student/friends.php" class="menu-item" style="color: black;">
<i class="fas fa-user-friends"></i>
    <h3>Friends</h3>
</a>
<a class="menu-item" id="notifications">
    <span>
        <i class="fa fa-bell" aria-hidden="true">
            <?php if ($notifications_count > 0): ?>
                <small class="notification-count"><?php echo $notifications_count > 9 ? '9+' : $notifications_count; ?></small>
            <?php endif; ?>
        </i>
    </span>
    <h3>Notifications</h3>
    <div class="notifications-popup">
        <?php if ($notifications_count > 0): ?>
            <?php while ($notification = $notifications_result->fetch_assoc()): ?>
                <div class="notification-item">
                    <div class="profile-pic">
                        <img src="<?php echo htmlspecialchars($notification['image'] ? "php/images/" . $notification['image'] : 'php/images/default.png'); ?>" alt="Profile Picture">
                    </div>
                    <div class="notification-body">
                        <b><?php echo htmlspecialchars($notification['firstName'] . ' ' . $notification['lastName']); ?></b>
                        <?php echo htmlspecialchars($notification['message']); ?>
                        <small class="text-muted"><?php echo htmlspecialchars(time_elapsed_string($notification['created_at'])); ?></small>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No new notifications</p>
        <?php endif; ?>
    </div>
</a>


    <a class="menu-item" id="messages-notifications">
        <i class="fa fa-envelope" aria-hidden="true"></i>
        <h3>Messages</h3>
    </a>
    <a href="Student/Groups.php" class="menu-item" style="color: black;">
    <span><i class="fa fa-users" aria-hidden="true"></i></span>
    <h3>Groups</h3>
</a>
<a href="Student/Pages.php" class="menu-item" style="color: black;">
    <span><i class="fa fa-paperclip" aria-hidden="true"></i></span>
    <h3>Pages</h3>
</a>
    <a href="Student/index.php" class="menu-item" style="color: black;">
    <span><i class="fa fa-pencil" aria-hidden="true"></i></span>
    <h3>Academics Management</h3>
</a>
    <a class="menu-item" id="theme">
        <span><i class="fa fa-paint-brush" aria-hidden="true"></i></span>
        <h3>Theme</h3>
    </a>

    <label class="btn btn-primary" for="create-post" id="create-lg">Create Post</label>
</div>