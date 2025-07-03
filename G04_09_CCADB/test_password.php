<?php

echo "<h1>Password Verification Test</h1>";

// --- Test 1: Verify the function works with hardcoded values ---

$hardcoded_password = 'admin123';
$hardcoded_hash = '$2y$10$R/JcK8wG3C25eG9e7P4bI.yB2u5v9kY6d7o8C.n9l/n/F.b9t.e4a';

echo "<h2>Test 1: Hardcoded Values</h2>";
echo "<p><strong>Password to check:</strong> " . htmlspecialchars($hardcoded_password) . "</p>";
echo "<p><strong>Known good hash:</strong> " . htmlspecialchars($hardcoded_hash) . "</p>";

if (password_verify($hardcoded_password, $hardcoded_hash)) {
    echo "<p style='color:green; font-weight:bold;'>Result: MATCH! The password_verify function is working correctly on your server.</p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>Result: NO MATCH. There is a fundamental issue with your PHP password functions.</p>";
}

echo "<hr>";


// --- Test 2: Fetch the hash from the database and verify it ---

require_once 'db_conn.php';

echo "<h2>Test 2: Value from Database</h2>";

$username_to_check = 'admin';
$password_to_check = 'admin123';

$stmt = $conn->prepare("SELECT Password FROM User WHERE Username = ?");
$stmt->bind_param("s", $username_to_check);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $hash_from_db = $user['Password'];

    echo "<p><strong>Username checked:</strong> " . htmlspecialchars($username_to_check) . "</p>";
    echo "<p><strong>Password to check:</strong> " . htmlspecialchars($password_to_check) . "</p>";
    echo "<p><strong>Hash fetched from DB:</strong> <pre>" . htmlspecialchars($hash_from_db) . "</pre></p>";
    echo "<p><strong>Length of DB hash:</strong> " . strlen($hash_from_db) . " characters.</p>";

    if (password_verify($password_to_check, $hash_from_db)) {
        echo "<p style='color:green; font-weight:bold;'>Result: MATCH! The hash in the database is correct. The login problem is somewhere else.</p>";
    } else {
        echo "<p style='color:red; font-weight:bold;'>Result: NO MATCH. The hash in the database is WRONG or TRUNCATED. This is the source of your login problem.</p>";
    }

} else {
    echo "<p style='color:red; font-weight:bold;'>Error: Could not find the user '" . htmlspecialchars($username_to_check) . "' in the database.</p>";
}

$conn->close();

?>