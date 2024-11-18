document
  .getElementById("downloadReportBtn")
  .addEventListener("click", async function () {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    const reportDate = new Date().toLocaleDateString();
    const logoUrl = "../images/logo.png";

    // Fetch the image and convert it to base64
    let logoBase64;
    try {
      logoBase64 = await fetchImageAsBase64(logoUrl);
    } catch (error) {
      console.error("Failed to load logo image:", error);
      logoBase64 = null; // Set to null or fallback image
    }

    // Add logo and date to PDF
    if (logoBase64) {
      doc.addImage(logoBase64, "PNG", 84, 10, 30, 30); // Position and size of logo
    }
    doc.setFontSize(18);
    doc.text("Reports and Metrics", 14, 40);
    doc.setFontSize(12);
    doc.text(`Date: ${reportDate}`, 160, 20, { align: "right" });

    // Add Total Users Table
    doc.text("Total Users", 14, 50);

    const userTable = document.getElementById("userTable");
    const userTableData = [];
    Array.from(userTable.rows).forEach((row, index) => {
      if (index !== 0) {
        // Skip header row
        const rowData = Array.from(row.cells).map((cell) => cell.innerText);
        userTableData.push(rowData);
      }
    });

    if (doc.autoTable) {
      doc.autoTable({
        startY: 60,
        head: [["ID", "Username", "Email", "Actions"]],
        body: userTableData,
        theme: "grid",
        margin: { top: 10 },
        styles: {
          fontSize: 10,
          cellPadding: 2,
          halign: "center",
        },
      });
    } else {
      console.error("autoTable plugin is not loaded.");
    }

    // Add Course Enrollment Chart
    let finalY = doc.previousAutoTable ? doc.previousAutoTable.finalY : 80;
    doc.text("Course Enrollments", 14, finalY + 10);
    const courseChart = document.getElementById("courseChart");
    if (courseChart) {
      const courseChartImage = courseChart.toDataURL("image/png");
      doc.addImage(courseChartImage, "PNG", 14, finalY + 20, 180, 100);
    }

    // Check if Enrollment Status should start on a new page
    finalY = finalY + 400; // Approximate height from the previous section
    if (finalY > 250) {
      // Threshold near page bottom
      doc.addPage();
      finalY = 10; // Reset to top of the new page
    }

    // Enrollment Status Chart on the new page or continued page
    doc.text("Enrollment Status", 14, finalY);
    const enrollmentStatusChart = document.getElementById(
      "enrollmentStatusChart"
    );
    if (enrollmentStatusChart) {
      const enrollmentStatusImage =
        enrollmentStatusChart.toDataURL("image/png");
      doc.addImage(enrollmentStatusImage, "PNG", 14, finalY + 10, 180, 100);
    }

    // Add Attendance Table on the same page
    finalY += 120; // Adjust starting Y position for attendance table if needed
    doc.text("Attendance Distribution", 14, finalY + 10);

    // Attendance Table Data
    const attendanceTable = document.getElementById("attendanceTable");
    const attendanceTableData = [];
    Array.from(attendanceTable.rows).forEach((row, index) => {
      if (index !== 0) {
        // Skip header row
        const rowData = Array.from(row.cells).map((cell) => cell.innerText);
        attendanceTableData.push(rowData);
      }
    });

    // Add Attendance Table using autoTable on the same page
    if (attendanceTableData.length > 0) {
      doc.autoTable({
        startY: finalY + 20, // Positioning after the title
        head: [["ID", "Username", "Email", "Status", "Date"]],
        body: attendanceTableData,
        theme: "grid",
        margin: { top: 10 },
        styles: {
          fontSize: 10,
          cellPadding: 2,
          halign: "center",
        },
      });
    } else {
      console.warn("No attendance data available for the table.");
    }

    // Save the PDF
    doc.save("report.pdf");
  });

// Helper function to fetch an image and convert it to base64
async function fetchImageAsBase64(url) {
  try {
    const response = await fetch(url);
    const blob = await response.blob();
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onloadend = () => resolve(reader.result);
      reader.onerror = () =>
        reject(new Error("Failed to convert image to base64"));
      reader.readAsDataURL(blob);
    });
  } catch (error) {
    console.error("Error fetching image:", error);
    throw error;
  }
}
