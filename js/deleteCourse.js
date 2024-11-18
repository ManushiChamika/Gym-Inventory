function deleteCourse(courseId) {
  if (
    confirm(
      "Are you sure you want to delete this course? This action cannot be undone."
    )
  ) {
    // Make an AJAX request to delete the course
    fetch(`delete_course.php?id=${courseId}`, { method: "GET" })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Update the URL parameters to include status and action
          const newUrl = `${window.location.pathname}?status=success&action=deleted`;
          window.history.replaceState({}, document.title, newUrl);

          // Trigger the success modal
          showSuccessModal("Course deleted successfully!");

          // Optionally, remove the deleted course from the DOM
          const courseCard = document.getElementById(`course-${courseId}`);
          if (courseCard) {
            courseCard.remove();
          }

          // Update the URL to remove status and action parameters
          setTimeout(() => {
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
          }, 500);

          window.location.reload();
        } else {
          console.error("Failed to delete course:", data.message);
        }
      })
      .catch((error) => console.error("Error deleting course:", error));
  }
}
