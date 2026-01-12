<?php 
  session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="Buddy-icon" href="images/12.png">
    <title>Buddy - Menu</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .menu-content {
    text-align: center;
    margin: 50px auto;
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin: 30px 0;
}

.menu-item {
    background-color: #f4f4f4;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.menu-item:hover {
    transform: scale(1.05);
}

.menu-item h2 {
    color: #333;
    margin-bottom: 15px;
}

.menu-item p {
    color: #777;
    margin-bottom: 20px;
}

.menu-item a {
    text-decoration: none;
    color: #fff;
    background-color: #d752b1;
    padding: 10px 20px;
    border-radius: 5px;
    display: inline-block;
}

.menu-item a:hover {
    background-color: #c2469c;
}

    </style>
</head>
<body>
    <section>
        <header>
            <a href="#"><img src="images/12.png" class="logo"></a>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="menu.php" class="active">Menu</a></li>
                <li><a href="login.php">Student</a></li>
                <li><a href="login_form.php">Lecture/Admin</a></li>
            </ul>
        </header>
        <div class="menu-content">
            <h1>Explore Buddy's Features</h1>
            <div class="menu-grid">
                <div class="menu-item">
                    <h2>Dashboard</h2>
                    <p>Manage your academic life, track attendance, view your schedule, and more.</p>
                </div>
                <div class="menu-item">
                    <h2>Messages</h2>
                    <p>Stay connected with your peers and lecturers. Send and receive messages in real-time.</p>
                </div>
                <div class="menu-item">
                    <h2>Posts</h2>
                    <p>Share updates, collaborate on projects, and engage with the university community.</p>
                </div>
                <div class="menu-item">
                    <h2>Attendance</h2>
                    <p>Track your attendance in real-time, ensuring you meet university requirements.</p>
                </div>
                <div class="menu-item">
                    <h2>Profile</h2>
                    <p>Update your profile and keep your information up-to-date for a personalized experience.</p>
                </div>
                <div class="menu-item">
                    <h2>Settings</h2>
                    <p>Manage your account settings, privacy, and notifications.</p>
                </div>
            </div>
        </div>
    </section>
</body>
</html>