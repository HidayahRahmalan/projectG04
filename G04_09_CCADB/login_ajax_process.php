<?php
// This script acts as an API endpoint. It must return JSON.
session_start();
require_once 'db_conn.php';

// Set the header to tell the browser the response is JSON
header('Content-Type: application/json');

// Create a default response array
$response = [
    'success' => false,
    'message' => 'An unknown error occurred.'
];

// --- Check if the request is a valid POST request ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Safely get username and password from the POST data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($username) || empty($password)) {
        $response['message'] = 'Username and password are required.';
        // Immediately stop and send the JSON response
        echo json_encode($response);
        exit();
    }

    // --- Process the login attempt ---
    $stmt = $conn->prepare("SELECT UserID, Username, Password, Role FROM User WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the password against the correct hash from the database
        if (password_verify($password, $user['Password'])) {
            // --- SUCCESS ---
            // Password is correct. Set session data for backend security on other pages.
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $user['Role'];

            // Prepare a successful JSON response
            $response['success'] = true;
            $response['message'] = 'Login successful!';
            // Send back user data (without the password) for the JavaScript to use
            $response['user'] = [
                'user_id' => $user['UserID'],
                'username' => $user['Username'],
                'role' => $user['Role']
            ];
            
        } else {
            // --- FAILURE ---
            // Password is not correct
            $response['message'] = 'Invalid username or password.';
        }
    } else {
        // --- FAILURE ---
        // No user found with that username
        $response['message'] = 'Invalid username or password.';
    }

    $stmt->close();
    $conn->close();

} else {
    // If someone tries to access this file directly in their browser or with a GET request
    $response['message'] = 'Invalid request method.';
}

// Echo the final JSON response to the JavaScript
echo json_encode($response);