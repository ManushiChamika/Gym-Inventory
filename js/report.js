document
  .getElementById("reportModal")
  .addEventListener("shown.bs.modal", function () {
    // Fetch and populate the user data every time the modal is shown
    loadUserData();
  });

// Function to fetch user data and update the table
function loadUserData() {
  fetch("../admin/report.php")
    .then((response) => response.json())
    .then((data) => {
      const userTableBody = document.getElementById("userTableBody");
      userTableBody.innerHTML = ""; // Clear any existing rows

      data.users.forEach((user) => {
        const row = document.createElement("tr");
        row.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td><button class="btn btn-danger btn-sm" onclick="removeUser(${user.id})">Remove</button></td>
                `;
        userTableBody.appendChild(row);
      });

      // Generate charts using the fetched data
      generateCharts(data);
    })
    .catch((error) => console.error("Error fetching data:", error));
}

function generateCharts(data) {
  // Course Enrollment Chart
  const courseLabels =
    data.enrollmentsData && Array.isArray(data.enrollmentsData)
      ? data.enrollmentsData.map((course) => course.title)
      : [];

  const courseCounts =
    data.enrollmentsData && Array.isArray(data.enrollmentsData)
      ? data.enrollmentsData.map((course) => course.enrollment_count)
      : [];

  if (courseLabels.length > 0 && courseCounts.length > 0) {
    new Chart(document.getElementById("courseChart"), {
      type: "bar",
      data: {
        labels: courseLabels,
        datasets: [
          {
            label: "Enrollments per Course",
            data: courseCounts,
            backgroundColor: "rgba(255, 99, 132, 0.6)",
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    });
  }

  // Enrollment Status Bar Chart for Each Course
  const courseNames = Object.keys(data.enrollmentsStatusData);
  const pendingData = [];
  const approvedData = [];
  const rejectedData = [];
  const backgroundColors = {
    pending: "rgba(169, 169, 169, 0.6)", // Grey for Pending
    approved: "rgba(0, 255, 0, 0.6)", // Green for Approved
    rejected: "rgba(255, 0, 0, 0.6)", // Red for Rejected
  };

  courseNames.forEach((course) => {
    const statusData = data.enrollmentsStatusData[course];
    pendingData.push(statusData.pending);
    approvedData.push(statusData.approved);
    rejectedData.push(statusData.rejected);
  });

  new Chart(document.getElementById("enrollmentStatusChart"), {
    type: "bar",
    data: {
      labels: courseNames, // Course names
      datasets: [
        {
          label: "Pending Enrollments",
          data: pendingData,
          backgroundColor: backgroundColors.pending,
          borderColor: "rgba(169, 169, 169, 1)",
          borderWidth: 1,
        },
        {
          label: "Approved Enrollments",
          data: approvedData,
          backgroundColor: backgroundColors.approved,
          borderColor: "rgba(0, 255, 0, 1)",
          borderWidth: 1,
        },
        {
          label: "Rejected Enrollments",
          data: rejectedData,
          backgroundColor: backgroundColors.rejected,
          borderColor: "rgba(255, 0, 0, 1)",
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "top",
        },
        tooltip: {
          callbacks: {
            label: function (tooltipItem) {
              return (
                tooltipItem.dataset.label +
                ": " +
                tooltipItem.raw +
                " enrollments"
              );
            },
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: "Number of Enrollments",
          },
        },
      },
    },
  });

  // Attendance Table
  const attendanceTableBody = document.getElementById("attendanceTableBody");
  attendanceTableBody.innerHTML = ""; // Clear existing rows

  data.attendanceData.forEach((attendance) => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${attendance.id}</td>
      <td>${attendance.username}</td>
      <td>${attendance.email}</td>
      <td>${attendance.status}</td>
      <td>${attendance.date === null ? "" : attendance.date}</td>
    `;
    attendanceTableBody.appendChild(row);
  });
}

// Function to remove a user and refresh the table
function removeUser(userId) {
  if (
    confirm(
      "Are you sure you want to delete this student? This will remove all their enrollments as well."
    )
  ) {
    fetch(`../admin/delete_user.php?id=${userId}`, {
      method: "DELETE",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Student removed successfully.");
          // Re-fetch the user data to refresh the table
          loadUserData();
        } else {
          alert(`Failed to remove Student: ${data.error}`);
        }
      })
      .catch((error) => console.error("Error deleting student:", error));
  }
}
