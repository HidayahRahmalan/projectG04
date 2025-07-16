<?php
include('connect.php');
header('Content-Type: application/json');

// Accept doc_id and optional action
$doc_id = $_REQUEST['doc_id'] ?? null;
$action = $_REQUEST['action'] ?? 'extract';

if (!$doc_id || !is_numeric($doc_id)) {
    echo json_encode(['success' => false, 'error' => 'Document ID not provided or invalid']);
    exit();
}

try {
    $conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    $stmt = $conn->prepare("SELECT doc_data, doc_type FROM document WHERE doc_id = ?");
    $stmt->execute([$doc_id]);
    $stmt->bindColumn(1, $doc_data, PDO::PARAM_LOB);
    $stmt->bindColumn(2, $doc_type);
    $stmt->fetch(PDO::FETCH_BOUND);

    if (!$doc_data || strtoupper($doc_type) !== 'TXT') {
        echo json_encode([
            'success' => false,
            'error' => 'Only TXT documents are supported for reading.'
        ]);
        exit();
    }

    // Extract and clean the text
    $content = is_resource($doc_data) ? stream_get_contents($doc_data) : $doc_data;
    $text = preg_replace('/\s+/', ' ', trim($content ?? ''));

    if (empty($text)) {
        echo json_encode(['success' => false, 'error' => 'No readable text found.']);
        exit();
    }

    // Respond with clean text (for browser speech synthesis)
    echo json_encode([
        'success' => true,
        'text' => $text
    ]);
    exit();

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
    exit();
}
