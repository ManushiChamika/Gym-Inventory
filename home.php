<?php
session_start();
include("connection.php");

if (!isset($_SESSION['username'])) {
    header("location:index.php");
}

// Fetch user details from the database
$username = $_SESSION['username'];
$query = "SELECT height, weight FROM users WHERE username='$username'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$height = $user['height'];
$weight = $user['weight'];

// Calculate BMI
if ($height > 0 && $weight > 0) {
    $heightInMeters = $height / 100;
    $bmi = $weight / ($heightInMeters * $heightInMeters);
    $bmi = number_format($bmi, 2);
} else {
    $bmi = "N/A";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym member homepage</title>

    <link rel="icon" href="images/favicon.png" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
    
   
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- navbar section   -->

    <header class="navbar-section">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <div class="logo-wrapper">
                        <img src="images/logo.png" alt="Student Management System Logo">
                    </div>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="#home">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#dietPlan">Diet Plan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#projects">Programs</a>
                        </li>
                        <!-- Payment Link with Payment Check -->
                        <li class="nav-item">
                            <?php
                            // Check if the user is logged in
                            if (isset($_SESSION['username'])) {
                                $username = $_SESSION['username'];
                                // Query the payments table to check if a payment exists for the logged-in user
                                $query = mysqli_query($conn, "SELECT * FROM payments WHERE username = '$username' LIMIT 1");
                                // If payment exists, show an alert when the user hovers over the link
                                if (mysqli_num_rows($query) > 0) {
                                    echo '<a class="nav-link" href="#" onclick="alertPayment()">Payment</a>';
                                } else {
                                    echo '<a class="nav-link" href="payment.php">Payment</a>';
                                }
                            }
                            ?>
                        </li>                               
                        <li class="nav-item">
                            <a class="nav-link" href="gym_management.php">Gym Inventory</a>
                        </li>
                        <li class="nav-item">
                            <div class="dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="dropdownMenuLink" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="bi bi-person"></i>
                                </a>
                                <ul class="dropdown-menu mt-2 mr-0" aria-labelledby="dropdownMenuLink">
                                    <li>
                                        <?php

                                        $id = $_SESSION['id'];
                                        $query = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");

                                        while ($result = mysqli_fetch_assoc($query)) {
                                            $res_username = $result['username'];
                                            $res_email = $result['email'];
                                            $res_id = $result['id'];
                                        }


                                        echo "<a class='dropdown-item' href='edit.php?id=$res_id'>Change Profile</a>";


                                        ?>

                                    </li>
                                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- to show an alert if payment exists -->
    <script type="text/javascript">
        function alertPayment() {
            alert("You have already subscribed. Go to your profile to view.");
        }
    </script>

    <div class="name">
        <center>Welcome
            <?php
            // echo $_SESSION['valid'];

            echo $_SESSION['username'];

            ?>
            !
        </center>
    </div>

    <!-- hero section  -->

    <section id="home" class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-12 col-sm-12 text-content">
                    <h1>this is where you find all the programs</h1>
                    <p>Please enroll in the program to get the best experience of learning.
                    </p>
                    <a href="#projects" style="color: azure;"><button class="btn">Go to Programs</button></a>
                </div>
                <div class="col-lg-8 col-md-12 col-sm-12">
                    <img src="images/bg.jpg" alt="" class="img-fluid">
                </div>

            </div>
        </div>
    </section>

    <div class="stats-container-bg">
    <div class="stats-container">
        <div class="stat-card">
            <i class="fa fa-ruler stat-icon"></i>
            <div class="stat-value"><?php echo htmlspecialchars($height); ?> cm</div>
            <div class="stat-label">Height</div>
        </div>
        <div class="stat-card">
            <i class="fa fa-weight stat-icon"></i>
            <div class="stat-value"><?php echo htmlspecialchars($weight); ?> kg</div>
            <div class="stat-label">Weight</div>
        </div>
        <div class="stat-card">
            <i class="fa fa-heartbeat stat-icon"></i>
            <div class="stat-value"><?php echo $bmi; ?></div>
            <div class="stat-label">BMI</div>
        </div>
    </div>
    </div>

    <section class="project-section" id="projects">
    <div class="container">
        <div class="row text">
            <div class="col-lg-6 col-md-12">
                <h1>Available Programs</h1>
                <hr>
            </div>
            <div class="col-lg-6 col-md-12">
                <p>Choose the program you wish to enroll in</p>
            </div>
        </div>

        <!-- Category Filter Dropdown -->
        <div class="row filter-row justify-content-center">
            <div class="col-lg-6 col-md-8 text-center">
                <label for="categoryFilter" class="filter-label">Filter by Category:</label>
                <select id="categoryFilter" class="category-filter" onchange="filterCourses()">
                    <option value="all">All Categories</option>
                    <?php
                    // Fetch distinct categories from the database
                    $category_query = "SELECT DISTINCT category FROM courses";
                    $category_result = mysqli_query($conn, $category_query);

                    while ($category = mysqli_fetch_assoc($category_result)) {
                        echo '<option value="' . htmlspecialchars($category['category']) . '">' . htmlspecialchars($category['category']) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Scroll Buttons -->
        <button class="carousel-control left-arrow" onclick="scrollCardsLeft()">&#10094;</button>
        <button class="carousel-control right-arrow" onclick="scrollCardsRight()">&#10095;</button>

        <div class="project-container">
            <div class="row project">
                <?php
                // Fetch courses from the database
                $query = "SELECT * FROM courses ORDER BY last_modified DESC";
                $result = mysqli_query($conn, $query);
                $user_id = $_SESSION['id'];

                if (mysqli_num_rows($result) > 0) {
                    while ($course = mysqli_fetch_assoc($result)) {
                        // Get enrollment status
                        $enrollment_check_query = "SELECT status FROM enrollments WHERE user_id = $user_id AND course_id = " . $course['id'];
                        $enrollment_check_result = mysqli_query($conn, $enrollment_check_query);
                        $enrollment_status = mysqli_fetch_assoc($enrollment_check_result) ?? ['status' => ''];

                        // Set button appearance based on status
                        $status_class = 'enroll-btn';
                        $status_text = 'Enroll';
                        $button_disabled = false;

                        switch ($enrollment_status['status']) {
                            case 'pending':
                                $status_class = 'enroll-btn--pending';
                                $status_text = 'Enrollment Pending';
                                $button_disabled = true;
                                break;
                            case 'approved':
                                $status_class = 'enroll-btn--approved';
                                $status_text = 'Enrolled';
                                $button_disabled = true;
                                break;
                            case 'rejected':
                                $status_class = 'enroll-btn--rejected';
                                $status_text = 'Enrollment Rejected';
                                $button_disabled = true;
                                break;
                        }

                        $imagePath = !empty($course['image_path']) ? 'admin/' . htmlspecialchars($course['image_path']) : 'images/default_image.jpg';
                        $course_data = array_merge($course, ['status' => $enrollment_status['status']]);
                ?>
                        <div class="card course-card" data-category="<?php echo htmlspecialchars($course['category']); ?>" onclick="openModal(<?php echo htmlspecialchars(json_encode($course_data, ENT_QUOTES), ENT_QUOTES); ?>)">
                            <img src="<?php echo $imagePath; ?>" class="card-img-top" alt="Course Image">
                            <div class="card-body">
                                <h4 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h4>
                                <p class="card-text">
                                    <?php echo htmlspecialchars($course['category']); ?><br><br>
                                    <?php echo date("M d, Y", strtotime($course['date'])); ?>
                                </p>
                                <!-- Enroll Button -->
                                <button class="<?php echo $status_class; ?>"
                                    data-course='<?php echo htmlspecialchars(json_encode($course), ENT_QUOTES); ?>'
                                    onclick="showConfirmationModal(event)"
                                    <?php echo $button_disabled ? 'disabled' : ''; ?>>
                                    <?php echo $status_text; ?>
                                </button>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo "<p>No courses available at the moment.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</section>



    <!-- Enroll Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeConfirmationModal()">&times;</span>
            <h3>Are you sure you want to enroll in this program?</h3>
            <div class="modal-footer">
                <button id="confirmEnroll" class="enroll-btn" onclick="confirmEnrollment()">Yes, Enroll</button>
                <button class="cancel-btn" onclick="closeConfirmationModal()">Cancel</button>
            </div>
        </div>
    </div>

    <div id="courseModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                <img id="modalImage" src="" alt="Course Image" class="modal-img">
                <h2 id="modalTitle"></h2>
            </div>
            <div class="modal-body">
                <p id="modalCategory"></p>
                <p id="modalDate"></p>
                <p id="modalDescription"></p>
            </div>
            <div class="modal-footer">
                <button id="enrollButton" class="enroll-btn" onclick="showConfirmationModal(event)">Enroll Now</button>
            </div>
        </div>
    </div>

        <!--diet plan section-->
        <section class="diet-plan-section" id="dietPlan">
        <div class="container">
            <div class="row">
                <!-- Left side content -->
                <div class="col-lg-6 col-md-12 order-2 order-lg-1">
                    <h1 class="text-center">Diet Plan and Tracking</h1>
                    <hr>
                    <h4>Personalized Diet Plans</h4>
                    <p>Members can personalize their diet plans based on their fitness targets and receive personal nutrition recommendations.</p>
                    <form id="dietPlanForm" class="mb-5">
                        <div class="mb-3">
                            <label for="fitnessGoal" class="form-label">Select Fitness Goal</label>
                            <select class="form-select" id="fitnessGoal" name="fitnessGoal" required>
                                <option value="weightLoss">Weight Loss</option>
                                <option value="muscleGain">Muscle Gain</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="dietPreferences" class="form-label">Diet Preferences</label>
                            <select class="form-select" id="dietPreferences" name="dietPreferences" required>
                                <option value="">Select Diet Preference</option>
                                <option value="vegetarian">Vegetarian</option>
                                <option value="vegan">Vegan</option>
                                <option value="lowCarb">Low-carb</option>
                                <option value="highProtein">High-protein</option>
                                <option value="paleo">Paleo</option>
                                <option value="keto">Keto</option>
                            </select>
                        </div>

                        <button type="button" class="btn btn-primary" onclick="openDietPlanModal()">Enroll in Diet Plan</button>
                    </form>
                    
                    <div class="modal fade" id="dietPlanModal" tabindex="-1" aria-labelledby="dietPlanModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="dietPlanModalLabel">Customize Your 7-Day Diet Plan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Fitness Goal:</strong> <span id="selectedFitnessGoal"></span></p>
                                <p><strong>Diet Preference:</strong> <span id="selectedDietPreference"></span></p>

                                <!-- Table for 7-Day Diet Plan -->
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Day</th>
                                                <th>Breakfast</th>
                                                <th>Lunch</th>
                                                <th>Dinner</th>
                                                <th>Snacks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Generate rows for each day of the week with selectable options -->
                                            <script>
                                                const daysOfWeek = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
                                                const mealOptions = {
                                                    breakfast: ["Oatmeal & Fruits", "Scrambled Eggs & Avocado", "Smoothie Bowl", "Greek Yogurt & Berries", "Pancakes & Honey"],
                                                    lunch: ["Grilled Chicken Salad", "Quinoa & Veggies", "Turkey Sandwich", "Lentil Soup", "Vegetable Stir-Fry"],
                                                    dinner: ["Salmon & Veggies", "Vegetarian Chili", "Grilled Steak & Sweet Potatoes", "Pasta Primavera", "Tofu Stir-Fry"],
                                                    snacks: ["Nuts & Seeds", "Fruit Salad", "Protein Bar", "Carrot Sticks & Hummus", "Yogurt & Berries"]
                                                };

                                                document.write(daysOfWeek.map(day => `
                                                    <tr>
                                                        <td>${day}</td>
                                                        <td>${generateMealSelect(day, 'breakfast')}</td>
                                                        <td>${generateMealSelect(day, 'lunch')}</td>
                                                        <td>${generateMealSelect(day, 'dinner')}</td>
                                                        <td>${generateMealSelect(day, 'snacks')}</td>
                                                    </tr>
                                                `).join(''));

                                                // Function to generate a select dropdown for each meal
                                                function generateMealSelect(day, mealType) {
                                                    const options = mealOptions[mealType].map(option => `<option value="${option}">${option}</option>`).join('');
                                                    return `<select class="form-control" name="${day.toLowerCase()}_${mealType}">
                                                                <option value="" disabled selected>Select an option</option>
                                                                ${options}
                                                            </select>`;
                                                }
                                            </script>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" onclick="saveDietPlan()">Save Diet Plan</button>

                            </div>
                        </div>
                    </div>
                </div>
                <h4>Progress Tracking</h4>
                <p>Use the tracking features below to monitor your dietary habits, weight changes, and body composition over time.</p>
                <form id="progressTrackingForm">
                    <div class="mb-3">
                        <label for="currentWeight" class="form-label">Current Weight (kg)</label>
                        <input type="number" class="form-control" id="currentWeight" name="currentWeight" required>
                    </div>
                    <div class="mb-3">
                        <label for="bodyFat" class="form-label">Body Fat Percentage (%)</label>
                        <input type="number" class="form-control" id="bodyFat" name="bodyFat" required>
                    </div>
                    <button type="submit" class="btn btn-success">Track Progress</button>
                </form>
            </div>
            
            <!-- Right side image -->
            <div class="col-lg-6 col-md-12 order-1 order-lg-2 d-flex align-items-center justify-content-center">
                <img src="./images/gym9.jpg" class="img-fluid shadow rounded" alt="Diet Tracking Illustration">
            </div>
        </div>
        
        <!-- Separate row for the progress chart -->
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="text-center">Progress Over Time</h4>
                <canvas id="progressChart"></canvas>
            </div>
        </div>
    </div>
</section>
    
    <!-- footer section  -->

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-12 col-sm-12">
                    <img src="images/logo.png" alt="Student Management System Logo" style="width: 100px;">
                </div>
                <div class="col-lg-6 col-md-12 col-sm-12">
                    <ul class="d-flex">
                        <li><a href="#">Home</a></li>
                        <li><a href="#">Courses</a></li>
                        <li><a href="#">projects</a></li>
                        <li><a href="payment.php">Payment</a></li>
                        <li><a href="gym_management.php">Gym Inventory</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-12 col-sm-12">
                    <p>&copy;2024<br>
                    <span style="font-style: italic; color: #8e8e8e;">
                        Welform Fitness Centre 167/2, Hokandara North, Hokandara, Malabe 10118
                    </span>
                         

                    </p>
                </div>

                <div class="col-lg-1 col-md-12 col-sm-12">
                    <!-- back to top  -->

                    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
                            class="bi bi-arrow-up-short"></i></a>
                </div>

            </div>

        </div>

    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm"
        crossorigin="anonymous"></script>

    <script src="js/courseCarousel.js"></script>
    <script src="js/courseDetailsModal.js"></script>

    <script>
        // JavaScript function to filter courses by category
        function filterCourses() {
            const selectedCategory = document.getElementById('categoryFilter').value;
            const courses = document.querySelectorAll('.course-card');

            courses.forEach(course => {
                const courseCategory = course.getAttribute('data-category');
                if (selectedCategory === 'all' || courseCategory === selectedCategory) {
                    course.style.display = 'block';
                } else {
                    course.style.display = 'none';
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize Chart.js for tracking progress over time
    const ctx = document.getElementById('progressChart').getContext('2d');
    const progressChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [], // Dates for tracking
            datasets: [
                {
                    label: 'Weight (kg)',
                    data: [],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false,
                },
                {
                    label: 'Body Fat (%)',
                    data: [],
                    borderColor: 'rgba(255, 99, 132, 1)',
                    fill: false,
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: { display: true, title: { display: true, text: 'Date' } },
                y: { display: true, title: { display: true, text: 'Value' } }
            }
        }
    });

    // JavaScript to handle form submission and update the chart
    document.getElementById('progressTrackingForm').addEventListener('submit', function(event) {
        event.preventDefault();
        
        const date = new Date().toLocaleDateString();
        const weight = document.getElementById('currentWeight').value;
        const bodyFat = document.getElementById('bodyFat').value;
        
        progressChart.data.labels.push(date);
        progressChart.data.datasets[0].data.push(weight);
        progressChart.data.datasets[1].data.push(bodyFat);
        progressChart.update();
    });
</script>
<script>
    // Function to open the modal and display selected options
    function openDietPlanModal() {
        const fitnessGoal = document.getElementById('fitnessGoal').value;
        const dietPreference = document.getElementById('dietPreferences').value;

        // Validate that both fitness goal and diet preference are selected
        if (!fitnessGoal || !dietPreference) {
            alert('Please select both a fitness goal and a diet preference before proceeding.');
            return;
        }

        // Display selected fitness goal and diet preference in the modal
        document.getElementById('selectedFitnessGoal').textContent = fitnessGoal;
        document.getElementById('selectedDietPreference').textContent = dietPreference;

        // Show the modal using Bootstrap
        const dietPlanModal = new bootstrap.Modal(document.getElementById('dietPlanModal'));
        dietPlanModal.show();
    }

    // Function to save the diet plan
    function saveDietPlan() {
        const fitnessGoal = document.getElementById('fitnessGoal').value;
        const dietPreference = document.getElementById('dietPreferences').value;
        const dietPlan = {};

        // Loop through each day of the week to collect meal selections
        const daysOfWeek = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
        for (const day of daysOfWeek) {
            dietPlan[day] = {
                breakfast: document.querySelector(`[name="${day.toLowerCase()}_breakfast"]`).value || '',
                lunch: document.querySelector(`[name="${day.toLowerCase()}_lunch"]`).value || '',
                dinner: document.querySelector(`[name="${day.toLowerCase()}_dinner"]`).value || '',
                snacks: document.querySelector(`[name="${day.toLowerCase()}_snacks"]`).value || ''
            };
        }

        // Perform basic validation to ensure all meals are selected
        if (Object.values(dietPlan).some(meals => Object.values(meals).includes(''))) {
            alert('Please select meals for all fields before saving the diet plan.');
            return;
        }

        // Make a POST request to the PHP backend
        fetch('save_diet_plan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ fitnessGoal, dietPreference, dietPlan })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Diet plan saved successfully!');
                    // Hide the modal after saving
                    const dietPlanModal = bootstrap.Modal.getInstance(document.getElementById('dietPlanModal'));
                    dietPlanModal.hide();
                } else {
                    alert(`Failed to save the diet plan: ${data.message || 'Unknown error'}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred while saving the diet plan.');
            });
    }
</script>


</body>

</html>