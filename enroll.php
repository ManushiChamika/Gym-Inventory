<?php
session_start();

include("connection.php");

// Check if the user is logged in (you can customize this based on your session handling)
if (!isset($_SESSION['id'])) {
    die("You must be logged in to enroll in a course.");
}

// Check if the course ID is provided via POST
if (isset($_POST['course_id'])) {
    $course_id = intval($_POST['course_id']); // Sanitize course ID
    $user_id = $_SESSION['id']; // Get the logged-in user's ID from the session

    // Insert into the enrollments table
    $query = "INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)";

    // Prepare the statement
    if ($stmt = $conn->prepare($query)) {
        // Bind the parameters (s = string, i = integer)
        $stmt->bind_param("ii", $user_id, $course_id);

        // Execute the statement
        if ($stmt->execute()) {
            echo "Successfully enrolled in the course!";
        } else {
            echo "Error: Could not enroll in the course.";
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error: Could not prepare the query.";
    }
} else {
    echo "Error: No course ID provided.";
}

// Close the database connection
$conn->close();
