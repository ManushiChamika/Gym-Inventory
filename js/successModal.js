// Function to display success modal
function showSuccessModal(message) {
  document.getElementById("successMessage").innerText = message;
  document.getElementById("successModal").style.display = "block";
}

// Function to close the success modal
function closeSuccessModal() {
  document.getElementById("successModal").style.display = "none";
}

// Check for success status in URL parameters
window.onload = function () {
  const urlParams = new URLSearchParams(window.location.search);
  const status = urlParams.get("status");
  const action = urlParams.get("action");

  if (status === "success") {
    const message =
      action === "added"
        ? "Course added successfully!"
        : action === "updated"
        ? "Course updated successfully!"
        : "Course deleted successfully!";

    showSuccessModal(message);

    // Clear the URL parameters after showing the modal
    const newUrl = window.location.href.split("?")[0];
    window.history.replaceState({}, document.title, newUrl);
  }
};
