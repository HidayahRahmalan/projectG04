<?php
function log_audit($conn, $user_id, $action, $recipe_id, $details) {
    $stmt = $conn->prepare("INSERT INTO audit_trail (User_ID, Action, Recipe_ID, Details) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $user_id, $action, $recipe_id, $details);
    $stmt->execute();
    $stmt->close();
}
?>