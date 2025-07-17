<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') { header("Location: login.php"); exit(); }
require_once 'db_conn.php';

$api_key = 'YOUR_SECRET_API_KEY'; // PASTE YOUR NEW, SECRET KEY HERE

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = intval($_POST['appointment_id']);
    $patient_id = intval($_POST['patient_id']);
    $diagnosis = trim($_POST['diagnosis']); // This will be used if no image is uploaded
    $notes = trim($_POST['notes']);
    $mc_method = $_POST['mc_method'] ?? 'none';

    // --- MC LOGIC: We handle the MC first to potentially get diagnosis/notes from the AI ---
    $mc_saved = false;

    // === OPTION B: AI-Powered Image Upload (Handles Diagnosis/Notes Extraction) ===
    if ($mc_method === 'upload' && isset($_FILES['mc_image']) && $_FILES['mc_image']['error'] == 0) {
        $upload_dir = 'uploads/mcs/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
        $file_path = $upload_dir . 'mc_' . $appointment_id . '_' . time() . '.' . pathinfo($_FILES['mc_image']['name'], PATHINFO_EXTENSION);
        
        if (move_uploaded_file($_FILES['mc_image']['tmp_name'], $file_path)) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $api_key;
            // --- NEW, ENHANCED PROMPT ---
            $prompt = "From this image of a medical certificate, extract these details and format as a simple JSON object: {\"start_date\": \"YYYY-MM-DD\", \"days_off\": X, \"diagnosis\": \"The main diagnosis written on the MC\", \"notes\": \"Any other notes or remarks\"}.";
            
            $image_data = base64_encode(file_get_contents($file_path));
            $image_mime_type = $_FILES['mc_image']['type'];
            $data = ['contents' => [['parts' => [['text' => $prompt], ['inline_data' => ['mime_type' => $image_mime_type, 'data' => $image_data]]]]]];
            $json_data = json_encode($data);

            $ch = curl_init();
            curl_setopt_array($ch, [CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $json_data, CURLOPT_HTTPHEADER => ['Content-Type: application/json']]);
            $response = curl_exec($ch);
            curl_close($ch);

            $response_data = json_decode($response, true);
            $ai_text = $response_data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $json_text = trim(str_replace(['```json', '```'], '', $ai_text));
            $mc_details = json_decode($json_text, true);

            // Extract data from AI, with fallbacks
            $start_date = $mc_details['start_date'] ?? date('Y-m-d');
            $num_days = intval($mc_details['days_off'] ?? 1);
            $reason = $mc_details['reason'] ?? ($mc_details['diagnosis'] ?? 'Medical leave as per uploaded certificate.'); // Use diagnosis as reason
            
            // --- NEW: Use AI-extracted text to override the form's diagnosis/notes ---
            $diagnosis = $mc_details['diagnosis'] ?? $diagnosis;
            $notes = $mc_details['notes'] ?? $notes;
            
            // Save to the MedicalCertificate table
            $stmt_mc = $conn->prepare("INSERT INTO MedicalCertificate (AppointmentID, StartDate, NumberOfDays, Reason, ImagePath) VALUES (?, ?, ?, ?, ?)");
            $stmt_mc->bind_param("isiss", $appointment_id, $start_date, $num_days, $reason, $file_path);
            $stmt_mc->execute();
            $stmt_mc->close();
            $mc_saved = true;
        }
    } 
    // === OPTION A: AI-Assisted Text Generation ===
    elseif ($mc_method === 'generate' && !empty($_POST['mc_days'])) {
        // This logic remains the same
        $mc_days = intval($_POST['mc_days']);
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $api_key;
        $prompt = "Based on the medical diagnosis '" . addslashes($diagnosis) . "', write a formal, one-sentence reason for a medical certificate suitable for use in Malaysia. For example, if the diagnosis is 'viral fever', the reason could be 'is suffering from a viral infection and requires adequate rest to recover.'";
        $data = ['contents' => [['parts' => [['text' => $prompt]]]]];
        $json_data = json_encode($data);

        $ch = curl_init();
        curl_setopt_array($ch, [CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $json_data, CURLOPT_HTTPHEADER => ['Content-Type: application/json']]);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $response_data = json_decode($response, true);
        $ai_generated_reason = $response_data['candidates'][0]['content']['parts'][0]['text'] ?? "Patient requires medical leave to recover.";
        
        $start_date = date('Y-m-d');
        $stmt_mc = $conn->prepare("INSERT INTO MedicalCertificate (AppointmentID, StartDate, NumberOfDays, Reason) VALUES (?, ?, ?, ?)");
        $stmt_mc->bind_param("isis", $appointment_id, $start_date, $mc_days, $ai_generated_reason);
        $stmt_mc->execute();
        $stmt_mc->close();
        $mc_saved = true;
    }

    // --- Save the main medical record (now with potentially AI-filled data) ---
    if (!empty($appointment_id) && !empty($diagnosis)) {
        $stmt_mr = $conn->prepare("INSERT INTO MedicalRecord (AppointmentID, Diagnosis, Notes) VALUES (?, ?, ?)");
        $stmt_mr->bind_param("iss", $appointment_id, $diagnosis, $notes);
        $stmt_mr->execute();
        $stmt_mr->close();
    }

    // --- Redirect back to the patient's history page ---
    header("Location: doctor_view_patient.php?patient_id=" . $patient_id);
    exit();
}