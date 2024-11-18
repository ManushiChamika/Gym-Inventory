<?php
session_start();
include("connection.php");

if (!isset($_SESSION['id'])) {
    header("location: index.php");
    exit;
}

// Include QR code generation library
include('lib/phpqrcode/qrlib.php');

// Define the profile image upload directory
$profileImageDir = "admin/uploads/profile/";
if (!is_dir($profileImageDir)) {
    mkdir($profileImageDir, 0755, true);
}

// Get user ID from session
$userId = intval($_SESSION['id']);

// Retrieve user info
$query = "SELECT username, email FROM users WHERE id = $userId";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
$username = $user['username'];
$email = $user['email'];

// Retrieve the latest payment details for the current user
$paymentQuery = "SELECT * FROM payments WHERE username = '$username' ORDER BY id DESC LIMIT 1";
$paymentResult = mysqli_query($conn, $paymentQuery);
$currentPayment = mysqli_fetch_assoc($paymentResult);

if ($currentPayment) {
    $plan = $currentPayment['plan'];
    $paymentMethod = $currentPayment['payment_method'];
    $paymentStatus = "Successful";
} else {
    $plan = "No Plan";
    $paymentMethod = "N/A";
    $paymentStatus = "No payment found.";
}

// Check if a success message should be shown
$showModal = isset($_SESSION['showModal']) ? $_SESSION['showModal'] : false;
if ($showModal) {
    unset($_SESSION['showModal']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Profile</title>
    <link rel="icon" href="images/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styleProfile.css">
</head>

<!-- Body Section Starts -->
<body>
    <div class="container">
        <div class="form-box-edit box" style="width: 80%;">
            <?php
            if (isset($_POST['update'])) {
                $username = $_POST['username'];
                $email = $_POST['email'];
                $password = $_POST['password'];
                $phone = $_POST['phone']; // Capture phone number
                $id = $_SESSION['id'];
                $res_age = $_POST['age'];
                $res_weight = $_POST['weight'];
                $res_height = $_POST['height'];
                $res_bmi = $_POST['bmi'];
            
                // Handle profile image upload
                if ($_FILES['profile_image']['name']) {
                    $profileImageName = basename($_FILES['profile_image']['name']);
                    $targetPath = $profileImageDir . $profileImageName;
            
                    // Move the uploaded file
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                    // Update the user profile with the image path, phone, age, weight, height, and BMI
                    $edit_query = mysqli_query($conn, "UPDATE users SET 
                        username='$username', 
                        email='$email', 
                        password='$password', 
                        phone='$phone', 
                        profile_image='$targetPath', 
                        age='$res_age', 
                        weight='$res_weight', 
                        height='$res_height', 
                        bmi='$res_bmi' 
                        WHERE id = $id");
                } else {
                    echo "<div class='message'><p>Image upload failed.</p></div><br>";
                }
                } else {
                    // Update user details without changing profile image
                    $edit_query = mysqli_query($conn, "UPDATE users SET 
                        username='$username', 
                        email='$email', 
                        password='$password', 
                        phone='$phone', 
                        age='$res_age', 
                        weight='$res_weight', 
                        height='$res_height', 
                        bmi='$res_bmi' 
                        WHERE id = $id");
                }
                
                if ($edit_query) {
                    // Set session variable for modal display and redirect
                    $_SESSION['showModal'] = true;
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
            } else {
                $id = $_SESSION['id'];
                $query = mysqli_query($conn, "SELECT * FROM users WHERE id = $id") or die("error occurs");

                while ($result = mysqli_fetch_assoc($query)) {
                    $res_username = $result['username'];
                    $res_email = $result['email'];
                    $res_password = $result['password'];
                    $res_phone = $result['phone']; // Fetch phone number
                    $res_profile_image = $result['profile_image'];
                    $res_id = $result['id'];
                    $res_age = $result['age'];
                    $res_weight = $result['weight'];
                    $res_height = $result['height'];
                    $res_bmi = $result['bmi'];
                }

                // Directory to store QR codes
                $qrDir = "qrcodes/";
                if (!is_dir($qrDir)) {
                    mkdir($qrDir);
                }

                // Generate a unique QR code for the student
                $qrContent = "http://localhost:8080/gym_mngt_sys/mark_attendance.php?student_id=" . $res_id;
                $qrFileName = $qrDir . $res_id . ".png";

                if (!file_exists($qrFileName)) {
                    QRcode::png($qrContent, $qrFileName);
                }
            ?>

                <header style="display: flex; align-items: center; justify-content: space-between;">
                    <!-- Home Button -->
                    <div class="home-button">
                        <a href="home.php">
                            <i class="fas fa-home"></i>
                        </a>
                    </div>&nbsp;&nbsp;
                    <span>Change Profile</span>
                    
                    
                </header>
                <!-- Display Profile Image at the top of the form -->
                <div class="profile-image-section" style="text-align: center;">
                    <?php if ($res_profile_image) { ?>
                        <img src="<?php echo $res_profile_image; ?>" alt="Profile Image" style="width: 150px; height: 150px; border-radius: 50%;">
                    <?php } else { ?>
                        <img src="images/defaultProfile.webp" alt="Default Profile Image" style="width: 150px; height: 150px; border-radius: 50%;">
                    <?php } ?>
                </div>

                <form action="#" method="POST" enctype="multipart/form-data">
                    <div class="form-box">
                        <div class="input-container-edit">
                            <i class="fa fa-user icon"></i>
                            <input class="input-field" type="text" placeholder="Username" name="username" value="<?php echo $res_username; ?>" required>
                        </div>
                        <div class="input-container-edit">
                            <i class="fa fa-envelope icon"></i>
                            <input class="input-field" type="email" placeholder="Email Address" name="email" value="<?php echo $res_email; ?>" required>
                        </div>
                        <div class="input-container-edit">
                            <i class="fa fa-phone icon"></i>
                            <input 
                                class="input-field" 
                                type="tel" 
                                placeholder="Phone Number (e.g., +94712345678)" 
                                name="phone" 
                                value="<?php echo htmlspecialchars($res_phone); ?>" 
                                required>
                        </div>

                        <div class="input-container-edit" hidden>
                            <i class="fa fa-lock icon"></i>
                            <input class="input-field password" type="password" placeholder="Password" name="password" value="<?php echo $res_password; ?>" required>
                            <i class="fa fa-eye toggle icon"></i>
                        </div>
                        <div class="input-container-edit">
                            <i class="fa fa-camera icon"></i>
                            <input class="input-field" type="file" name="profile_image" accept="image/*">
                        </div>
                        <div class="input-container-edit">
                            <i class="fa fa-calendar icon"></i>
                            <input class="input-field" type="number" placeholder="Age" name="age" value="<?php echo $res_age; ?>" required>
                        </div>
                        <div class="input-container-edit">
                            <i class="fa fa-weight icon"></i>
                            <input class="input-field" type="number" step="0.01" placeholder="Weight (kg)" name="weight" value="<?php echo $res_weight; ?>" required>
                        </div>
                        <div class="input-container-edit">
                            <i class="fa fa-ruler icon"></i>
                            <input class="input-field" type="number" step="0.01" placeholder="Height (cm)" name="height" value="<?php echo $res_height; ?>" required>
                        </div>
                        <div class="input-container-edit">
                            <i class="fa fa-heartbeat icon"></i>
                            <input class="input-field" type="text" placeholder="BMI" name="bmi" value="<?php echo $res_bmi; ?>" readonly>
                        </div>
                    </div>
                    <input type="submit" name="update" id="submit" value="Update" class="btn-edit" style="display: block;">
                </form>


                <!-- Modal -->
                <div id="successModal" class="modal" style="display: <?php echo $showModal ? 'block' : 'none'; ?>;">
                    <div class="modal-content">
                        <span class="close-btn" onclick="closeModal()">&times;</span>
                        <p>Profile Updated Successfully!</p>
                    </div>
                </div>

                <?php
                // Fetch the current user's ID
                $user_id = $_SESSION['id'];

                // Query to get enrolled courses for the current user
                $enrollments_query = "SELECT courses.title, courses.description, courses.category, courses.date 
                      FROM enrollments 
                      JOIN courses ON enrollments.course_id = courses.id 
                      WHERE enrollments.user_id = $user_id AND enrollments.status = 'approved'";
                $result = mysqli_query($conn, $enrollments_query);
                ?>

                <div class="enrolled-courses">
                    <h2>Enrolled Programs</h2>
                    <table class="courses-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div>
                <div>
                <h2>Payment Summary</h2>
                <table class="summary-table">
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th> <!-- Add Phone Column -->
                        <th>Plan</th>
                    </tr>
                    <tr>
                        <td><?php echo htmlspecialchars($username); ?></td>
                        <td><?php echo htmlspecialchars($email); ?></td>
                        <td><?php echo htmlspecialchars($res_phone); ?></td> <!-- Display Phone -->
                        <td><?php echo str_replace('_', ' ', ucfirst(htmlspecialchars($plan))); ?></td>
                    </tr>
                </table>
            </div>

           
                <div class="qr-code-section" style="text-align: center; margin-top: 20px;">
                    <p>Your QR Code for Attendance:</p>
                    <img src="<?php echo $qrFileName; ?>" alt="QR Code for Attendance" style="width: 150px;">
                    <p>Scan this code at the entrance to mark your attendance.</p>
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
        const toggle = document.querySelector(".toggle"),
            input = document.querySelector(".password");
        toggle.addEventListener("click", () => {
            if (input.type === "password") {
                input.type = "text";
                toggle.classList.replace("fa-eye-slash", "fa-eye");
            } else {
                input.type = "password";
            }
        })
    </script>

    <script>
        function showModal() {
            document.getElementById("successModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("successModal").style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById("successModal")) {
                closeModal();
            }
        }
    </script>
    <script>
    document.getElementById('submit').addEventListener('click', function (event) {
        const phoneField = document.querySelector('input[name="phone"]');
        const phoneValue = phoneField.value;
        const phonePattern = /^\+94\d{9}$/;

        if (!phonePattern.test(phoneValue)) {
            event.preventDefault(); // Prevent form submission
            alert("Invalid phone number format. Please enter a number in the format +947XXXXXXXX.");
        }
    });
</script>

</body>

<!-- Body Section Ends -->

</html>