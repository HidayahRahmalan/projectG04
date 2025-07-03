<?php

// This script will generate a new, 100% compatible hash for a given password.

$password_to_hash = 'testpass';

// Use PHP's built-in function to create a secure hash.
// PASSWORD_DEFAULT uses the strong BCRYPT algorithm.
$new_hash = password_hash($password_to_hash, PASSWORD_DEFAULT);

// --- Display the results to the user ---

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head><title>Password Hash Generator</title><style>body{font-family: sans-serif; padding: 20px;} pre{background-color:#eee; padding:15px; border:1px solid #ccc; font-size:16px; word-wrap:break-word;} strong{color:#006600;}</style></head>";
echo "<body>";

echo "<h1>New Password Hash Generator</h1>";
echo "<p>This page has generated a new, secure password hash specifically for your server environment.</p>";

echo "<p>Password that was hashed: <strong>" . htmlspecialchars($password_to_hash) . "</strong></p>";
echo "<p>Your new, 100% compatible hash is:</p>";
echo "<pre>" . htmlspecialchars($new_hash) . "</pre>";

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li><strong>Copy the entire new hash string</strong> from the box above (it starts with `$2y$10$`).</li>";
echo "<li>Go to your phpMyAdmin dashboard.</li>";
echo "<li>Open the `User` table and find the row for the `admin` user. Click **Edit**.</li>";
echo "<li>In the form, delete the old hash from the `Password` field.</li>";
echo "<li><strong>Paste this new hash</strong> into the `Password` field.</li>";
echo "<li>Click the **Go** button to save the changes.</li>";
echo "<li>Your login will now work with the username `admin` and password `admin123`.</li>";
echo "</ol>";

echo "<p>You can delete this file (`generate_hash.php`) after you have updated the password in the database.</p>";

echo "</body>";
echo "</html>";

?>