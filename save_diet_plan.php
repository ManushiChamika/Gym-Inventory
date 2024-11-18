<?php
// save_diet_plan.php
header('Content-Type: application/json');

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $fitnessGoal = $data['fitnessGoal'];
    $dietPreference = $data['dietPreference'];
    $dietPlan = $data['dietPlan'];

    include("connection.php");

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    // Prepare SQL for saving diet plan (Example: Insert into a "diet_plans" table)
    $sql = "INSERT INTO diet_plans (fitness_goal, diet_preference) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $fitnessGoal, $dietPreference);

    if ($stmt->execute()) {
        $planId = $conn->insert_id;

        foreach ($dietPlan as $day => $meals) {
            $sqlMeal = "INSERT INTO diet_plan_meals (plan_id, day, breakfast, lunch, dinner, snacks) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtMeal = $conn->prepare($sqlMeal);
            $stmtMeal->bind_param("isssss", $planId, $day, $meals['breakfast'], $meals['lunch'], $meals['dinner'], $meals['snacks']);
            $stmtMeal->execute();
        }

        echo json_encode(['success' => true, 'message' => 'Diet plan saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save diet plan']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}

?>