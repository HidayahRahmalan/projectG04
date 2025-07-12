<?php
require 'connect.php';
$doc_id = $_GET['doc_id'] ?? '';
if (!$doc_id) exit;
$stmt = $conn->prepare("SELECT DOC_NAME, DOC_TYPE, DOC_DATA FROM DOCUMENT WHERE DOC_ID = ?");
$stmt->execute([$doc_id]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);
if ($doc) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $doc['DOC_NAME'] . '"');
    echo $doc['DOC_DATA'];
    exit;
} else {
    echo "Document not found.";
}
?>