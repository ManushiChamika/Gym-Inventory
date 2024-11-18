<?php
include("../connection.php");

if (isset($_POST['enrollment_id']) && isset($_POST['status'])) {
    $enrollmentId = $_POST['enrollment_id'];
    $status = $_POST['status'];

    // Update the enrollment status
    $updateQuery = "UPDATE enrollments SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, 'si', $status, $enrollmentId);

    if (mysqli_stmt_execute($stmt)) {
        // Fetch enrollment details
        $detailsQuery = "
            SELECT 
                e.id AS enrollment_id,
                u.username,
                u.email,
                u.phone, 
                c.title AS course_title,
                c.category AS course_category,
                e.status AS enrollment_status
            FROM enrollments e
            INNER JOIN users u ON e.user_id = u.id
            INNER JOIN courses c ON e.course_id = c.id
            WHERE e.id = ?
        ";
        $detailsStmt = mysqli_prepare($conn, $detailsQuery);
        mysqli_stmt_bind_param($detailsStmt, 'i', $enrollmentId);
        mysqli_stmt_execute($detailsStmt);
        $result = mysqli_stmt_get_result($detailsStmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $username = $row['username'];
            $phoneNumber = $row['phone'];
            $courseTitle = $row['course_title'];
            $courseCategory = $row['course_category'];
            $enrollmentStatus = $row['enrollment_status'];

            // Construct SMS message
            $message = "Hello $username, your enrollment in the course '$courseTitle' (Category: $courseCategory) has been $status.";

            // Call send_sms.php
            $smsData = [
                'phone_number' => $phoneNumber, 
                'message' => $message,
            ];
            $smsDataJson = json_encode($smsData);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://localhost/gym_mngt_sys/clickSend/send_sms.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $smsDataJson);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);

            // Debugging output for response
            if (curl_errno($ch)) {
                echo "CURL Error: " . curl_error($ch);
            } else {
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                echo "HTTP Code: $httpCode. SMS Response: $response";
            }

            curl_close($ch);
        } else {
            echo 'Error fetching enrollment details';
        }
        mysqli_stmt_close($detailsStmt);
    } else {
        echo 'Error updating enrollment status';
    }

    mysqli_stmt_close($stmt);
}
