document.addEventListener('DOMContentLoaded', function() {
    // Enable Bootstrap tooltips if you use them
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Logic for the "Resolve Task" modal in my_tasks.php
    const resolveModal = document.getElementById('resolveModal');
    if (resolveModal) {
        resolveModal.addEventListener('show.bs.modal', function(event) {
            // Button that triggered the modal
            const button = event.relatedTarget;
            // Extract info from data-bs-* attributes
            const reportId = button.getAttribute('data-bs-report-id');
            // Update the modal's content.
            const modalReportIdInput = resolveModal.querySelector('#modal_report_id');
            modalReportIdInput.value = reportId;
        });
    }
});