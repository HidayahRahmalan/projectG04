<?php
include('connect.php');
header('Content-Type: application/json');

$doc_id = $_REQUEST['doc_id'] ?? null;

if (!$doc_id || !is_numeric($doc_id)) {
    echo json_encode(['success' => false, 'error' => 'Document ID not provided or invalid']);
    exit();
}

try {
    // Important PDO settings for consistent fetch behavior
    $conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $conn->prepare("SELECT doc_data, doc_type FROM document WHERE doc_id = ?");
    $stmt->execute([$doc_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['success' => false, 'error' => 'Document not found']);
        exit();
    }

    $doc_data = $row['doc_data'];
    $doc_type = strtoupper($row['doc_type']);

    if ($doc_type !== 'TXT') {
        echo json_encode(['success' => false, 'error' => 'Only TXT files can be read aloud.']);
        exit();
    }

    // --- Handle binary LONGBLOB ---
    // If it's a stream (resource), read it
    if (is_resource($doc_data)) {
        $text = stream_get_contents($doc_data);
    } else {
        $text = $doc_data;
    }

    // Sanitize and convert text
    $text = mb_convert_encoding($text, 'UTF-8', 'auto');
    $text = preg_replace('/\s+/', ' ', trim($text));

    if (empty($text)) {
        echo json_encode(['success' => false, 'error' => 'No readable text found.']);
        exit();
    }

    // Return success
    echo json_encode(['success' => true, 'text' => $text]);
    exit();

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit();
}
