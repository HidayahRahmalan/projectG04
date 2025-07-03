// js/admin_modal.js

// Get the modal element
var modal = document.getElementById("actionModal");

// Get the elements inside the modal that we need to update
var modalAppointmentIdSpan = document.getElementById("modalAppointmentId");
var rescheduleBtn = document.getElementById("rescheduleBtn");
var cancelBtn = document.getElementById("cancelBtn");
var deleteBtn = document.getElementById("deleteBtn");

// Function to open the modal
function openActionModal(appointmentId) {
    // Update the content of the modal with the specific appointment ID
    modalAppointmentIdSpan.textContent = appointmentId;

    // Update the href attributes of the buttons inside the modal
    // Note: reschedule.php is a placeholder for now
    rescheduleBtn.href = 'admin_reschedule_appointment.php?id=' + appointmentId;
    cancelBtn.href = 'admin_cancel_appointment.php?id=' + appointmentId;
    deleteBtn.href = 'admin_delete_appointment.php?id=' + appointmentId;

    // Show the modal
    modal.style.display = "block";
}

// Function to close the modal
function closeActionModal() {
    modal.style.display = "none";
}

// Close the modal if the user clicks anywhere outside of the modal content
window.onclick = function(event) {
    if (event.target == modal) {
        closeActionModal();
    }
}