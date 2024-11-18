<?php
session_start();
include("connection.php");

// Ensure the 'student_id' is passed as a GET parameter
if (!isset($_GET['student_id'])) {
    $message = 'Student ID is missing';
    $status = 'error';
    showMessage($status, $message);
    exit;
}

// Get the student_id from the URL
$student_id = $_GET['student_id'];

// Validate the student ID exists in the database
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $message = 'Student not found';
    $status = 'error';
    showMessage($status, $message);
    exit;
}

// Fetch student details
$student = $result->fetch_assoc();
$username = htmlspecialchars($student['username']);  // Using 'username' as the student's display name

// Check if attendance is already recorded today
$date = date('Y-m-d');
$query = "SELECT * FROM attendance WHERE student_id = ? AND date = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $student_id, $date);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $message = 'Attendance already recorded today';
    $status = 'error';
    showMessage($status, $message);
    exit;
}

// Insert the attendance record
$query = "INSERT INTO attendance (student_id, date, status) VALUES (?, ?, 'present')";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $student_id, $date);

if ($stmt->execute()) {
    $message = 'Attendance recorded successfully';
    $status = 'success';
} else {
    $message = 'Error recording attendance';
    $status = 'error';
}

// Display the profile and message
function showMessage($status, $message)
{
    global $username;
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student Profile</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background-color: #f5f5f5;
            }

            .profile-container {
                background-color: #ffffff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                width: 300px;
                text-align: center;
            }

            .profile-container h2 {
                color: #333;
                margin-bottom: 10px;
            }

            .profile-container p {
                color: #666;
                margin: 5px 0;
            }

            .message {
                margin-top: 15px;
                padding: 10px;
                border-radius: 5px;
                color: #fff;
                font-weight: bold;
            }

            .message.success {
                background-color: #4CAF50;
            }

            .message.error {
                background-color: #f44336;
            }
        </style>
    </head>

    <body>
        <div class="profile-container">
            <h2>Member Profile</h2>
            <p><strong>Member Name:</strong> <?php echo $username; ?></p>
            <div class="message <?php echo $status; ?>">
                <?php echo $message; ?>
            </div>
        </div>
    </body>

    </html>
<?php
}
showMessage($status, $message);
?>