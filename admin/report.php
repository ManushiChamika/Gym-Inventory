<?php
include("../connection.php");

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query to count users by number of enrollments
$usersQuery = "SELECT id, username, email FROM users WHERE username != 'admin'";
$usersResult = mysqli_query($conn, $usersQuery);
$usersData = [];
while ($row = mysqli_fetch_assoc($usersResult)) {
    $usersData[] = $row;
}

// Query for enrollment status counts by course
$enrollmentsStatusQuery = "SELECT courses.title, enrollments.status, COUNT(*) as count
                           FROM enrollments
                           JOIN courses ON courses.id = enrollments.course_id
                           GROUP BY courses.id, enrollments.status";
$enrollmentsStatusResult = mysqli_query($conn, $enrollmentsStatusQuery);
$enrollmentsStatusData = [];

if ($enrollmentsStatusResult) {
    while ($row = mysqli_fetch_assoc($enrollmentsStatusResult)) {
        if (!isset($enrollmentsStatusData[$row['title']])) {
            $enrollmentsStatusData[$row['title']] = [
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0
            ];
        }
        $enrollmentsStatusData[$row['title']][$row['status']] = $row['count'];
    }
}

// Query for course enrollments
$courseEnrollmentsQuery = "SELECT courses.title, COUNT(enrollments.id) as enrollment_count 
                            FROM courses 
                            JOIN enrollments ON courses.id = enrollments.course_id 
                            GROUP BY courses.id";
$courseEnrollments = mysqli_query($conn, $courseEnrollmentsQuery);
$enrollmentsData = [];
while ($row = mysqli_fetch_assoc($courseEnrollments)) {
    $enrollmentsData[] = $row;
}

// Query to fetch attendance status counts
$attendanceCountsQuery = "
    SELECT status, COUNT(*) AS count
    FROM attendance
    GROUP BY status
";
$attendanceCountsResult = mysqli_query($conn, $attendanceCountsQuery);

// Check if the query was successful
$attendanceCounts = [];
if ($attendanceCountsResult) {
    while ($row = mysqli_fetch_assoc($attendanceCountsResult)) {
        $attendanceCounts[$row['status']] = $row['count'];
    }
} else {
    // Log the SQL error for debugging
    error_log("Attendance counts query error: " . mysqli_error($conn));
}

// Query to fetch attendance status for each student, excluding "admin" and including the attendance date
$attendanceQuery = "
    SELECT users.id, users.username, users.email, IFNULL(attendance.status, 'Absent') AS status, attendance.date
    FROM users
    LEFT JOIN attendance ON users.id = attendance.student_id
    WHERE users.username != 'admin'
";
$attendanceResult = mysqli_query($conn, $attendanceQuery);

// Check if the query was successful
$attendanceData = [];
if ($attendanceResult) {
    while ($row = mysqli_fetch_assoc($attendanceResult)) {
        $attendanceData[] = $row;
    }
} else {
    // Log the SQL error for debugging
    error_log("Attendance query error: " . mysqli_error($conn));
}

// Prepare data for JSON output
$data = [
    'users' => $usersData,
    'enrollmentsData' => $enrollmentsData,
    'attendanceCounts' => $attendanceCounts,
    'enrollmentsStatusData' => $enrollmentsStatusData,
    'attendanceData' => $attendanceData,
];

header('Content-Type: application/json');
echo json_encode($data);

mysqli_close($conn);
