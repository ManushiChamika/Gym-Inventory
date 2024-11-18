<?php
require '../connection.php';

$uploadDir = 'uploads/';
$uploadFile = '';

$courseId = '';
$title = '';
$category = '';
$description = '';
$date = '';
$modalTitle = 'Add New Course';
$submitButton = 'Add Course';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['title']) && !empty($_POST['category']) && !empty($_POST['description']) && !empty($_POST['date'])) {
        $courseId = isset($_POST['courseId']) ? mysqli_real_escape_string($conn, $_POST['courseId']) : null;
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $date = mysqli_real_escape_string($conn, $_POST['date']);

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uploadFile = $uploadDir . uniqid() . '-' . basename($_FILES['image']['name']);

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/avif'];
            $maxSize = 5 * 1024 * 1024;

            if (in_array($_FILES['image']['type'], $allowedTypes) && $_FILES['image']['size'] <= $maxSize) {
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                    die("Error uploading file.");
                }
            } else {
                echo "<script>alert('Invalid file type or file is too large.'); window.history.back();</script>";
                exit;
            }
        }

        if ($courseId) {
            $query = "UPDATE courses SET 
                        title='$title', 
                        category='$category', 
                        description='$description', 
                        date='$date'" . (!empty($uploadFile) ? ", image_path='$uploadFile'" : "") . ",
                        last_modified=NOW()
                      WHERE id='$courseId'";
            $action = "updated";
        } else {
            $query = "INSERT INTO courses (title, category, description, date, image_path, last_modified) 
                      VALUES ('$title', '$category', '$description', '$date', '$uploadFile', NOW())";
            $action = "added";
        }

        if (mysqli_query($conn, $query)) {
            header("Location: home.php?status=success&action=$action");
            exit;
        } else {
            echo "Database error: " . mysqli_error($conn);
        }
    } else {
        echo "<p style='color: red;'>All form fields are required.</p>";
    }
}
