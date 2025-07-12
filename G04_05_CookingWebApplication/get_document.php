<?php
include('connect.php');

// Critical PDO settings for binary data
$conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

if (!isset($_GET['id']) && !isset($_GET['doc_id'])) {
    header("HTTP/1.0 400 Bad Request");
    exit(json_encode(['success' => false, 'error' => 'Document ID is required']));
}

$doc_id = $_GET['id'] ?? $_GET['doc_id'] ?? null;

try {
    // Get document metadata
    $stmt = $conn->prepare("SELECT doc_data, doc_type, doc_name FROM document WHERE doc_id = ?");
    $stmt->execute([$doc_id]);
    
    $stmt->bindColumn(1, $doc_data, PDO::ATTR_STRINGIFY_FETCHES ? PDO::PARAM_STR : PDO::PARAM_LOB);
    $stmt->bindColumn(2, $doc_type);
    $stmt->bindColumn(3, $doc_name);
    $stmt->fetch(PDO::FETCH_BOUND);
    
    if (!$doc_data) {
        header("HTTP/1.0 404 Not Found");
        exit(json_encode(['success' => false, 'error' => 'Document not found']));
    }

    // Handle text extraction request
    if (isset($_GET['action']) && $_GET['action'] === 'extract') {
        // For text extraction, we only handle TXT files in this basic example
        if ($doc_type !== 'TXT') {
            exit(json_encode([
                'success' => false, 
                'error' => 'Only TXT files can be read aloud in this version'
            ]));
        }
        
        // Get the text content
        $text = is_resource($doc_data) ? stream_get_contents($doc_data) : $doc_data;
        
        // Basic sanitization
        $text = mb_convert_encoding($text, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', trim($text));
        
        header('Content-Type: application/json');
        exit(json_encode([
            'success' => true,
            'text' => $text
        ]));
    }

    // Handle regular download request
    // Clean all output buffers
    while (ob_get_level()) ob_end_clean();

    // Set content types
    $content_types = [
        'PDF' => 'application/pdf',
        'DOCX' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'TXT' => 'text/plain; charset=UTF-8'
    ];
    
    $content_type = $content_types[$doc_type] ?? 'application/octet-stream';
    
    // Set headers
    header("Content-Type: $content_type");
    header("Content-Disposition: attachment; filename=\"" . htmlspecialchars($doc_name) . "\"");
    
    // Output the binary data
    if (is_resource($doc_data)) {
        fpassthru($doc_data);
    } else {
        echo $doc_data;
    }
    exit;
    
} catch (PDOException $e) {
    header("HTTP/1.0 500 Internal Server Error");
    exit(json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]));
}