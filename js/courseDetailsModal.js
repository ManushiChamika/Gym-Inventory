let currentCourse = null; // To hold the current course selected

// This function is triggered when you click on the course card (opens the course modal)
function openModal(course) {
  currentCourse = course; // Save the selected course globally

  // Set the course image in the course modal
  document.getElementById("modalImage").src = course.image_path
    ? "admin/" + course.image_path
    : "images/default_image.jpg";

  // Set the course details in the course modal
  document.getElementById("modalTitle").innerText = course.title;
  document.getElementById("modalCategory").innerText =
    "Category: " + course.category;
  document.getElementById("modalDate").innerText =
    "Date: " + new Date(course.date).toLocaleDateString();
  document.getElementById("modalDescription").innerText = course.description;

  // Access the enroll button in the modal
  const enrollButton = document.getElementById("enrollButton");

  // Remove any existing class to reset the button styles
  enrollButton.classList.remove(
    "enroll-btn--pending",
    "enroll-btn--approved",
    "enroll-btn--rejected",
    "enroll-btn"
  );

  // Check if the course has pending, approved, or rejected status
  if (course.status === "pending") {
    enrollButton.disabled = true;
    enrollButton.classList.add("enroll-btn--pending");
    enrollButton.innerText = "Enrollment Pending";
  } else if (course.status === "approved") {
    enrollButton.disabled = true;
    enrollButton.classList.add("enroll-btn--approved");
    enrollButton.innerText = "Enrolled";
  } else if (course.status === "rejected") {
    enrollButton.disabled = true;
    enrollButton.classList.add("enroll-btn--rejected");
    enrollButton.innerText = "Enrollment Rejected";
  } else {
    enrollButton.disabled = false;
    enrollButton.classList.add("enroll-btn");
    enrollButton.innerText = "Enroll";
  }

  // Show the course modal
  document.getElementById("courseModal").style.display = "block";

  // Attach the course data to the "Enroll Now" button inside the course modal
  enrollButton.setAttribute("data-course", JSON.stringify(course)); // Store course data in the button
}

// This function is triggered when you click on the "Enroll" button inside the course modal or the card
function showConfirmationModal(event) {
  event.stopPropagation(); // Prevent event propagation to avoid triggering any parent click handlers

  // Get the course data from the clicked button's data-course attribute
  let course = null;

  // If the event target has data-course (for course cards and modal buttons), use that course data
  if (event.target.getAttribute("data-course")) {
    course = JSON.parse(event.target.getAttribute("data-course"));
  }
  // If no data-course, use the globally saved currentCourse (this happens if the click was from the modal)
  else if (currentCourse) {
    course = currentCourse;
  }

  // Check if the course data is valid (just for safety)
  if (!course) {
    console.error("No course data found!");
    return;
  }

  // Set the course details in the confirmation modal
  document.getElementById("confirmationModal").querySelector("h3").innerText =
    "Are you sure you want to enroll in the course: " + course.title + "?";

  // Close the course modal (if open) and show the confirmation modal
  document.getElementById("courseModal").style.display = "none";
  document.getElementById("confirmationModal").style.display = "block";

  // Save the course object globally to use in confirmEnrollment
  currentCourse = course;
}

// Close the confirmation modal
function closeConfirmationModal() {
  document.getElementById("confirmationModal").style.display = "none";
}

function confirmEnrollment() {
  const courseId = currentCourse.id; // Get the course ID

  // Make the POST request to enroll.php to enroll the user in the course
  fetch("enroll.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `course_id=${courseId}`, // Send the course ID via POST
  })
    .then((response) => response.text())
    .then((data) => {
      // Display server response
      alert(data);
      closeConfirmationModal();
      window.location.reload();
    })
    .catch((error) => {
      console.error("Error enrolling in course:", error);
      alert("There was an error enrolling in the course.");
      closeConfirmationModal();
    });
}

// Close the course modal (this is triggered when the user clicks the close button on the course modal)
function closeModal() {
  document.getElementById("courseModal").style.display = "none";
}
