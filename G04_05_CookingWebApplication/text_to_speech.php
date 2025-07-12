<?php
require 'vendor/autoload.php';
include('connect.php');

// Set proper headers
header('Content-Type: application/json');

// Handle both GET and POST requests
$doc_id = $_REQUEST['doc_id'] ?? null;
$action = $_REQUEST['action'] ?? 'extract'; // 'extract' or 'speak'

if (!$doc_id) {
    echo json_encode(['error' => 'Document ID not provided']);
    exit();
}

// Get document from database with proper binary handling
$conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
$stmt = $conn->prepare("SELECT `doc_data`, `doc_type` FROM `document` WHERE `doc_id` = ?");
$stmt->execute([$doc_id]);
$stmt->bindColumn(1, $doc_data, PDO::PARAM_LOB);
$stmt->bindColumn(2, $doc_type);
$stmt->fetch(PDO::FETCH_BOUND);

if (!$doc_data) {
    echo json_encode(['error' => 'Document not found']);
    exit();
}

try {
    // Handle both resource streams and string data
    $content = is_resource($doc_data) ? stream_get_contents($doc_data) : $doc_data;
    
    switch(strtoupper($doc_type)) {
        case 'PDF':
            $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
            file_put_contents($tempFile, $content);
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($tempFile);
            $text = $pdf->getText();
            unlink($tempFile);
            break;
            
        case 'DOCX':
            $tempFile = tempnam(sys_get_temp_dir(), 'docx');
            file_put_contents($tempFile, $content);
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($tempFile);
            $text = '';
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                        $text .= $element->getText() . ' ';
                    }
                }
            }
            unlink($tempFile);
            break;
            
        case 'TXT':
            $text = $content;
            break;
            
        default:
            throw new Exception("Unsupported document type");
    }
    
    // Clean up text
    $text = preg_replace('/\s+/', ' ', trim($text));
    if (empty($text)) {
        throw new Exception("No text could be extracted");
    }

    // Return based on requested action
    if ($action === 'speak') {
        // Generate audio file (optional future implementation)
        echo json_encode([
            'success' => true,
            'text' => $text,
            'audioUrl' => null // Client will handle speech synthesis
        ]);
    } else {
        // Just return extracted text
        echo json_encode([
            'success' => true,
            'text' => $text
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error processing document: ' . $e->getMessage()
    ]);
}