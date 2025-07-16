<?php
include('connect.php');

// Safeguard PDO settings if needed (you already included this in your version)
$conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

// Accept either "id" (for download) or "doc_id" (for extraction)
$doc_id = $_GET['id'] ?? $_GET['doc_id'] ?? null;

if (!$doc_id || !is_numeric($doc_id)) {
    header("HTTP/1.0 400 Bad Request");
    echo json_encode(['success' => false, 'error' => 'Document ID is required']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT doc_data, doc_type, doc_name FROM document WHERE doc_id = ?");
    $stmt->execute([$doc_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || empty($row['doc_data'])) {
        header("HTTP/1.0 404 Not Found");
        echo json_encode(['success' => false, 'error' => 'Document not found']);
        exit;
    }

    $doc_data = $row['doc_data'];
    $doc_type = strtoupper($row['doc_type']);  // normalize
    $doc_name = $row['doc_name'];

    // === TEXT TO AUDIO (EXTRACTION) ===
    if (isset($_GET['action']) && $_GET['action'] === 'extract') {
        if ($doc_type !== 'TXT') {
            echo json_encode([
                'success' => false,
                'error' => 'Only TXT files can be read aloud in this version'
            ]);
            exit;
        }

        // Handle LONGBLOB as resource or string
        $text = is_resource($doc_data) ? stream_get_contents($doc_data) : $doc_data;

        if (!$text) {
            echo json_encode(['success' => false, 'error' => 'Unable to extract text from document.']);
            exit;
        }

        // Clean & format the text
        $text = mb_convert_encoding($text, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', trim($text));

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'text' => $text]);
        exit;
    }

    // === FILE DOWNLOAD ===
    while (ob_get_level()) ob_end_clean(); // Clean output buffers

    // Define proper content type
    $content_types = [
        'PDF' => 'application/pdf',
        'DOCX' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'TXT' => 'text/plain; charset=UTF-8'
    ];
    $content_type = $content_types[$doc_type] ?? 'application/octet-stream';

    // Set headers for download
    header("Content-Type: $content_type");
    header("Content-Disposition: attachment; filename=\"" . basename($doc_name) . "\"");

    // Output document data
    if (is_resource($doc_data)) {
        rewind($doc_data);
        fpassthru($doc_data);
    } else {
        echo $doc_data;
    }
    exit;

} catch (PDOException $e) {
    header("HTTP/1.0 500 Internal Server Error");
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
