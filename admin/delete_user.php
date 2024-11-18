<?php
include("../connection.php");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE' && isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    // Debug: Check if the user ID is valid
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
        exit;
    }

    // Delete the user's attendance records
    $deleteAttendance = "DELETE FROM attendance WHERE student_id = $userId";
    if (!mysqli_query($conn, $deleteAttendance)) {
        echo json_encode(['success' => false, 'error' => 'Failed to delete attendance records: ' . mysqli_error($conn)]);
        exit;
    }

    // Delete the user's enrollments
    $deleteEnrollments = "DELETE FROM enrollments WHERE user_id = $userId";
    if (!mysqli_query($conn, $deleteEnrollments)) {
        echo json_encode(['success' => false, 'error' => 'Failed to delete enrollments: ' . mysqli_error($conn)]);
        exit;
    }

    // Delete the user
    $deleteUser = "DELETE FROM users WHERE id = $userId";
    if (mysqli_query($conn, $deleteUser)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete user: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

mysqli_close($conn);
