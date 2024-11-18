// Function to open the modal with empty fields for adding a new course
document.getElementById("addCourseBtn").addEventListener("click", function () {
  // Clear form fields
  document.getElementById("courseId").value = "";
  document.getElementById("title").value = "";
  document.getElementById("description").value = "";
  document.getElementById("category").selectedIndex = 0; // Reset category to the first option
  document.getElementById("date").value = "";
  document.getElementById("image").value = ""; // Clear any previously uploaded file

  // Set modal title and button text for "Add Course" mode
  document.getElementById("modalTitle").innerText = "Add New Course";
  document.getElementById("submitBtn").innerText = "Add Course";

  // Open the modal
  document.getElementById("courseModal").style.display = "block";
});

// Function to open the modal with pre-filled fields for editing an existing course
function openModal(id, title, category, description, date) {
  // Fill the form fields with the selected course data
  document.getElementById("courseId").value = id;
  document.getElementById("title").value = title;
  document.getElementById("description").value = description;
  document.getElementById("category").value = category;
  document.getElementById("date").value = date;

  // Set modal title and button text for "Edit Course" mode
  document.getElementById("modalTitle").innerText = "Edit Course";
  document.getElementById("submitBtn").innerText = "Save Changes";

  // Open the modal
  document.getElementById("courseModal").style.display = "block";
}

// Close modal function
document.getElementById("closeModal").addEventListener("click", function () {
  document.getElementById("courseModal").style.display = "none";
});

// Close the modal if clicking outside of the modal content
window.addEventListener("click", function (event) {
  if (event.target === document.getElementById("courseModal")) {
    document.getElementById("courseModal").style.display = "none";
  }
});
