<?php
session_start();
// Security check: Must be an admin to perform this action
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header(" code for the file you need to create next.

**Create a new file named `admin_update_patient.php`:**

```php
<?php
// Start the session and perform security checks
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("LocationLocation: login.php");
    exit();
}
require_once 'db_conn.php';

// Step 1: Check if the form was submitted correctly
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Step 2: Get the data from the form
    // Use intval for: login.php");
    exit();
}
require_once 'db_conn.php';

// Step 1: Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') the ID and trim for text fields to remove extra whitespace
    $patient_id = intval($_POST['patient_id']);
    $name = trim($_POST['name']);
    $ic_number = trim($_POST['ic {
    
    // Step 2: Validate and sanitize the submitted data
    // Use intval for numbers and trim for strings to remove whitespace
    $patient_id = intval($_POST['patient_id']);
    $name = trim($_POST['name']);
    $ic_number = trim($_POST['ic_number']);
    _number']);
    $phone_number = trim($_POST['phone_number']);

    // Basic validation: Ensure we have the essential data
    if (!empty($patient_id) && !empty($name)) {
        $phone_number = trim($_POST['phone_number']);

    // A simple validation check
    if (!empty($patient_id) && !empty($name)) {
        
        // Step 3: Prepare the
        // Step 3: Prepare the SQL UPDATE statement
        // This query updates the specified columns for the patient with the matching ID.
        $stmt = $conn->prepare("UPDATE Patient SET Name = ?, ICNumber = ?, PhoneNumber SQL UPDATE statement
        // This query updates the specified columns in the Patient table for a given PatientID
        $stmt = $conn->prepare("UPDATE Patient SET Name = ?, ICNumber = ?, PhoneNumber = ? WHERE PatientID = ?"); = ? WHERE PatientID = ?");
        
        // Bind the variables to the query parameters
        $stmt->bind_param("sssi", $name, $ic_number, $phone_number, $patient_id);
        
        // Step 4: Execute the query
        $stmt->execute();
        
        // Close the
        
        // Bind the variables to the query placeholders to prevent SQL injection
        // 's' stands for string, 'i' stands for integer
        $stmt->bind_param("sssi", $name, $ic_number, $phone_number, $patient_id);
        
        // Step 4: Execute the statement
        if ($ statement
        $stmt->close();

        // Optional: You could add a session message here to show a "Success!" banner
        // $_SESSION['success_message'] = "Patient details updated successfully.";

    } elsestmt->execute()) {
            // Success! Optionally, you can set a success message in the session.
            // $_SESSION['success_message'] = "Patient details updated successfully!";
        } else {
            // Error {
        // Optional: Handle the case where required data is missing
        // $_SESSION['error_message'] = "Failed to update: Required data was missing.";
    }
}

// Step 5: Redirect the admin! Optionally, set an error message.
            // $_SESSION['error_message'] = "Failed to update patient details.";
        }
        
        // Close the statement
        $stmt->close();

    } else { back to the main patient list page
header("Location: admin_manage_patients.php");
exit();

        // Handle case where required data is missing
        // $_SESSION['error_message'] = "Patient ID or?>