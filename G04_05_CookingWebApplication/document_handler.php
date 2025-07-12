<?php
header('Content-Type: application/json');
require 'connect.php';

$docId = $_GET['doc_id'] ?? '';
$action = $_GET['action'] ?? '';

if (!$docId || $action !== 'extract') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

try {
    // Fetch document BLOB and type from MySQL
    $stmt = $conn->prepare("SELECT DOC_DATA, DOC_TYPE FROM DOCUMENT WHERE DOC_ID = ?");
    $stmt->execute([$docId]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        echo json_encode(['success' => false, 'error' => 'Document not found']);
        exit;
    }

    $docData = $doc['DOC_DATA'];
    $docType = strtoupper($doc['DOC_TYPE']);

    if ($docType !== 'TXT') {
        echo json_encode(['success' => false, 'error' => 'Only TXT files are supported for reading.']);
        exit;
    }

    // Convert the binary data to string (assuming UTF-8 encoded plain text)
    $text = mb_convert_encoding($docData, 'UTF-8', 'UTF-8');

    echo json_encode([
        'success' => true,
        'text' => $text
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>
