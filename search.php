<?php
require 'php/config.php'; // Ensure this points to your config

if (isset($_GET['q'])) {
    $query = '%' . $_GET['q'] . '%'; // Use wildcards for SQL LIKE query

    // Search for students
    $stmt_student = $conn->prepare("
        SELECT student.student_id, student.firstName, student.lastName, profile.image 
        FROM student
        LEFT JOIN profile ON profile.student_id = student.student_id
        WHERE student.firstName LIKE ? OR student.lastName LIKE ?
    ");
    $stmt_student->bind_param('ss', $query, $query);
    $stmt_student->execute();
    $result_student = $stmt_student->get_result();

    // Search for groups
    $stmt_group = $conn->prepare("
        SELECT `group`.group_id, `group`.group_name, `group`.group_image
        FROM `group`
        WHERE `group`.group_name LIKE ?
    ");
    $stmt_group->bind_param('s', $query);
    $stmt_group->execute();
    $result_group = $stmt_group->get_result();

    // Search for pages
    $stmt_page = $conn->prepare("
        SELECT page.page_id, page.page_title, page.image
        FROM page
        WHERE page.page_title LIKE ?
    ");
    $stmt_page->bind_param('s', $query);
    $stmt_page->execute();
    $result_page = $stmt_page->get_result();

    // Output the results for students
    if ($result_student->num_rows > 0) {
        while ($row = $result_student->fetch_assoc()) {
            $name = htmlspecialchars($row['firstName']) . ' ' . htmlspecialchars($row['lastName']);
            $image = $row['image'] ? "php/images/" . htmlspecialchars($row['image']) : 'path/to/default-image.png';
            $student_id = $row['student_id'];

            echo "<a href='Student/friendProfile.php?student_id=$student_id'>
                    <img src='$image' alt='$name' style='width: 40px; height: 40px; border-radius: 50%; margin-right: 10px;'>
                    <span>$name</span>
                  </a><br>";
        }
    }

    // Output the results for groups
    if ($result_group->num_rows > 0) {
        while ($row = $result_group->fetch_assoc()) {
            $group_name = htmlspecialchars($row['group_name']);
            $group_image = $row['group_image'] ? "php/images/" . htmlspecialchars($row['group_image']) : 'path/to/default-group-image.png';
            $group_id = $row['group_id'];

            echo "<a href='Student/groupProfile.php?group_id=$group_id'>
                    <img src='$group_image' alt='$group_name' style='width: 40px; height: 40px; border-radius: 50%; margin-right: 10px;'>
                    <span>$group_name</span>
                  </a><br>";
        }
    }

    // Output the results for pages
    if ($result_page->num_rows > 0) {
        while ($row = $result_page->fetch_assoc()) {
            $page_title = htmlspecialchars($row['page_title']);
            $page_image = $row['image'] ? "php/images/" . htmlspecialchars($row['image']) : 'path/to/default-page-image.png';
            $page_id = $row['page_id'];

            echo "<a href='Student/pageProfile.php?page_id=$page_id'>
                    <img src='$page_image' alt='$page_title' style='width: 40px; height: 40px; border-radius: 50%; margin-right: 10px;'>
                    <span>$page_title</span>
                  </a><br>";
        }
    }

    // If no results were found
    if ($result_student->num_rows == 0 && $result_group->num_rows == 0 && $result_page->num_rows == 0) {
        echo "<p>No results found</p>";
    }
}
?>
