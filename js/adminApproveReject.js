document.addEventListener("DOMContentLoaded", function () {
  // Select all approve and reject buttons
  const approveButtons = document.querySelectorAll(".approve-btn");
  const rejectButtons = document.querySelectorAll(".reject-btn");

  // Add click event listeners to approve and reject buttons
  approveButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      const enrollmentId = this.getAttribute("data-enrollment-id");
      handleEnrollmentAction(enrollmentId, "approved", this);
    });
  });

  rejectButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      const enrollmentId = this.getAttribute("data-enrollment-id");
      handleEnrollmentAction(enrollmentId, "rejected", this);
    });
  });

  function handleEnrollmentAction(enrollmentId, status, button) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "update_enrollment_status.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
      if (xhr.status === 200) {
        // Notify success with different colors based on the status
        const enrollmentItem = button.closest("li");
        const message = document.createElement("p");

        // Set the alert class based on the status
        if (status === "approved") {
          message.className = "alert alert-success";
          message.innerText = "Enrollment approved.";
        } else if (status === "rejected") {
          message.className = "alert alert-danger";
          message.innerText = "Enrollment rejected.";
        }

        // Replace the enrollment item with the status message
        enrollmentItem.replaceWith(message);

        // Remove the message after a short delay to show the next request
        setTimeout(() => {
          message.remove();
          // Check if there are any more enrollments left
          if (!document.querySelector(".approve-btn")) {
            document.querySelector(".modal-body").innerHTML =
              "<p>No more pending enrollment requests.</p>";
          }
        }, 2000); // Adjust delay as needed
      } else {
        alert("An error occurred while updating enrollment status.");
      }
    };
    xhr.send("enrollment_id=" + enrollmentId + "&status=" + status);
  }

  // Listen for the modal close event to refresh the page
  const notificationModal = document.getElementById("notificationModal");
  notificationModal.addEventListener("hidden.bs.modal", function () {
    location.reload(); // Refresh the page when the modal is closed
  });
});
