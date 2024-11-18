<?php
include("connection.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $facility = $_POST['facility'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Check if the selected facility is available in inventory
    $inventory_check = $conn->query("SELECT * FROM inventory WHERE name='$facility' AND quantity > 0");

    if ($inventory_check->num_rows > 0) {
        // Insert booking record
        $conn->query("INSERT INTO bookings (facility, date, time, quantity) VALUES ('$facility', '$date', '$time', 1)");

        // Update inventory
        $conn->query("UPDATE inventory SET quantity = quantity - 1 WHERE name='$facility'");

        echo "Booking successful and inventory updated!";
    } else {
        echo "Selected facility is not available.";
    }
}

$conn->close();
?>
