<?php
session_start();
include("../connection.php");

// Initialize a variable to hold success messages
$successMessage = "";

// Handle add inventory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_inventory'])) {
    $name = $_POST['name'];
    $usage_status = $_POST['usage_status'];
    $next_maintenance = $_POST['next_maintenance'];
    $quantity = $_POST['quantity'];

    $sql = "INSERT INTO inventory (name, usage_status, next_maintenance, quantity)
            VALUES ('$name', '$usage_status', '$next_maintenance', '$quantity')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "New inventory added successfully!";
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $successMessage = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle delete inventory
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($conn->query("DELETE FROM inventory WHERE id=$id") === TRUE) {
        $_SESSION['message'] = "Inventory deleted successfully!";
    } else {
        $_SESSION['message'] = "Error deleting record: " . $conn->error;
    }
    header("Location: admin_dashboard.php");
    exit;
}

// Handle update inventory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_inventory'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $usage_status = $_POST['usage_status'];
    $next_maintenance = $_POST['next_maintenance'];
    $quantity = $_POST['quantity'];

    $sql = "UPDATE inventory SET 
            name='$name', 
            usage_status='$usage_status', 
            next_maintenance='$next_maintenance', 
            quantity='$quantity' 
            WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Inventory updated successfully!";
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $successMessage = "Error updating record: " . $conn->error;
    }
}

// Fetch inventory
$result = $conn->query("SELECT * FROM inventory");

// Check for success message in the session
if (isset($_SESSION['message'])) {
    $successMessage = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying it
}

// Handle new reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_equipment'])) {
    $equipment_id = $_POST['equipment_id'];
    $reserved_from = $_POST['reserved_from'];
    $reserved_to = $_POST['reserved_to'];
    $requested_quantity = $_POST['quantity'];

    // Ensure start time is less than end time
    if (strtotime($reserved_from) >= strtotime($reserved_to)) {
        $_SESSION['message'] = "Start time must be earlier than end time!";
        header("Location: admin_dashboard.php");
        exit;
    }

    // Validate reservation duration (must not exceed 1 hour)
    $duration = (strtotime($reserved_to) - strtotime($reserved_from)) / 3600;
    if ($duration > 1) {
        $_SESSION['message'] = "Reservations cannot exceed 1 hour!";
        header("Location: admin_dashboard.php");
        exit;
    }

    // Check for conflicts and available quantity
    $conflict_check = $conn->query("
        SELECT SUM(quantity) as reserved_quantity 
        FROM reservations 
        WHERE equipment_id=$equipment_id AND 
              ('$reserved_from' < reserved_to AND '$reserved_to' > reserved_from)
    ");

    $conflict = $conflict_check->fetch_assoc();
    $reserved_quantity = $conflict['reserved_quantity'] ?? 0;

    // Fetch the total quantity of the equipment
    $inventory_result = $conn->query("SELECT quantity FROM inventory WHERE id=$equipment_id");
    $inventory = $inventory_result->fetch_assoc();
    $available_quantity = $inventory['quantity'] - $reserved_quantity;

    if ($requested_quantity > $available_quantity) {
        $_SESSION['message'] = "Only $available_quantity units available during this time slot!";
    } else {
        // Proceed with reservation
        $sql = "INSERT INTO reservations (equipment_id, reserved_from, reserved_to, quantity) 
                VALUES ($equipment_id, '$reserved_from', '$reserved_to', $requested_quantity)";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['message'] = "Reservation added successfully!";
        } else {
            $_SESSION['message'] = "Error: " . $conn->error;
        }
    }
    header("Location: admin_dashboard.php");
    exit;
}

// Add inventory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_inventory'])) {
    $name = $_POST['name'];
    $usage_status = $_POST['usage_status'];
    $next_maintenance = $_POST['next_maintenance'];
    $quantity = $_POST['quantity'];

    $sql = "INSERT INTO inventory (name, usage_status, next_maintenance, quantity)
            VALUES ('$name', '$usage_status', '$next_maintenance', '$quantity')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "New inventory added successfully!";
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $successMessage = "Error: " . $sql . "<br>" . $conn->error;
    }
}
// Fetch reservations
$reservations_result = $conn->query("
    SELECT reservations.id, inventory.name, reservations.reserved_from, reservations.reserved_to, reservations.quantity
    FROM reservations
    JOIN inventory ON reservations.equipment_id = inventory.id
");

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Inventory Dashboard</title>

   <style>
        /* General Body Styling */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Floating Home Button on Left Side */
        .home-button {
            position: fixed;
            top: 20px;  /* Distance from the top of the page */
            left: 20px;  /* Distance from the left of the page */
            background-color: #4CAF50;  /* Button color */
            color: white;
            padding: 15px 25px;  /* Padding around the text */
            border-radius: 50px;  /* Rounded corners for circular look */
            text-decoration: none;  /* Remove underline from link */
            font-size: 18px;
            z-index: 1000;  /* Ensure the button stays above other content */
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease;
        }

        .home-button:hover {
            background-color: #45a049;  /* Darker green on hover */
            transform: scale(1.1);
        }

        /* Container Styling */
        .container {
            padding: 30px;
            margin-left: 100px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        /* Table Styling */
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

        /* Form Styling */
        form {
            background-color: #fff;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }

        form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 16px;
        }

        form button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        form button:hover {
            background-color: #45a049;
        }

        /* Success Message Popup */
        .popup-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
            text-align: center;
            z-index: 1000;
            font-size: 18px;
            display: <?php echo $successMessage ? 'block' : 'none'; ?>;
        }

        /* Overlay for Background Opacity */
        .overlay {
            display: <?php echo $successMessage ? 'block' : 'none'; ?>;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8); /* semi-transparent black */
            z-index: 999;
        }

        /* Close Button for Popup */
        .close-popup {
            cursor: pointer;
            font-weight: bold;
            color: #fff;
            position: absolute;
            top: 5px;
            right: 10px;
        }
        /* Edit and Delete Buttons Styling */
        .edit-btn,
        .delete-btn {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        /* Edit Button */
        .edit-btn {
            background-color: #4CAF50;  /* Green for edit */
            color: white;
            border: 1px solid #4CAF50;
        }

        .edit-btn:hover {
            background-color: #45a049;  /* Darker green on hover */
            border-color: #45a049;
        }

        /* Delete Button */
        .delete-btn {
            background-color: #f44336;  /* Red for delete */
            color: white;
            border: 1px solid #f44336;
        }

        .delete-btn:hover {
            background-color: #e53935;  /* Darker red on hover */
            border-color: #e53935;
        }

        /* Optional: Add margin between buttons */
        .edit-btn, .delete-btn {
            margin-right: 10px;
        }


   </style>
</head>
<body>
    <div class="container">
        <a href="home.php" class="home-button">Home</a>

        <!-- Display Popup Message -->
        <?php if ($successMessage): ?>
        <div class="popup-message" id="popup-message">
            <span class="close-popup" onclick="document.getElementById('popup-message').style.display='none';">&times;</span>
            <?php echo $successMessage; ?>
        </div>
        <?php endif; ?>

        <h1>Admin Dashboard - Inventory Management</h1>

        <!-- Inventory Table -->
        <h2>Current Inventory</h2>
        <table>
            <tr>
                <th>Equipment</th>
                <th>Usage Status</th>
                <th>Next Maintenance</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['usage_status']; ?></td>
                <td><?php echo $row['next_maintenance']; ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td>
                    <a href="admin_dashboard.php?edit=<?php echo $row['id']; ?>" class="edit-btn">Edit</a>
                    <a href="admin_dashboard.php?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <!-- Add New Inventory Form -->
        <h2>Add New Inventory</h2>
        <form action="admin_dashboard.php" method="post">
            <label>Equipment Name:</label>
            <input type="text" name="name" required>

            <label>Usage Status:</label>
            <input type="text" name="usage_status" required>

            <label>Next Maintenance:</label>
            <input type="date" name="next_maintenance" required>

            <label>Quantity:</label>
            <input type="number" name="quantity" required>

            <button type="submit" name="add_inventory">Add Inventory</button>
        </form>

     
        
    </div>
        <?php
        // If edit is clicked, fetch the data for that item
        if (isset($_GET['edit'])) {
            $id = $_GET['edit'];
            $edit_result = $conn->query("SELECT * FROM inventory WHERE id=$id");
            $edit_row = $edit_result->fetch_assoc();
        ?>
        
        <!-- Update Inventory Form -->
        <h2>Edit Inventory</h2>
        <form action="admin_dashboard.php" method="post">
            <input type="hidden" name="id" value="<?php echo $edit_row['id']; ?>">
            <label>Equipment Name:</label>
            <input type="text" name="name" value="<?php echo $edit_row['name']; ?>" required>

            <label>Usage Status:</label>
            <input type="text" name="usage_status" value="<?php echo $edit_row['usage_status']; ?>" required>

            <label>Next Maintenance:</label>
            <input type="date" name="next_maintenance" value="<?php echo $edit_row['next_maintenance']; ?>" required>

            <label>Quantity:</label>
            <input type="number" name="quantity" value="<?php echo $edit_row['quantity']; ?>" required>

            <button type="submit" name="update_inventory">Update Inventory</button>
        </form>
        <?php } ?>
    </div>

    <script>
    // Hide popup after 3 seconds
    setTimeout(function() {
        var popup = document.getElementById('popup-message');
        if (popup) popup.style.display = 'none';
    }, 3000);

    // Prevent reservations longer than 1 hour
    document.querySelector('form').addEventListener('submit', function(e) {
        const start = new Date(document.querySelector('[name="reserved_from"]').value);
        const end = new Date(document.querySelector('[name="reserved_to"]').value);
        const diff = (end - start) / (1000 * 60 * 60);

        if (diff > 1) {
            alert("Reservations cannot exceed 1 hour.");
            e.preventDefault();
        }
    });
    
</script>
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const start = new Date(document.querySelector('[name="reserved_from"]').value);
    const end = new Date(document.querySelector('[name="reserved_to"]').value);

    // Ensure start time is less than end time
    if (start >= end) {
        alert("Start time must be earlier than end time.");
        e.preventDefault();
        return;
    }

    // Ensure reservation duration does not exceed 1 hour
    const diff = (end - start) / (1000 * 60 * 60);
    if (diff > 1) {
        alert("Reservations cannot exceed 1 hour.");
        e.preventDefault();
    }
});

</script>


</body>
</html>
