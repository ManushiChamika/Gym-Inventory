const modal = document.getElementById("courseModal");
const addCourseBtn = document.getElementById("addCourseBtn");
const closeModal = document.getElementById("closeModal");
const submitBtn = document.getElementById("submitBtn");
const courseForm = document.getElementById("courseForm");
const modalTitle = document.getElementById("modalTitle");
const courseIdField = document.getElementById("courseId");

// Open the modal for adding a new course
addCourseBtn.onclick = function () {
  modal.style.display = "block";
  modalTitle.textContent = "Add New Course";
  courseForm.action = "upload.php"; // Default action for adding
  submitBtn.textContent = "Add Course"; // Button text for adding
  courseIdField.value = ""; // Reset the hidden field for course ID
};

// Open the modal for editing an existing course
function openModal(courseId, title, category, description, date) {
  modal.style.display = "block";
  modalTitle.textContent = "Edit Course";
  courseForm.action = "upload.php"; // Default action for editing
  submitBtn.textContent = "Save Changes"; // Button text for editing

  // Fill the form with existing course data
  courseIdField.value = courseId;
  document.getElementById("title").value = title;
  document.getElementById("category").value = category;
  document.getElementById("description").value = description;
  document.getElementById("date").value = date;
}

// Close the modal
closeModal.onclick = function () {
  modal.style.display = "none";
};
