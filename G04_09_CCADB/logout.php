<?php
// Step 1: Start the session so we can access session variables.
// This must be done before any session manipulation.
session_start();

// Step 2: Unset all of the session variables.
// This clears all data stored in the session, like 'user_id', 'username', and 'role'.
$_SESSION = array();

// Step 3: Destroy the session cookie.
// This is a crucial step to ensure the session is not just empty but fully terminated.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Step 4: Finally, destroy the session itself.
// This invalidates the session ID on the server.
session_destroy();

// Step 5: Redirect the user to the homepage.
// This is per your request to send them to index.php after logging out.
header("Location: index.php");

// Step 6: Stop the script from running further.
// This is important to ensure no other code executes after the redirect header is sent.
exit();
?>