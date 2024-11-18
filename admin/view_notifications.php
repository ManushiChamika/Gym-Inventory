<?php
include("update_enrollment_status.php");
$pendingEnrollments = [];  // Initialize as an empty array

$query = "
    SELECT e.id AS enrollment_id, e.user_id, e.course_id, u.username, u.email, c.title AS course_title, c.description AS course_description 
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    WHERE e.status = 'pending'
";

$result = mysqli_query($conn, $query);

// Check if query was successful and fetch the data
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pendingEnrollments[] = $row; // Store the results in the array
    }
} else {
    echo "Error: " . mysqli_error($conn);  // Output query error if any
}

// Get the count of pending enrollments
$pendingCount = count($pendingEnrollments);
?>

<!-- Modal for Enrollment Requests -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Pending Enrollment Requests</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (count($pendingEnrollments) > 0): ?>
                    <?php foreach ($pendingEnrollments as $enrollment): ?>
                        <h5><?php echo $enrollment['course_title']; ?></h5>
                        <p><?php echo $enrollment['course_description']; ?></p>

                        <h6>Students Requesting Enrollment:</h6>
                        <ul>
                            <li>
                                <strong><?php echo $enrollment['username']; ?></strong> (<?php echo $enrollment['email']; ?>)
                                <button class="btn btn-success btn-sm approve-btn" data-enrollment-id="<?php echo $enrollment['enrollment_id']; ?>">Approve</button>
                                <button class="btn btn-danger btn-sm reject-btn" data-enrollment-id="<?php echo $enrollment['enrollment_id']; ?>">Reject</button>
                            </li>
                        </ul>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No pending enrollment requests at this moment.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="../js/adminApproveReject.js"></script>