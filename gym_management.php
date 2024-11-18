<?php
session_start();
include("./connection.php");

$message = "";

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle reservation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_equipment'])) {
    // CSRF token check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid CSRF token. Please refresh the page and try again.";
    } else {
        // Form data
        $equipment_id = intval($_POST['equipment_id']);
        $reserved_from = $conn->real_escape_string($_POST['reserved_from']);
        $reserved_to = $conn->real_escape_string($_POST['reserved_to']);
        $requested_quantity = intval($_POST['quantity']);
        $user_id = $_SESSION['id'];

        // Validation checks
        $date_from = date('Y-m-d', strtotime($reserved_from));
        $date_to = date('Y-m-d', strtotime($reserved_to));

        if ($date_from !== $date_to) {
            $message = "Start and end times must be on the same date!";
        } elseif ($requested_quantity <= 0) {
            $message = "Quantity must be a positive value!";
        } elseif ((strtotime($reserved_to) - strtotime($reserved_from)) !== 3600) {
            $message = "Reservations must be exactly 1 hour!";
        } else {
            // Check for duplicate reservations (same time slot)
            $duplicate_check = $conn->query("
                SELECT * FROM user_reservations 
                WHERE user_id = $user_id 
                  AND equipment_id = $equipment_id 
                  AND reserved_from = '$reserved_from' 
                  AND reserved_to = '$reserved_to'
            ");
            if ($duplicate_check->num_rows > 0) {
                $message = "You already have a reservation for this equipment at the selected time!";
            } else {
                // Check availability
                $conflict_check = $conn->query("
                    SELECT SUM(quantity) as reserved_quantity 
                    FROM user_reservations 
                    WHERE equipment_id = $equipment_id 
                      AND ('$reserved_from' < reserved_to AND '$reserved_to' > reserved_from)
                ");
                $conflict = $conflict_check->fetch_assoc();
                $reserved_quantity = $conflict['reserved_quantity'] ?? 0;

                $inventory_result = $conn->query("SELECT quantity FROM inventory WHERE id = $equipment_id");
                $inventory = $inventory_result->fetch_assoc();
                $available_quantity = $inventory['quantity'] - $reserved_quantity;

                if ($requested_quantity > $available_quantity) {
                    $message = "Only $available_quantity units available during this time slot!";
                } else {
                    // Insert reservation into `user_reservations`
                    $sql = "INSERT INTO user_reservations 
                            (user_id, equipment_id, reserved_from, reserved_to, quantity) 
                            VALUES ($user_id, $equipment_id, '$reserved_from', '$reserved_to', $requested_quantity)";
                    if ($conn->query($sql) === TRUE) {
                        $message = "Reservation added successfully!";
                        // Regenerate token after successful submission
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    } else {
                        $message = "Error: " . $conn->error;
                    }
                }
            }
        }
    }

    // Prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle reservation deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_reservation'])) {
    // CSRF token check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid CSRF token. Please refresh the page and try again.";
    } else {
        $reservation_id = intval($_POST['reservation_id']);
        $user_id = $_SESSION['id'];

        // Check if the reservation exists and if it belongs to the logged-in user
        $check_reservation = $conn->query("SELECT * FROM user_reservations WHERE id = $reservation_id AND user_id = $user_id");

        if ($check_reservation->num_rows > 0) {
            // Proceed to delete the reservation
            $delete_query = "DELETE FROM user_reservations WHERE id = $reservation_id AND user_id = $user_id";
            if ($conn->query($delete_query) === TRUE) {
                $message = "Reservation deleted successfully!";
            } else {
                $message = "Error deleting reservation: " . $conn->error;
            }
        } else {
            $message = "You do not have permission to delete this reservation.";
        }
    }

    // Prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all available equipment
$reservations_result = $conn->query("
    SELECT inventory.id AS equipment_id, 
           inventory.name, 
           inventory.quantity AS total_quantity, 
           COALESCE(SUM(user_reservations.quantity), 0) AS reserved_quantity 
    FROM inventory 
    LEFT JOIN user_reservations 
           ON inventory.id = user_reservations.equipment_id 
           AND user_reservations.reserved_from < NOW() + INTERVAL 1 MONTH 
           AND user_reservations.reserved_to > NOW()
    GROUP BY inventory.id
");

// Prepare equipment slots array
$equipment_slots = [];
while ($row = $reservations_result->fetch_assoc()) {
    $equipment_slots[] = [
        'equipment_id' => $row['equipment_id'],
        'name' => $row['name'],
        'available_inventory' => max(0, $row['total_quantity'] - $row['reserved_quantity']),
    ];
}

// Fetch user's reservations
$user_id = $_SESSION['id'];
$user_reservations = $conn->query("
    SELECT user_reservations.id, 
           inventory.name, 
           user_reservations.reserved_from, 
           user_reservations.reserved_to, 
           user_reservations.quantity 
    FROM user_reservations 
    JOIN inventory ON user_reservations.equipment_id = inventory.id
    WHERE user_reservations.user_id = $user_id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Equipment Reservation</title>
    <style>
/* General Layout */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f7fc;
    margin: 0;
    padding: 20px;
    color: #333;
}

h1 {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: #007BFF;
}

h2 {
    text-align: center;
    font-size: 1.75rem;
    margin-bottom: 10px;
    color: #333;
}

/* Notice Board */
.notice {
    background: linear-gradient(145deg, #f0f8ff, #e6f7ff);  /* Soft gradient background */
    border: 2px solid #007BFF;  /* A more pronounced border color */
    border-radius: 8px;
    padding: 30px;
    margin: 50px;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);  /* Stronger box shadow */
    font-family: 'Arial', sans-serif;
    color: #333;
    position: relative; /* For positioning icons */
}

.notice::before {
    content: "⚠️";  /* Adding a warning icon */
    position: absolute;
    top: 20px;
    left: 20px;
    font-size: 3rem;  /* Larger icon */
    color: #007BFF;  /* Color matching border */
}

.notice h2 {
    font-size: 2rem;
    margin-bottom: 15px;
    font-weight: bold;
    color: #007BFF;  /* Make heading standout with color */
}

.notice ul {
    list-style: none;
    padding-left: 0;
    line-height: 1.8;
}

.notice ul li {
    font-size: 1.2rem;
    margin: 12px 0;
    font-weight: 500;
}

.notice ul li::before {
    content: "✔️"; /* Add a checkmark for each item */
    margin-right: 10px;
    color: #28a745;  /* Green checkmark */
}

/* Optional: Add hover effect to notice box */
.notice:hover {
    box-shadow: 0 8px 20px rgba(0, 123, 255, 0.3);  /* Slightly bigger shadow on hover */
    transform: translateY(-5px);
    transition: all 0.3s ease;
}


/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

th, td {
    padding: 12px;
    text-align: center;
    border: 1px solid #ddd;
}

th {
    background-color: #007BFF;
    color: #fff;
    font-size: 1.1rem;
}

td {
    background-color: #fff;
    font-size: 1rem;
}

/* Table Rows - Hover Effect */
tr:hover {
    background-color: #f7f7f7;
}

button {
    background-color: #28a745;
    color: #fff;
    border: none;
    padding: 10px 15px;
    font-size: 1rem;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #218838;
}

button:focus {
    outline: none;
}

/* Floating Home Button */
.floating-button {
    position: fixed;
    left: 20px;
    top: 20px;
    background-color: #007BFF;
    color: transparent; /* Hide text */
    border: none;
    padding: 15px;
    border-radius: 50%;
    text-align: center;
    text-decoration: none;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

/* Logo inside Floating Button */
.home-logo {
    width: 50px;  /* Adjust the size of the logo */
    height: 50px;
    object-fit: contain;
}

/* Hover Effect for Floating Button */
.floating-button:hover {
    background-color: #0056b3;
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}

/* Floating Button Focus */
.floating-button:focus {
    outline: none;
}


/* Form Styles */
form {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #ddd;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-top: 30px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

label {
    font-size: 1rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 10px;
    display: block;
}

input, select {
    width: 100%;
    padding: 12px;
    border-radius: 4px;
    border: 1px solid #ddd;
    margin-bottom: 20px;
    font-size: 1rem;
    box-sizing: border-box;
}

input[type="number"] {
    width: 100%;
}

input[type="datetime-local"] {
    padding: 10px;
}

/* Responsive Design */
@media (max-width: 768px) {
    body {
        padding: 10px;
    }

    h1 {
        font-size: 2rem;
    }

    h2 {
        font-size: 1.5rem;
    }

    table, form {
        width: 100%;
        padding: 10px;
    }

    .notice {
        padding: 15px;
    }

    .floating-button {
        padding: 12px;
        font-size: 3.0rem;
    }
}

/* Alert Messages */
p {
    font-size: 1rem;
    text-align: center;
    margin: 15px 0;
}

p.success {
    color: green;
}

p.error {
    color: red;
}

/* Cards */
.card {
    background-color: #ffffff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}


    </style>
</head>
<body>
<div>
    <!-- Floating Home Button -->
<!-- Floating Home Button with Logo -->
<a href="home.php" id="homeButton" class="floating-button">
    <img src="images/logo.png" alt="Home Logo" class="home-logo">
</a>

    <h1>Reserve your Inventory</h1>

    

    <!-- Display Messages -->
    <?php if ($message): ?>
        <p style="color: <?php echo strpos($message, 'success') !== false ? 'green' : 'red'; ?>;">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>

    <!-- Available Equipment and Slots Table -->
    <h2>Available Equipment and Slots</h2>
    <table>
        <tr>
            <th>Equipment ID</th>
            <th>Equipment Name</th>
            <th>Available Inventory</th>
        </tr>
        <?php if (!empty($equipment_slots)): ?>
            <?php foreach ($equipment_slots as $slot): ?>
                <tr>
                    <td><?php echo $slot['equipment_id']; ?></td>
                    <td><?php echo $slot['name']; ?></td>
                    <td><?php echo $slot['available_inventory']; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">No reservations found.</td>
            </tr>
        <?php endif; ?>
    </table>

    <!-- User's Reservations Table -->
    <h2>Your Reservations</h2>
    <table>
        <tr>
            <th>Reservation ID</th>
            <th>Equipment Name</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Quantity</th>
            <th>Actions</th>
        </tr>
        <?php if ($user_reservations && $user_reservations->num_rows > 0): ?>
            <?php while ($user_reservation = $user_reservations->fetch_assoc()): ?>
            <tr>
                <td><?php echo $user_reservation['id']; ?></td>
                <td><?php echo $user_reservation['name']; ?></td>
                <td><?php echo $user_reservation['reserved_from']; ?></td>
                <td><?php echo $user_reservation['reserved_to']; ?></td>
                <td><?php echo $user_reservation['quantity']; ?></td>
                <td>
                    <!-- Delete Button -->
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" style="display:inline;" onsubmit="return confirmDelete();">
                        <input type="hidden" name="reservation_id" value="<?php echo $user_reservation['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" name="delete_reservation">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">You have no reservations.</td>
            </tr>
        <?php endif; ?>
    </table>

    <!-- Notice Board -->
    <div class="notice">
        <h2>Notice Board</h2>
        <ul>
            <li>Reservations must be made for the same date.</li>
            <li>Quantity must be a positive value.</li>
            <li>Each reservation is strictly for 1 hour.</li>
            <li>Ensure you do not double-book the same equipment and time slot.</li>
        </ul>
    </div>
    <!-- Reservation Form -->
    <h2>Reserve Equipment</h2>
<form id="reservationForm" method="post" action="">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <label for="equipment_id">Equipment:</label>
    <select name="equipment_id" id="equipment_id" required>
        <option value="" disabled selected>Select equipment</option>
        <?php foreach ($equipment_slots as $slot): ?>
            <option value="<?php echo $slot['equipment_id']; ?>">
                <?php echo $slot['name']; ?>
            </option>
        <?php endforeach; ?>
    </select>
    <label for="reserved_from">Start Time:</label>
    <input type="datetime-local" name="reserved_from" id="reserved_from" required>
    <label for="reserved_to">End Time:</label>
    <input type="datetime-local" name="reserved_to" id="reserved_to" required>
    <label for="quantity">Quantity:</label>
    <input type="number" name="quantity" id="quantity" min="1" required>
    <button type="submit" name="reserve_equipment">Reserve</button>
</form>
</div>
</body>
<script>
    document.getElementById("reservationForm").addEventListener("submit", function(event) {
        // Get form values
        const reservedFrom = document.getElementById("reserved_from").value;
        const reservedTo = document.getElementById("reserved_to").value;
        const quantity = document.getElementById("quantity").value;

        if (!reservedFrom || !reservedTo || !quantity) {
            alert("Please fill out all fields.");
            event.preventDefault();
            return;
        }

        const fromDate = new Date(reservedFrom);
        const toDate = new Date(reservedTo);

        // Check if reserved_from and reserved_to are on the same date
        if (fromDate.toDateString() !== toDate.toDateString()) {
            alert("Start and end times must be on the same date.");
            event.preventDefault();
            return;
        }

        // Check if reservation duration is exactly 1 hour
        const duration = (toDate - fromDate) / (1000 * 60 * 60); // Convert milliseconds to hours
        if (duration !== 1) {
            alert("Reservations must be exactly 1 hour long.");
            event.preventDefault();
            return;
        }

        // Check if quantity is a positive value
        if (quantity <= 0) {
            alert("Quantity must be a positive value.");
            event.preventDefault();
            return;
        }

        // If all validations pass, allow the form to be submitted
    });
</script>
<script>
    function confirmDelete() {
        // Display the confirmation dialog
        var result = confirm("Are you sure you want to delete this reservation?");
        // If the user clicks "OK", return true to submit the form
        // If they click "Cancel", return false to prevent form submission
        return result;
    }
</script>
</html>
