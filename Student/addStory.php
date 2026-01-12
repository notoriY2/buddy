<?php
session_start();
require '../php/config.php';

// Check if the user is logged in
if (!isset($_SESSION['studentNo'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch user data
$studentNo = $_SESSION['studentNo'];
$stmt = $conn->prepare("
    SELECT s.student_id, s.firstName, s.lastName, p.image 
    FROM student s 
    LEFT JOIN profile p ON s.student_id = p.student_id 
    WHERE s.studentNo = ?
");
$stmt->bind_param("s", $studentNo);
$stmt->execute();
$result = $stmt->get_result();

$profileImage = null;
$firstName = '';
$lastName = '';

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $profileImage = !empty($row['image']) && file_exists("../php/images/{$row['image']}") ? $row['image'] : null;
    $firstName = htmlspecialchars($row['firstName']);
    $lastName = htmlspecialchars($row['lastName']);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $row['student_id'];
    $imagePath = null;

    // Handle image upload
    if (isset($_FILES['storyImage']) && $_FILES['storyImage']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../php/images/";
        $targetFile = $targetDir . basename($_FILES["storyImage"]["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = array('jpg', 'png', 'jpeg');

        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["storyImage"]["tmp_name"], $targetFile)) {
                $imagePath = htmlspecialchars(basename($_FILES["storyImage"]["name"]));
            }
        } else {
            echo "Sorry, only JPG, JPEG, and PNG files are allowed.";
            exit;
        }
    }

    // Handle cropping if provided
if (isset($_POST['croppedImageData']) && !empty($imagePath)) {
    $croppedData = json_decode($_POST['croppedImageData'], true);
    $imageFileType = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
    $srcImage = null;

    // Check the image file type and use the appropriate function
    if ($imageFileType == 'jpeg' || $imageFileType == 'jpg') {
        $srcImage = imagecreatefromjpeg($targetDir . $imagePath);
    } elseif ($imageFileType == 'png') {
        $srcImage = imagecreatefrompng($targetDir . $imagePath);
    } else {
        die("Unsupported image type.");
    }

    if ($srcImage !== false) {
        $imageWidth = imagesx($srcImage);
        $imageHeight = imagesy($srcImage);

        $cropX = $croppedData['cropX'] * $imageWidth;
        $cropY = $croppedData['cropY'] * $imageHeight;
        $cropWidth = $croppedData['cropWidth'] * $imageWidth;
        $cropHeight = $croppedData['cropHeight'] * $imageHeight;

        $croppedImage = imagecrop($srcImage, [
            'x' => $cropX,
            'y' => $cropY,
            'width' => $cropWidth,
            'height' => $cropHeight
        ]);

        if ($croppedImage !== false) {
            $croppedImagePath = $targetDir . "cropped_" . $imagePath;
            if ($imageFileType == 'jpeg' || $imageFileType == 'jpg') {
                imagejpeg($croppedImage, $croppedImagePath);
            } elseif ($imageFileType == 'png') {
                imagepng($croppedImage, $croppedImagePath);
            }
            imagedestroy($srcImage);
            imagedestroy($croppedImage);
            $imagePath = htmlspecialchars("cropped_" . $imagePath);
        } else {
            die("Image cropping failed.");
        }
    } else {
        die("Failed to create image from file.");
    }
}


    // Set expiry time for story (1 day from now)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));

    // Insert story into the database
    $stmt = $conn->prepare("INSERT INTO story (image, student_id, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $imagePath, $studentId, $expiresAt);

    if ($stmt->execute()) {
        header('Location: ../users.php');
        exit;
    } else {
        die("Failed to insert story into database: " . $stmt->error);
    }
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
    <link rel="stylesheet" href="https://unpkg.com/cropperjs/dist/cropper.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    
    <style>
        /* Ensure this only applies to the story section */
        .story-section {
            height: 100vh; /* Full height of the viewport */
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #94827F; /* Background color for the story section */
        }

        /* Keep the rest of your story option styling */
        .story-option {
            display: flex;
            gap: 50px;
        }

        /* Keep the card styling as it was */
        .card {
            width: 250px;
            height: 400px;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 18px;
            cursor: pointer;
            position: relative;
        }

        /* Background gradients */
        .photo-story {
            background: linear-gradient(135deg, #4E81FF, #A8C1FF);
        }

        .text-story {
            background: linear-gradient(135deg, #F74BFF, #FEC8FF);
        }

        /* Icon styling */
        .card i {
            font-size: 60px;
            margin-bottom: 20px;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 50%;
            padding: 20px;
        }

        /* Hover effect */
        .card:hover {
            transform: scale(1.05);
            transition: transform 0.3s ease-in-out;
        }
         /* Add this to your existing CSS file or in a <style> tag in the HTML */
.add-text-btn {
    display: inline-flex; /* Makes the icon and text inline */
    align-items: center; /* Vertically aligns the items */
    text-decoration: none; /* Removes the underline */
    color: inherit; /* Inherits the color from the parent */
    padding: 10px 15px; /* Adds some padding for a button-like feel */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    transition: background-color 0.3s ease, transform 0.3s ease; /* Smooth background change on hover */
    width: 100%; /* Make the button full width */
    max-width: 315px; /* Set a maximum width */
    justify-content: space-between; /* Distribute icon and text */
}

.add-text-btn:hover {
    background-color: #E1DEED; /* Darker shade on hover */
    transform: translateY(-5px);
}

.icon-circle {
    background-color: #E1DEED; /* Circle background color */
    border-radius: 50%; /* Makes the background circular */
    width: 50px; /* Width of the circle */
    height: 50px; /* Height of the circle */
    display: flex;
    justify-content: center;
    align-items: center;
    transition: transform 0.3s ease; /* Smooth rotation */
}

.icon-circle i {
    color: white; /* Icon color */
    font-size: 20px; /* Icon size */
}

.icon-circle:hover {
    transform: rotate(360deg); /* Rotate the icon on hover */
}
.add-text-btn h3 {
    margin: 0; /* Remove default margin from h3 */
    font-size: 18px; /* Adjust font size if needed */
    flex-grow: 1; /* Ensure text grows to fill available space */
    text-align: center; /* Center align the text */
}

button[type="submit"] {
    width: 100%; /* Make submit buttons full width */
    margin-top: 10px; /* Add some space between buttons */
    padding: 10px;
    font-size: 16px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    background-color: #94827F; /* Button background color */
    color: white; /* Button text color */
    transition: background-color 0.3s ease, transform 0.3s ease; /* Smooth background change on hover */
}

button[type="submit"]:hover {
    background-color: #A99B99; /* Darker shade on hover */
    transform: scale(1.05); /* Slightly enlarge on hover */
}
/* Container for the preview section */
.preview-box {
            width: 100%; /* Full width */
            max-width: 700px; /* Maximum width for larger screens */
            height: 650px; /* Fixed height */
            padding: 20px;
            background-color: white; /* Background color for the preview box */
            border-radius: 10px; /* Rounded corners */
            margin-bottom: 20px; /* Space below the preview box */
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* Soft shadow */
            position: relative; /* For positioning child elements */
        }

        /* Styling for the "Preview" text */
        .preview-box h2 {
            margin: 0; /* Remove margin */
            font-size: 24px; /* Font size for the heading */
            color: #333; /* Text color */
            text-align: center; /* Center align the text */
            margin-bottom: 15px; /* Space below the text */
        }

        /* Smartphone crop box */
        .smartphone-crop-box {
            width: 100%; /* Full width of the preview box */
            height: 550px; /* Adjust height as needed */
            position: relative; /* For positioning child elements */
            overflow: hidden; /* Hide overflow */
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 10px;
        }

        /* Image container */
        .smartphone-screen img {
    width: 100%; /* Initially set width to 100% of the overlay box */
    height: auto; /* Maintain aspect ratio */
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%); /* Center the image */
    z-index: 1;
}


        /* Grey overlay with clear center */
        .overlay-box {
    width: 100%; /* Full width of the smartphone-crop-box */
    height: 100%; /* Full height of the smartphone-crop-box */
    position: absolute; /* Position relative to smartphone-crop-box */
    top: 0;
    left: 0;
    background-color: rgba(0, 0, 0, 0); /* Make it fully transparent */
    display: flex;
    justify-content: center; /* Center the clear box horizontally */
    align-items: center; /* Center the clear box vertically */
    z-index: 2; /* Place the overlay above the image */
}

        /* Ensure the clear-box shows the text clearly */
.clear-box {
    width: 300px; /* Set width of the clear box */
    height: 550px; /* Set height of the clear box */
    background-color: rgba(255, 255, 255, 0); /* Transparent to show the image */
    border: 2px solid #94827F; /* Optional: Add a border to visualize the clear box */
    box-shadow: none; /* Remove the shadow to ensure no overlay effect */
    pointer-events: none; /* Make the clear box non-interactive */
    display: flex;
    justify-content: center; /* Center text horizontally */
    align-items: center; /* Center text vertically */
    font-size: 24px; /* Font size for the text */
    color: white; /* Text color */
    text-align: center; /* Center text alignment */
    overflow: hidden; /* Hide overflow */
    padding: 10px; /* Add some padding to ensure text doesn't touch edges */
    word-wrap: break-word; /* Allow text to wrap onto the next line */
}

        /* Slider container */
        .slider-container {
            margin-top: 20px;
        }

        #size-slider {
            width: 100%; /* Full width for the slider */
        }

#img-story-container {
    display: none; /* Hide initially */
}
/* Modal Styles */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1000; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto; /* 15% from the top and centered */
    padding: 20px;
    border: 1px solid #888;
    width: 80%; /* Could be more or less, depending on screen size */
    max-width: 500px; /* Maximum width */
    border-radius: 10px; /* Rounded corners */
}

.modal-buttons {
    text-align: right;
}

.modal-buttons button {
    background-color: #94827F; /* Button background color */
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    margin-left: 10px;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.modal-buttons button:hover {
    background-color: #A99B99; /* Darker shade on hover */
    transform: scale(1.05);
}


.search-results {
    position: absolute;
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 460px;
    max-height: 300px;
    overflow-y: auto;
    display: none;
    z-index: 10;
}

.search-results a {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #f0f0f0;
    text-decoration: none;
    color: #333;
    transition: background-color 0.3s ease;
}

.search-results a:last-child {
    border-bottom: none;
}

.search-results a:hover {
    background-color: #f9f9f9;
}

.search-results img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 15px;
    object-fit: cover;
}

.search-results span {
    font-size: 16px;
    font-weight: 500;
    color: #444;
}

.search-results p {
    padding: 15px;
    color: #999;
    text-align: center;
    font-size: 14px;
}

/* Mobile-friendly adjustments */
@media (max-width: 600px) {
    .search-results {
        width: 90%;
    }

    .search-results a {
        padding: 8px;
    }

    .search-results img {
        width: 35px;
        height: 35px;
    }

    .search-results span {
        font-size: 14px;
    }
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
    <input type="search" id="search-input" placeholder="Search for Students, Groups and Pages" />
    <div id="search-results" class="search-results"></div>
</div>
        <label class="btn btn-primary" for="create-post" id="create-lg1">Creating a Story</label>
        <a href="../php/logout.php?logout_id=<?php echo urlencode($_SESSION['studentNo']); ?>" class="logout-btn">Log Out</a>
        
        <a href="Student/StudentProfile.php">
            <div class="profile-pic" id="my-profile-picture" style="height: 40px; width: 40px; background-color: #b9b7c1; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                <?php 
                    if ($profileImage) {
                        echo '<img src="../php/images/' . htmlspecialchars($profileImage) . '" alt="Profile Picture" style="height: 100%; width: 100%; object-fit: cover; border-radius: 50%;">';
                    } else {
                        echo '<i class="fas fa-user" style="font-size: 30px; color: #94827F;"></i>'; // Smaller fallback icon
                    }
                ?>
            </div>
        </a>
    </div>
</nav>

<div class="story-section">
    <div class="story-option">
        <div class="card photo-story" id="photo-story-card">
            <i class="fas fa-image"></i>
            <p>Create a photo story</p>
        </div>

    </div>
</div>

<div id="img-story-container">
<?php include_once "imgStory.php"; ?>
</div>

<!-- Discard Confirmation Modal -->
<div id="discard-modal" class="modal">
    <div class="modal-content">
        <h2>Discard Story?</h2>
        <p>Are you sure you want to discard this story? Your story won't be saved.</p>
        <div class="modal-buttons">
            <button id="discard-confirm">Discard</button>
            <button id="discard-cancel">Continue Editing</button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/cropperjs"></script>
<script>

document.getElementById('search-input').addEventListener('keyup', function() {
    let searchQuery = this.value.trim();
    
    if (searchQuery.length >= 3) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'search.php?q=' + encodeURIComponent(searchQuery), true);
        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('search-results').innerHTML = this.responseText;
                document.getElementById('search-results').style.display = 'block';
            }
        };
        xhr.send();
    } else {
        document.getElementById('search-results').style.display = 'none';
    }
});
document.addEventListener('DOMContentLoaded', function () {
    const photoStoryCard = document.getElementById('photo-story-card');
    const fileInput = document.getElementById('add-story');
    const slider = document.getElementById('size-slider');
    const previewBox = document.querySelector('.preview-box');
    const storySection = document.querySelector('.story-section');
    const imgStoryContainer = document.getElementById('img-story-container');
    const discardButton = document.querySelector('button[style="background: #E1DEED"]');
    const modal = document.getElementById('discard-modal');
    const discardConfirm = document.getElementById('discard-confirm');
    const discardCancel = document.getElementById('discard-cancel');
    const form = document.querySelector('form.create-group-form');
    const croppedImageDataInput = document.getElementById('croppedImageData');
    const clearBox = document.querySelector('.clear-box');
    let cropper = null;
    let img = null;



    // Show file input when photo story card is clicked
    photoStoryCard.addEventListener('click', function () {
        fileInput.click();
    });

    // Handle file selection
    fileInput.addEventListener('change', function () {
        const file = fileInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                if (img) {
                    img.src = e.target.result;
                } else {
                    img = document.createElement('img');
                    img.id = 'preview-image';
                    img.src = e.target.result;
                    img.alt = 'Selected Image';
                    img.style.width = '50%'; // Initial size
                    img.style.height = 'auto';
                    img.style.position = 'absolute';
                    img.style.top = '50%';
                    img.style.left = '50%';
                    img.style.transform = 'translate(-50%, -50%)';
                    previewBox.querySelector('.smartphone-screen').appendChild(img);
                }

                // Hide the story section and show imgStory.php content
                storySection.style.display = 'none';
                imgStoryContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    // Show modal when Discard button is clicked
    discardButton.addEventListener('click', function (event) {
        event.preventDefault();
        modal.style.display = 'block';
    });

    // Hide modal and redirect to users.php when Discard is confirmed
    discardConfirm.addEventListener('click', function () {
        window.location.href = '../users.php'; // Redirect to users.php
    });

    // Hide modal and continue editing
    discardCancel.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    // Close modal when clicking outside of it
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    document.querySelector('form.create-group-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the form from submitting normally

    const clearBox = document.querySelector('.clear-box');
    const previewImage = document.getElementById('preview-image');

    // Calculate the position and size of the visible area
    const clearBoxRect = clearBox.getBoundingClientRect();
    const imageRect = previewImage.getBoundingClientRect();

    // Calculate the crop dimensions relative to the image
    const cropX = clearBoxRect.left - imageRect.left;
    const cropY = clearBoxRect.top - imageRect.top;
    const cropWidth = clearBoxRect.width;
    const cropHeight = clearBoxRect.height;

    // Save these coordinates to the hidden input
    const croppedImageData = {
        cropX: cropX / imageRect.width,  // Normalize by image width
        cropY: cropY / imageRect.height, // Normalize by image height
        cropWidth: cropWidth / imageRect.width, 
        cropHeight: cropHeight / imageRect.height
    };

    document.getElementById('croppedImageData').value = JSON.stringify(croppedImageData);

    // Now you can submit the form
    event.target.submit();
});


    // Handle slider input to adjust image size
    slider.addEventListener('input', function(event) {
        const value = event.target.value;
        if (img) {
            img.style.width = `${value}%`;
        }
    });
});
</script>

</body>
</html>