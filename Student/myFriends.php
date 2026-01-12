<?php
session_start();
require '../php/config.php'; // Adjust the path to your config file as needed
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Check if the user is logged in
if (!isset($_SESSION['studentNo'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch user and profile data in one query
$studentNo = $_SESSION['studentNo'];
$stmt = $conn->prepare("SELECT student.student_id, student.firstName, student.lastName, student.email, profile.image 
    FROM student 
    LEFT JOIN profile ON student.student_id = profile.student_id 
    WHERE student.studentNo = ?");
$stmt->bind_param("s", $studentNo);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $studentId = $row['student_id'];
    $firstName = htmlspecialchars($row['firstName']);
    $lastName = htmlspecialchars($row['lastName']);
    $email = htmlspecialchars($row['email']);
    $profileImage = htmlspecialchars($row['image']);
    
    // Redirect to profile setup if the profile is not set
    if (is_null($profileImage)) {
        header('Location: profile.php');
        exit;
    }
} else {
    // If no user data found, force logout
    header('Location: ../logout.php');
    exit;
}

// Determine view
$view = isset($_GET['view']) ? $_GET['view'] : 'friends'; // Default to showing friends

// Fetch data based on view
if ($view === 'requests') {
    // Fetch friend requests sent to the logged-in user
    $requestQuery = "
        SELECT 
            student.student_id AS student_id,
            student.firstName AS firstName,
            student.lastName AS lastName,
            profile.image AS profile_image
        FROM request
        JOIN student ON request.from_student_id = student.student_id
        LEFT JOIN profile ON student.student_id = profile.student_id
        WHERE request.to_student_id = ? AND request.status = 'pending'
    ";
    $stmt = $conn->prepare($requestQuery);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $dataResult = $stmt->get_result();
} else {
    // Fetch all students except the logged-in student
    $studentsQuery = "
        SELECT 
            student.student_id AS student_id,
            student.firstName AS firstName,
            student.lastName AS lastName,
            profile.image AS profile_image,
            (SELECT status FROM request WHERE from_student_id = ? AND to_student_id = student.student_id) AS request_status
        FROM student
        LEFT JOIN profile ON student.student_id = profile.student_id
        WHERE student.student_id != ?
    ";
    $stmt = $conn->prepare($studentsQuery);
    $stmt->bind_param("ii", $studentId, $studentId);
    $stmt->execute();
    $dataResult = $stmt->get_result();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="Buddy-icon" href="../images/12.png">
    <title>Buddy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="./styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <style>
        /* Basic button styling */
button {
    background-color: #007bff; /* Default background color */
    color: white; /* Text color */
    border: none; /* Remove border */
    padding: 10px 20px; /* Padding */
    font-size: 16px; /* Font size */
    cursor: pointer; /* Pointer cursor on hover */
    border-radius: 5px; /* Rounded corners */
    transition: background-color 0.3s ease, transform 0.2s ease; /* Transition effect */
}

/* Hover effect */
button:hover {
    background-color: #0056b3; /* Darker background color on hover */
    transform: scale(1.05); /* Slightly enlarge the button */
}

/* Optional: Add a focus effect */
button:focus {
    outline: none; /* Remove default focus outline */
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Add a shadow */
}
.back {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px; /* Set the width of the button */
    height: 50px; /* Set the height of the button */
    background-color: #94827F; /* Background color for the button */
    color: white; /* Color of the icon */
    border-radius: 50%; /* Make the button circular */
    cursor: pointer; /* Change cursor to pointer on hover */
    transition: background-color 0.3s ease, transform 0.2s ease; /* Add transitions for effects */
    margin: 10px; /* Add some margin around the button */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add shadow for better visibility */
}

/* Hover effect */
.back:hover {
    background-color: #A99B99; /* Darker background color on hover */
    transform: scale(1.1); /* Slightly enlarge the button */
}

/* Focus effect */
.back:focus {
    outline: none; /* Remove default focus outline */
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.5); /* Add shadow on focus */
}
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
</head>
<body>
<nav>
<div class="container">
        <img src="../images/12.png" alt="Buddy Logo" style="width: 50px; margin-left: 100px;">
        <div class="search-bar">
            <i class="uil uil-search"></i>
            <input type="search" placeholder="Search for Students, Groups and Pages" />
        </div>
        <label class="btn btn-primary" for="create-post" id="create-lg1">Create Post</label>

        <a href="../php/logout.php?logout_id=<?php echo urlencode($_SESSION['studentNo']); ?>" class="logout-btn">Log Out</a>
        
        <a href="StudentProfile.php">
            <div class="profile-pic" id="my-profile-picture" style="height: 40px; width: 40px; background-color: #b9b7c1; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                <?php
                    if ($profileImage) {
                        echo '<img src="../php/images/' . htmlspecialchars($profileImage) . '" alt="Profile Picture" style="height: 100%; width: 100%; object-fit: cover; border-radius: 50%;">';
                    } else {
                        echo '<i class="fas fa-user" style="font-size: 30px; color: #94827F;"></i>';
                    }
                ?>
            </div>
        </a>
    </div>
</nav>

    <div class="profile-container" style="margin-top: 50px;">
        <div class="profile-info">
        <div class="info-col" style="max-width: 70px;">
        <a href="../users.php" class="back">
    <i class="fas fa-times"></i>
</a>
</div>

            <div class="post-col"  style="margin-right: 80px;">
            <div class="gps">
    <?php while ($student = $dataResult->fetch_assoc()): ?>
        <?php if ($student['request_status'] === 'accepted'): // Only show accepted friends ?>
        <div class="gp" style="border-radius: 10%;">
            <div class="gp-img" style="border-radius: 50%;">
                <?php if ($student['profile_image']): ?>
                    <img src="../php/images/<?php echo htmlspecialchars($student['profile_image']); ?>" alt="Student Image">
                <?php else: ?>
                    <img src="images/default-profile.png" alt="Default Profile Image">
                <?php endif; ?>
            </div>
            <div class="gp-details">
                <span><?php echo htmlspecialchars($student['firstName']) . ' ' . htmlspecialchars($student['lastName']); ?></span>
                <!-- Display the 'Friends' button for accepted requests -->
                <form action="../php/add_friend.php" method="post">
                    <input type="hidden" name="to_student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                    <button type="submit">Friends</button>
                </form>
                <form action="FriendProfile.php" method="get" style="display: inline;">
                    <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                    <button type="submit" style="margin-top: 10px;">View Friend</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    <?php endwhile; ?>
</div>

            </div>
            
        </div>
    </div>
    <script>
        document.querySelectorAll('.accept-form, .decline-form').forEach(function(form) {
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        const requestId = form.getAttribute('data-request-id');
        const action = form.querySelector('input[name="action"]').value;
        const button = form.querySelector(`.${action}-button`);

        fetch('', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                button.textContent = action.charAt(0).toUpperCase() + action.slice(1) + 'ed';
                setTimeout(function() {
                    const requestDiv = document.getElementById(`request-${requestId}`);
                    if (action === 'decline' && requestDiv) {
                        requestDiv.remove();
                    }
                    location.reload();
                }, 3000);
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
        document.getElementById('view-requests').addEventListener('click', function() {
    fetch('index.php?view=requests')
        .then(response => response.text())
        .then(data => {
            document.querySelector('.gps').innerHTML = data;
        });
});

document.getElementById('view-friends').addEventListener('click', function() {
    fetch('index.php?view=friends')
        .then(response => response.text())
        .then(data => {
            document.querySelector('.gps').innerHTML = data;
        });
});

    </script>    
</body>
</html>