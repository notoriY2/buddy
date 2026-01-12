<?php 
  session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="Buddy-icon" href="images/12.png">
    <title>Buddy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <section>
        <div class="circle"></div>
        <header>
            <a href="#"><img src="images/12.png" class="logo"></a>
            <ul>
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="login.php">Student</a></li>
                <li><a href="login_form.php">Lecture/Admin</a></li>
            </ul>
        </header>
        <div class="content">
            <div class="textBox">
                <h2>It's not just Social Media<br>It's <span>Buddy</span></h2>
                <p>Buddy is your one-stop solution to manage your academic and social life seamlessly. Connect with your peers, track your attendance, stay updated with course materials, and much more, all in one place.</p>
                <h5>rsa@9795</h5>
                <a href="Menu.php">Learn More</a>
            </div>
            <div class="imgBox">
                <img src="images/12.png" class="Starbucks">
            </div>
        </div>
        <ul class="thumb">
            <li><img src="images/12.png" onclick="imgSlider('images/12.png');changeCircleColor('#A99B99')"></li>
            <li><img src="images/101.png" onclick="imgSlider('images/101.png');changeCircleColor('#333333')"></li>
            <li><img src="images/100.png" onclick="imgSlider('images/100.png');changeCircleColor('#d752b1')"></li>
        </ul>
    </section>

    <script type="text/javascript">
        function imgSlider(anything){
            document.querySelector('.Starbucks').src = anything;
        }
        function changeCircleColor(color){
            const circle = document.querySelector('.circle');
            circle.style.background = color;
        }
    </script>
</body>
</html>