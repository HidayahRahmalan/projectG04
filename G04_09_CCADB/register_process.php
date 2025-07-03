<?php
session_start();
require_once 'db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user already exists
    $stmt = $conn->prepare("SELECT UserID FROM User WHERE Username = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User already exists
        $_SESSION['register_error'] = "An account with this email already exists.";
        header("Location: register.php");
        exit();
    } else {
        // Create new user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $conn->begin_transaction();
        try {
            // Create User record - This part is correct
            $stmt_user = $conn->prepare("INSERT INTO User (Username, Password, Role) VALUES (?, ?, 'Patient')");
            $stmt_user->bind_param("ss", $email, $hashed_password);
            $stmt_user->execute();
            $user_id = $conn->insert_id;

            // ===================================================================
            // THE FIX IS HERE
            // The original query was invalid because it didn't account for all columns.
            // This new query explicitly lists the columns we are inserting into.
            // Other columns like ICNumber and PhoneNumber will get their default (NULL) values.
            // ===================================================================
            $stmt_patient = $conn->prepare("INSERT INTO Patient (UserID, Name, PhoneNumber, ICNumber) VALUES (?, ?, NULL, NULL)");
            if ($stmt_patient === false) {
                // This will give a more readable error if the prepare statement fails again
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            // Bind parameters for the new, correct query. We are only binding UserID and Name.
            $stmt_patient->bind_param("is", $user_id, $name);
            $stmt_patient->execute();

            $conn->commit();

            // Success! Redirect to the login page.
            $_SESSION['register_success'] = "Account created successfully! Please sign in.";
            header("Location: login.php");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['register_error'] = "Registration failed. Please try again. Error: " . $e->getMessage();
            header("Location: register.php");
            exit();
        }
    }
} else {
    header("Location: login.php");
    exit();
}