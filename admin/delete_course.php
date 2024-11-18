<?php
include '../connection.php';
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $courseId = intval($_GET['id']);
    mysqli_begin_transaction($conn);

    try {
        // Fetch the image path for the course
        $fetchImagePathQuery = "SELECT image_path FROM courses WHERE id = ?";
        $stmt0 = mysqli_prepare($conn, $fetchImagePathQuery);
        mysqli_stmt_bind_param($stmt0, 'i', $courseId);
        mysqli_stmt_execute($stmt0);
        mysqli_stmt_bind_result($stmt0, $imagePath);
        mysqli_stmt_fetch($stmt0);
        mysqli_stmt_close($stmt0);

        // Delete related enrollments
        $deleteEnrollmentsQuery = "DELETE FROM enrollments WHERE course_id = ?";
        $stmt1 = mysqli_prepare($conn, $deleteEnrollmentsQuery);
        mysqli_stmt_bind_param($stmt1, 'i', $courseId);
        mysqli_stmt_execute($stmt1);
        mysqli_stmt_close($stmt1);

        // Delete the course from the courses table
        $deleteCourseQuery = "DELETE FROM courses WHERE id = ?";
        $stmt2 = mysqli_prepare($conn, $deleteCourseQuery);
        mysqli_stmt_bind_param($stmt2, 'i', $courseId);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);

        // Delete the image file from the server if it exists
        if (!empty($imagePath) && file_exists($imagePath)) {
            unlink($imagePath); // Delete the file from the server
        }

        // Commit the transaction
        mysqli_commit($conn);

        // Send success response
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback transaction if there's an error
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => 'Error deleting course']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
}
